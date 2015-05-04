<?php
//エンドポイントURL
//error_reporting(0);	//warningが無限に出るので
require"keys.php";
require"method_home.php";	
require"sign.php";
require"method_tweet.php";
require"japanese.php";
date_default_timezone_set('Asia/Tokyo');

$link = mysql_connect('localhost','phptest','yuji2943');//データベース接続
$db_selected = mysql_select_db('phptest',$link);
$json = home(20);

//第一引数はcount第二引数はそのIDよりも新しいツイート、第三引数はその逆
$array_home = json_decode($json,true);
for ($i = 0; $i < 20; $i++) {
	if(empty($array_home[$i])){
		continue;
	}
	if($array_home[$i]["user"]["screen_name"]!="BotOfNobuta" && $array_home[$i]["retweeted_status"]==null){	//自分自身以外でかつリツイートでない
		$temp = $array_home[$i]["text"];
		$reply_to = $array_home[$i]["id"];
		//$query = "insert into past_tweet values (".$reply_to.");";
		//$results = mysql_query($query);
		//if($results ==false)
		//	continue;
		for($num = 4001;$num<=6000;$num++){		//単語照合ループ
			$query = "select * from words where number=".$num;
			$result = mysql_query($query);					
			$row = mysql_fetch_assoc($result);	//whileで全表示可能
			$result_word = $row['word'];
			$result_meaning = $row['meaning'];
			$result_phase = $row['phase'];
			if(stristr($temp,$result_word)!=false || stristr($temp,$result_meaning)!=false){	//単語辞書と合致
				$detauk = "";
				$detail = IntoJapanese($result_word);
				mysql_query($query);
				$query = "select max(day) from past_words where user=\"".$array_home[$i]["user"]["screen_name"]."\" and word=\"".$result_word."\"";
	//			echo $query;
				$row = mysql_fetch_assoc(mysql_query($query));
				$result_day = $row['max(day)'];
	//			echo "\n".$result_day;
				$tweetstr ="@".$array_home[$i]["user"]["screen_name"]." ".$result_phase.": ".$result_word." ".$result_meaning."\n".$detail;
				/*if(mb_strlen($tweetstr)>140){
					$tweetstr = mb_substr($tweetstr,0,139);
				}
				*/
				$tweetstr = mb_convert_kana($tweetstr,"a");
	//			echo $tweetstr;
				if(($result_day>date("z")|| date("z")-$result_day>30) || $result_day==null){	//30日以内に反応していないa
	//				echo $result_day."\n";
	//				echo "tweet!".$tweetstr;
	//				echo $result_day."日につぶやいた過去がある";
					tweet($tweetstr,$reply_to);
				}
				$query = "insert into past_words values(\"".$array_home[$i]["user"]["screen_name"]."\",\"".$result_word."\",".date("z").")";
				$query;
				mysql_query($query);
				
				}
			}
	}
}
mysql_close($link);	//データベース閉じる
