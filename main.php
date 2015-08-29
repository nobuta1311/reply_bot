<?php
//error_reporting(0);	//warningが無限に出るので
require "Methods.php";
require "japanese.php";
require "mysql_key.php";
require "../shorter.php";
date_default_timezone_set('Asia/Tokyo');
//20分ごとに蓄積されたa単語を出力してデータベースをお掃除する関数hours
function a_word(){       //20分ごとのまとめツイート
        $query_users = "select * from point_word where (STRCMP(tempstr,\"\")!=0) and (STRCMP(tempstr,\".\")!=0)";        //空文字列でないレコード集める
        $fetcher = mysql_query($query_users);
        while($row = mysql_fetch_assoc($fetcher)){                           //まとめてツイート
                $tweetstr = "@".$row["user"]." 20分以内に発見したa単語\n".$row["tempstr"];
                update($tweetstr,$row['reply_to']);
               }
        $query_clear = "update point_word set tempstr=\"\",reply_to=\"\"";
        mysql_query($query_clear);   //まとめてツイートする内容と返信先を消去
}
function ranking(){//ランキング照会関数
        $query_rank = "select user,point from point_word order by point desc limit 5";
        $fetcher = mysql_query($query_rank);
        $tweetstr = "現時点の上位5位\n";
        for($k=1;$k<=5;$k++){
                echo $tweetstr;
                $row=mysql_fetch_assoc($fetcher);
                $tweetstr = $tweetstr.$k." ".$row["user"]." : ".$row["point"]."pt\n";
        }
        print_r (update($tweetstr,""));
}
function refer($user_name,$user_text,$reply_to){  //点数照会関数
                $query_refer = "select point from point_word where user=\"".$user_name."\"";  //ポイント取得
                $query_new = "select tempstr from point_word where user=\"".$user_name."\"";//tempstr取得
                $isnew = mysql_fetch_assoc(mysql_query($query_new));
                if($row_refer = mysql_fetch_assoc(mysql_query($query_refer))){
                        $current_point = $row_refer["point"];   //既存ポイントがあればその値
                        }else{
                        $current_point=0;                       //ポイントがなければ0
                        }
                if($isnew["tempstr"][0]!="."){                  //最近照会したなら実行しない
                        //echo mb_substr($isnew["tempstr"],0,1,"UTF-8");
                        update("@".$user_name. " あなたの現在の点数は".$current_point."です",$reply_to);
                        $query_mark = "update point_word set tempstr = concat(\".\",tempstr) where user = \"".$user_name."\"";
                        mysql_query($query_mark);
               }
}        
function word_check($user_name,$user_text,$reply_to,$fp){
                $query_read = "select * from words";
                $result_read = mysql_query($query_read);
		for($num = 1;$num<=6000;$num++){		//単語照合ループ
			$row = mysql_fetch_assoc($result_read);	//whileで全表示可能
			$result_word = $row['word'];	//一時的に格納
			$result_meaning = $row['meaning'];
			$result_phase = $row['phase'];
                        //単語の項目と合致した場合!
			if(stristr($user_text,$result_word)!=false || stristr($user_text,$result_meaning)!=false){
                                //単語発見
                                fwrite($fp,"\n".date("r")." ".$result_word." ".$user_name."の".$user_text);
				$detail = "";
				$detail = IntoJapanese($result_word);		//単語の説明をデ辞蔵から取得
                                //最近ツイートされてないかを調べる
				$query_maxday = "select max(day) from past_words where word=\"".$result_word."\"";
				$row = mysql_fetch_assoc(mysql_query($query_maxday));	//過去のツイートを検索
				$result_day = $row['max(day)']; 
				$tweetstr =$result_phase.": ".$result_word." ".$result_meaning."\n".$detail;	//ツイート内容構成
				$tweetstr = mb_convert_kana("@".$user_name." ".$tweetstr."\n","a",'UTF-8'); //半角化
                                //ツイートすべきかどうかの判断
                                $query_count_appere = "select word, count(word) from past_words where word=\"".$result_word."\" group by word"; //出現回数取得
                                $row = mysql_fetch_assoc(mysql_query($query_count_appere));
                                $query_insert = "insert into past_words values(\"".$user_name."\",\"".$result_word."\",".date("z").")";        //過去単語を更新
                               // fwrite($fp,"発見済み単語に記録\n".$query_insert."\n");
				mysql_query($query_insert);
				if($result_day==NULL||date("z")-$result_day>30 || (date("z")-$result_day<0 && (366-$result_day)+("z")>30)){	
                                        //過去40日に出現していないならば、得点の対象となる
                                        if($result_day==NULL){fwrite($fp,"過去に記録がないので得点の対象 ");}else{
                                        fwrite($fp,$result_day."日前に反応したので得点の対象 ");}
                                        if($num<4000){  //a単語ならば10点満点
                                                fwrite($fp,"a単語");
                                                if($row["count(word)"]==0){$point=10;}else{$point = round(10/($row["count(word)"]+1),2);}  
                                                //新たな単語は10ポイントで他のは10/回数+1
                                                $query_set = "update point_word set tempstr = concat(tempstr,\" ".$result_word.":".$result_meaning." ".$point."pt \") , reply_to =\"".$reply_to."\" where user = \"".$user_name."\"";
                                                //echo $result_word;
                                                //文字列を蓄える
                                                mysql_query($query_set);
                                        }else{          //b単語ならば点数100点満点
                                                fwrite($fp,"b単語");
                                                $longurl = "http://www.merriam-webster.com/dictionary/".$result_word;
                                                $tinyurl = get_tiny_url($longurl);
                                                $length = mb_strlen($tweetstr,'UTF-8');
                                                if($length<120){
                                                        $tweetstr = ".".$tweetstr." ".$tinyurl." ";
                                                }else if($length>134){
                                                        $tweetstr = mb_substr($tweetstr,0,134,'UTF-8');
                                                }
                                                if($row["count(word)"]==0){$point=100;}else{$point = round(100/($row["count(word)"]+1),2);}  
                                                //新たな単語は100ポイントで他のは100/回数+1
                                             //   makepng($result_phase);
					        //if($result_exec=="0"){
                                              //  upload($tweetstr.$point."pt","./result.png",$reply_to);
                                                //}else{
                                                update($tweetstr.$point."pt",$reply_to);
                                                fwrite($fp,"ツイート送信完了");
                                                sleep(5);
                                                exec("nohup php makeimage.php ".$result_phase." &");
                                                fwrite($fp,"画像作成関数実行");


                                        }
                                        //ポイントデータの処理    ユーザにデータベース造られているかどうか
                                        sleep(3);
                                        $query_check = "select * from point_word where user=\"".$user_name."\"";
                                        $result_check = mysql_query($query_check);
                                        $result_check_empty = mysql_fetch_assoc($result_check);
                                        if(!$result_check_empty){
                                                //ポイント追加失敗　レコード作成
                                                $query_addpoint = "insert into point_word value(\"".$user_name."\",".$point.",\"\",\"\")";
                                                mysql_query($query_addpoint);
                                                fwrite($fp,"失敗レコード作成");
                                        }else{
                                                $query_point = "UPDATE point_word SET point = point+".$point." WHERE user =\"".$user_name."\"";
                                                $result_point = mysql_query($query_point);
                                                fwrite($fp,"\n".$query_point."\n");
                                                //ポイント追加成功
                                                fwrite($fp,"成功!");               
                                        }
				}else{fwrite($fp,$result_day."にあらわれているので得点対象外");}
				}
              }
}

