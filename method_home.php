<?php //ツイートするだけの機能
require "keys.php";
function home($count=20,$than_new=0,$than_old=0){
global $params_b,$signature_key; //関数外の変数を利用するため
$request_method = "GET";
$request_url = "https://api.twitter.com/1.1/statuses/home_timeline.json";
/*
$params_a = array(
	"count"=>$count,
);
if($than_new >0)
	$params_a["max_id"] = $than_new;
if($than_old >0)
	$params_a["since_id"] = $than_old;
*/	
//$params_c = array_merge($params_a,  $params_b);
$params_c = $params_b;


$signature = make_sign($signature_key, $request_method, $request_url, $params_c);
$params_c["oauth_signature"]=$signature;
$header_params = http_build_query($params_c,"",",");
//$body_params = http_build_query($params_a,"","&");
$json = @file_get_contents(
	$request_url,
	false,
	stream_context_create(
		array(
		    "http" => array(
        	     "method" => $request_method, //リクエストメソッド
        	     "header" => array(                   //カスタムヘッダー
        	     		"Authorization: OAuth ".$header_params,
        		     	),
  //      	     "content" => $body_params, //リクエストボディ
		     		)
		)	
     	)	
);
 return $json;
}
?>
