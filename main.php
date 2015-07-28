<?php
//error_reporting(0);	//warningが無限に出るので
require "Methods.php";
require "japanese.php";
require "mysql_key.php";
date_default_timezone_set('Asia/Tokyo');
$link = mysql_connect('localhost',$mysql_user,$mysql_pass);//データベース接続
$db_selected = mysql_select_db('phptest',$link);
$array_home = home_timeline(20);
$newest = file_get_contents("log.txt");
file_put_contents("log.txt",$array_home[0]["id"]);
for ($i = 0; $i < 30; $i++) {           //１分間に30ツイートを想定
        sleep(1);
	if(empty($array_home[$i])){			//取得できているかチェック
		continue;
	}
        if($array_home[$i]["id"]==$newest){     //前回チェックしたところに到着->break
         //     print($array_home[$i]["text"]);
              $newest = $array_home[0]["id"]; //読み込んだツイートの履歴を更新
              break;
        }
	$reply_to = $array_home[$i]["id"];
	$user_name = $array_home[$i]["user"]["screen_name"];	//reply_to $user_name $user_text
        $user_text = $array_home[$i]["text"];
        //ここから点数照会ゾーン
        //echo $user_text;
        if(stristr($user_text,"@BotOfNobuta")){
                $query = "select point from point_word where user=\"".$user_name."\"";
                $row = mysql_fetch_assoc(mysql_query($query));
                $query_new = "select tempstr from point_word where user=\"".$user_name."\"";
                $isnew = mysql_fetch_assoc(mysql_query($query_new));
                if($row==false){$current_point = 0;}else{$current_point = $row["point"];}
                if($isnew["tempstr"][0]!="."){      
                        //echo mb_substr($isnew["tempstr"],0,1,"UTF-8");
                        update("@".$user_name. " あなたの現在の点数は".$current_point."です",$reply_to);
                        $query_mark = "update point_word set tempstr = concat(\".\",tempstr) where user = \"".$user_name."\"";
                        mysql_query($query_mark);

               }
        }
        //ここから、単語の照会をはじめる
	if($user_name!="BotOfNobuta" && $array_home[$i]["retweeted_status"]==null){	//自分自身以外でかつリツイートでない
                $query_read = "select * from words";
                $result_read = mysql_query($query_read);
		for($num = 1;$num<=6000;$num++){		//単語照合ループ
			$row = mysql_fetch_assoc($result_read);	//whileで全表示可能
			$result_word = $row['word'];	//一時的に格納
			$result_meaning = $row['meaning'];
			$result_phase = $row['phase'];
                        //単語の項目と合致した場合!
			if(stristr($user_text,$result_word)!=false || stristr($user_text,$result_meaning)!=false){
				$detail = "";
				$detail = IntoJapanese($result_word);		//単語の説明をデ辞蔵から取得
                                //最近ツイートされてないかを調べる
				$query = "select max(day) from past_words where word=\"".$result_word."\"";
				$row = mysql_fetch_assoc(mysql_query($query));	//過去のツイートを検索
				$result_day = $row['max(day)']; 
				$tweetstr =$result_phase.": ".$result_word." ".$result_meaning."\n".$detail;	//ツイート内容構成
				$tweetstr = mb_convert_kana("@".$user_name." ".$tweetstr."\n","a",'UTF-8'); //半角化
                                //echo $tweetstr;
                                //ツイートすべきかどうかの判断
                                //echo $num."\n";
				if($result_day==NULL||date("z")-$result_day>40 || (date("z")-$result_day<0 && (366-$result_day)+("z")>40)){	
                                        //過去40日に反応していないならば、得点の対象となる
                                        $query = "select word, count(word) from past_words where word=\"".$result_word."\" group by word"; //出現回数
                                        $row = mysql_fetch_assoc(mysql_query($query));
                                        if($num<4000){  //a単語ならば10点満点
                                                if($row["count(word)"]==0){$point=10;}else{$point = round(10/($row["count(word)"]+1),2);}  
                                                //新たな単語は10ポイントで他のは10/回数+1
                                                $query = "update point_word set tempstr = concat(tempstr,\" ".$result_word.":".$result_meaning." ".$point."pt \") where user = \"".$user_name."\"";
                                                echo $result_word;
                                                //文字列を蓄える
                                                mysql_query($query);
                                        }else{          //b単語ならば点数100点満点
                                                $tweetstr = ".".$tweetstr;
                                                if($row["count(word)"]==0){$point=100;}else{$point = round(100/($row["count(word)"]+1),2);}  
                                                //新たな単語は100ポイントで他のは100/回数+1
					        update($tweetstr.$point."pt",$reply_to);
                                        }
                                        //ポイントデータの処理    ユーザにデータベース造られているかどうか
                                        $query = "select point from point_word where user=\"".$user_name."\"";
                                        $row = mysql_fetch_assoc(mysql_query($query));
                                        if($row==false){        //既存のレコードがないならばレコード挿入
                                                $query = "insert into point_word value(\"".$user_name."\",".$point.",\"\")";
                                        }else{                  //既にレコードがあるならばレコード更新
                                                $query = "UPDATE point_word SET point = point+".$point." WHERE user =\"".$user_name."\"";
                                        }
                                        mysql_query($query);
				}
				$query = "insert into past_words values(\"".$user_name."\",\"".$result_word."\",".date("z").")";
                                        //過去単語を更新
				mysql_query($query);
				}
			}
	}

}

if(date("i")%20==0){hours();}              //hours実行
//hours();
mysql_close($link);	//データベース閉じる

//20分ごとに蓄積されたa単語を出力してデータベースをお掃除する関数hours
function hours(){
        $query_users = "select * from point_word where (STRCMP(tempstr,\"\")!=0) and (STRCMP(tempstr,\".\")!=0)";        //空文字列でないレコード集める
        $fetcher = mysql_query($query_users);
        while($row = mysql_fetch_assoc($fetcher)){                           //まとめてツイート
                $tweetstr = "@".$row["user"]." 20分位内に発見したa単語\n".$row["tempstr"];
                update($tweetstr,"");
                $query_clear = "update point_word set tempstr=\"\" where user=\"".$row["user"]."\"";
                mysql_query($query_clear);   //消去
        }
}
?>
