<?php
//エンドポイントURL
error_reporting(0);	//warningが無限に出るので
require"keys.php";
require"method_home.php";	
require"sign.php";
require"method_tweet.php";

$link = mysql_connect('localhost','phptest','yuji2943');//データベース接続
$db_selected = mysql_select_db('phptest',$link);
$json = home(20);

//第一引数はcount第二引数はそのIDよりも新しいツイート、第三引数はその逆
$array_home = json_decode($json,true);
for ($i = 1; $i < 20; $i++) {
	if(empty($array_home[$i])){
		continue;
	}
	if($array_home[$i]["user"]["screen_name"]!="BotOfNobuta"){
		$temp = $array_home[$i]["text"];
		$reply_to = $array_home[$i]["id"];
		//$query = "insert into past_tweet values (".$reply_to.");";
		//$results = mysql_query($query);
		//if($results ==false)
		//	continue;
		for($num = 4001;$num<=6000;$num++){
			$query = "select * from words where number=".$num;
			$result = mysql_query($query);					
			$row = mysql_fetch_assoc($result);	//whileで全表示可能
			$result_word = $row['word'];
			$result_meaning = $row['meaning'];
			$result_phase = $row['phase'];
			if(stristr($temp,$result_word)!=false || stristr($temp,$result_meaning)!=false){
				echo "tsurai";
				mysql_query($query);
				$query = "select max(day) from past_words where user=\"".$array_home[$i]["user"]["screen_name"]."\" and word=\"".$result_word."\"";
				$row = mysql_fetch_assoc(mysql_query($query));
				$result_day = $row['day'];
				$tweetstr ="@".$array_home[$i]["user"]["screen_name"]."\n".$result_phase.": ".$result_word."  ".$result_meaning."\nhttp://www.merriam-webster.com/dictionary/".$result_word;	
				echo $reply_to.".".$result_day;
				if(($result_day>date("z")|| date("z")-$result_day>30) && !empty($reply_to)){
					echo "tweet!";
					tweet($tweetstr,$reply_to);
					//過去に無いしリツイートでない
				}
				$query = "insert into past_words values(\"".$array_home[$i]["user"]["screen_name"]."\",\"".$result_word."\",".date("z").")";
				$query;
				mysql_query($query);
				
				}
			}
	}
}
mysql_close($link);	//データベース閉じる
