<?php //ツイートするだけの機能
require "keys.php";
//require "sign.php";
function tweet($status,$reply_to){
global $params_b,$signature_key; //関数外の変数を利用するため
$request_method = "POST";
$request_url = "https://api.twitter.com/1.1/statuses/update.json";
$params_a = array(
	"status" => $status,
//      "possibly_sensitive" => false,
//        "lat" => 34.403083,//広島大学
//        "long" => 132.71475,
//      "place_id" => "",
   "display_coordinates" => true,
  // "trim_user" => false,
//      "media_ids" => "",
);
if($reply_to != 0){
	$params_a["in_reply_to_status_id"] = $reply_to;//リプライ先ツイートID
}
$params_c = array_merge($params_a,  $params_b);
$signature = make_sign($signature_key, $request_method, $request_url, $params_c);
$params_c["oauth_signature"]=$signature;
$header_params = http_build_query($params_c,"",",");
$body_params = http_build_query($params_a,"","&");
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
        	     "content" => $body_params, //リクエストボディ
		     		)
		)	
     	)	
);
 return $json;
}
?>
