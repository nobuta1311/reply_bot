<?php
error_reporting(0);
while(true){
	$month = date("n");
	$day = date("j");
	$yobi = date("D"); //英語文字列で
	$tsusan = date("z");//一年の通算日数 0から265
	$hour = date("G");  //0から23
	$minutes = date("i");
	$second = date("s");
	
	if($second==0 && $minutes%3==0){
		exec("php -c '' 'reply.php' > /dev/null &");
		sleep(1);
	}

}
?>