$fp = fopen("statelog.txt", "a");
$link = mysql_connect('localhost',$mysql_user,$mysql_pass);//データベース接続
$db_selected = mysql_select_db('phptest',$link);        //データベース選択
$array_home = home_timeline(20);                        //ホームタイムライン20取得
$newest = file_get_contents("log.txt");                 //今回の下限ツイートを取得
file_put_contents("log.txt",$array_home[0]["id"]);      //次回のツイート下限を設定
for ($i = 0; $i < 30; $i++) {           //１分間に30ツイートを想定
        sleep(1);
	if(empty($array_home[$i])){continue;}   //取得エラーなら処理しない
        if($array_home[$i]["id"]==$newest){break;}    //前回チェックしたところに到着->break
	$reply_to = $array_home[$i]["id"];$user_name = $array_home[$i]["user"]["screen_name"]; $user_text = $array_home[$i]["text"];//変数名前変更
        if(stristr($user_text,"@HUSTNWRD")){refer($user_name,$user_text,$reply_to);}   //現時点の点数照会
        if($user_name!="HUSTNWRD" && $array_home[$i]["retweeted_status"]==null){word_check($user_name,$user_text,$reply_to,$fp);}//単語照会
}
if(date("i")%20==0){a_word();}              //20分ごとのa単語のチェック
if(date("G")==0 && date("i")==0 ){ranking();}   //ランキングの発表
mysql_close($link);	//データベース閉じる
fclose($fp);
?>
