<?php
function maketex($phase){
$phase = substr($phase,1);
require "./mysql_key.php";
$link = mysql_connect("localhost",$mysql_user,$mysql_pass);
$db_selected = mysql_select_db("phptest",$link);
$text = "\documentclass{jarticle}
\usepackage{colortbl}
\usepackage{graphicx}
\usepackage{ascmac}
\pagestyle{empty}\n";
for($i=1;$i<100;$i++){   //過去100日のどこで出てるか？
$text .= "\definecolor{color".$i."}{rgb}{ .99, .".sprintf("%02d", $i).", .10}\n";
}
$text .= "\definecolor{color100}{rgb}{ .99, 1, .10}\n";
$text .= "\definecolor{colornew}{rgb}{.30,.50,.95}\n";
$text .= "\definecolor{colorwhite}{rgb}{1,1,1}\n";
$text .= "\begin{document}\n";
$text .= "\begin{table}
\scalebox{0.8}[0.9]{
\begin{tabular}{|c|c|c|c|c|c|c|c|c|c|}\hline\n";

      //  $phase = 19;
        $number = 4000+$phase*100-100;
        for($k=1;$k<=100;$k++){
                $number++;
                $query_correct = "select distinct words.phase,words.number,words.meaning,words.word,day from past_words inner join words on past_words.word=words.word and words.number>=4000 and day>".(date("z")-100)." and number=".$number." order by number;";
                if($temp =  mysql_fetch_assoc(mysql_query($query_correct))){   //みつかってたら
                        print_r($temp);
                        $query_new = "select * from past_words where day = ".date("z")." and word = \"".$temp["word"]."\"";
                        if($result_new=mysql_fetch_assoc(mysql_query($query_new))){
                                $text.="\cellcolor{colornew} ";
                                $text.=$temp["word"]." ";
                        }else
                        {
                           // date("z")-$result_day>30 || (date("z")-$result_day<0 && (366-$result_day)+("z")>30)
                            /*
                                if(date("z"-$temp["day"]>0))$days=date("z")-$temp["day"];
                                else $days=366-date("z")+$temp["day"];
                                if($days<=0)$days=1;
                                echo "days:".$days;
                                if($days>70)
                                    $text.="";
                                else
                                    $text.="\cellcolor{color".(1/$days)."} ";
                             */
                            $fromlast=(date("z")-$temp["day"]+1);
                            $text.="\cellcolor{color".$fromlast."}";
                            if($fromlast>40){//結構前に見つかってるから隠す
                                $hidden = substr($temp["word"],0,3);
                                for($j=0;$j<strlen($temp["word"])-3;$j++){$hidden.="*";}
                                    $text.=$hidden." ";
                            }
                            else{
                                $text.=$temp["word"]." ";
                            }
                        }
                }else{  //みつかってなければ
                    echo "sippai";
                $query_hidden = "select * from words where number=".$number;
                $result_numword = mysql_fetch_assoc(mysql_query($query_hidden));
                $hidden = substr($result_numword["word"],0,3);
                $text.="\cellcolor{white}";
                for($j=0;$j<strlen($result_numword["word"])-3;$j++){$hidden.="*";}
                $text.=$hidden." ";
                }

                if($k%5==0){$text.= "\\\\ \\hline\n";}
                else{$text.=" & ";}
        }

$text.="\end{tabular}
}
\end{table}
\end{document}";
file_put_contents("result.tex",$text);

        mysql_close($link);
}      
?>
