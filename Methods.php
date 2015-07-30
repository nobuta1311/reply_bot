<?php
/*
各種コマンドを関数にまとめました。
in_reply_toがうまくいかないです。
*/
require "OauthBase.php";
require "keys.php";
function home_timeline($num){//ホームのタイムラインを取得
        $url = "https://api.twitter.com/1.1/statuses/".__FUNCTION__.".json";
        $additional_params=array("count" => $num);
        $json = oauth($url,$additional_params,false);
        $array_home = json_decode($json,true);
        return $array_home;
}
function update($string,$reply_to=null){//ツイート
        $url = "https://api.twitter.com/1.1/statuses/".__FUNCTION__.".json";
        $additional_params=array(
        "status" => $string,
        );
        if($reply_to!=null){
                $additional_params["in_reply_to_status_id"] = $reply_to;
        }
        $json = oauth($url,$additional_params,true);
        $array_home = json_decode($json,true);
        return $array_home;
}
function user_timeline($user,$num){//ユーザタイムライン取得
        $url = "https://api.twitter.com/1.1/statuses/".__FUNCTION__.".json";
        if(is_numeric($name)){
                $additional_params = array("user_id" => $name);
        }else {
                $additional_params = array("screen_name"=>$name);
        }
        $additional_params["count"]=$num;

        $json = oauth($url,$additional_params,false);
        $array_line = json_decode($json,true);
        return $array_line;
}
function show($name){   //アカウント名からユーザ情報取得
        $url = "https://api.twitter.com/1.1/users/".__FUNCTION__.".json";
        if(is_numeric($name)){
                $additional_params = array("user_id" => $name);
        }else {
                $additional_params = array("screen_name"=>$name);
        }
        $json = oauth($url,$additional_params,false);
        $array_info = json_decode($json,true);
        return $array_info;
}
function followers($name){//あるユーザのフォロワーリストを取得する
        $url = "https://api.twitter.com/1.1/".__FUNCTION__."/ids.json";
        if(is_numeric($name)){
                $additional_params = array("user_id" => $name);
        }else {
                $additional_params = array("screen_name"=>$name);
        }
        $json = oauth($url,$additional_params,false);
        $array_info = json_decode($json,true);
        return $array_info;
}
function mentions_timeline($num){//メンションを取得うまくいかね
        $url = "https://api.twitter.com/1.1/statuses/".__FUNCTION__.".json";
        $additional_params = array("count" => $num);
        $json = oauth($url,$additional_params,false);
        echo $json;
        $array_info = json_decode($json,true);
        return $array_info;
}
function upload($string,$media,$reply_to=null){ //画像つきツイート
        $url = "https://api.twitter.com/1.1/statuses/update_with_media.json";
        $additional_params = array(
                "status"=>$string,
                "media[]" => "@".$media,
                );
        if($reply_to!=null){$additional_params["in_reply_to_status_id"] = $reply_to;}
        $json = oauth($url,$additional_params,true);
        $array_info = json_decode($json,true);
        return $array_info;
}
?>
