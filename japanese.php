<?php

function IntoJapanese($word_num){
//デ辞蔵のRestAPIによるXMLファイルから内容を取得する。
$url = "http://public.dejizo.jp/NetDicV09.asmx/SearchDicItemLite?Dic=EJdict&Word=".$word_num."&Scope=HEADWORD&Match=EXACT&Merge=AND&Prof=XHTML&PageSize=1&PageIndex=0";
$xml = file_get_contents($url);
$object = simplexml_load_string($xml);
$arr =get_object_vars($object);
$arr = get_object_vars($arr["TitleList"]);
$arr = get_object_vars($arr["DicItemTitle"]);
$id = $arr["ItemID"];
$url ="http://public.dejizo.jp/NetDicV09.asmx/GetDicItemLite?Dic=EJdict&Item=".$id."&Loc=&Prof=XHTML";
$xml = file_get_contents($url);
$object = simplexml_load_string($xml);
$arr = get_object_vars($object);
$arr = get_object_vars($arr["Body"]);
$arr = get_object_vars($arr["div"]);
return  $arr["div"];
}
/*
//ファイルの内容の読み込み
$json = file_get_contents($url);
//連想配列にする
$arr = json_decode($json,true);
//駅情報だけ取得する
$station_array = $arr["response"]["station"];
//表示
echo "<pre>";
var_dump($station_array);
*/
?>
