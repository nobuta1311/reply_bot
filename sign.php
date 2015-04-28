<?php
require "keys.php";
$params_b = array(
	"oauth_consumer_key" => $api_key,
	"oauth_token"=> $access_token,
	"oauth_nonce"=>microtime(),
	"oauth_signature_method"=>"HMAC-SHA1",
	"oauth_timestamp"=>time(),
	"oauth_version"=>"1.0",
);

$signature_key = rawurlencode($api_secret)."&".rawurlencode($access_token_secret);

function make_sign($signature_key,$request_method,$request_url,$params_c){
ksort($params_c);
//配列[$params_c]を[キー=値&キー=値...]の文字列に変換

$signature_params = str_replace(array("+","%7E"),array("%20","~"),http_build_query($params_c,"","&"));

//変換した文字列をURLエンコードする
$signature_params = rawurlencode($signature_params);

//リクエストメソッドをURLエンコードする
$encoded_request_method = rawurlencode($request_method);
  
//リクエストURLをURLエンコードする
$encoded_request_url = rawurlencode($request_url);

//リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
$signature_data = "{$encoded_request_method}&{$encoded_request_url}&{$signature_params}";

//キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
$hash = hash_hmac("sha1",$signature_data,$signature_key,TRUE);

//base64エンコードして、署名[$signature]が完成する
$signature = base64_encode($hash);
return $signature;
}
?>
