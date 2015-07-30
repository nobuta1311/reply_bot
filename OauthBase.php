<?php
function oauth($url,$additional_params,$ispost){
require "keys.php";
$oauth_params = array(
           'oauth_consumer_key'     => $api_key,
           'oauth_signature_method' => 'HMAC-SHA1',
           'oauth_timestamp'        => time(),
           'oauth_version'          => '1.0a',
           'oauth_nonce'            => md5(mt_rand()),
           'oauth_token'            => $access_token,
);
$params_for_base = $additional_params;
unset($params_for_base["media[]"]);
$base = $oauth_params+$params_for_base;
$key = array($api_secret,$access_token_secret);

uksort($base, 'strnatcmp');
if($ispost == true){$method = "POST";}else{$method="GET";}
$oauth_params['oauth_signature'] = base64_encode(hash_hmac(
    'sha1',
       implode('&', array_map('rawurlencode', array(
       $method,
       $url,
      str_replace(
      array('+', '%7E'), 
      array('%20', '~'), 
      http_build_query($base, '', '&')
      )
      ))),
      implode('&', array_map('rawurlencode', $key)),
      true
      ));
      foreach ($oauth_params as $name => $value) {
      $items[] = sprintf('%s="%s"', urlencode($name), urlencode($value));
      }
      $header = 'Authorization: OAuth ' . implode(', ', $items);

// 新しい cURL リソースを作成します
$ch = curl_init();
//画像のとき
if($url=="https://api.twitter.com/1.1/statuses/update_with_media.json"){

    $opt_array=array(
        CURLOPT_URL =>$url.'?'.http_build_query($params_for_base,"","&"),
        CURLOPT_POST =>true,
             
        CURLOPT_POSTFIELDS => $additional_params,
        CURLOPT_HTTPHEADER => array($header),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "gzip",
        );
                                        
}
//POSTのとき
else if($ispost == true){
        $opt_array = array(
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,        
        CURLOPT_POSTFIELDS     => http_build_query($additional_params, '', '&'),
        CURLOPT_HTTPHEADER     => array($header),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => 'gzip',);
}
//GETのとき
else {
$opt_array = array(
            CURLOPT_URL            => $url. '?' . http_build_query($additional_params, '', '&'),
            CURLOPT_HTTPHEADER     => array($header),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,                            
            CURLOPT_ENCODING       => 'gzip',
);
}
//print_r($opt_array);
curl_setopt_array($ch,$opt_array);

// URL を取得し、ブラウザに渡します
$result =  curl_exec($ch);

// cURL リソースを閉じ、システムリソースを解放します
curl_close($ch);
return $result;
}
?>

