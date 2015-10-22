<?php
require "mkimg.php";
require "/var/www/html/php/Oauth/Methods.php";
require "/var/www/html/php/Oauth/keys_tango.php";
$phase = $argv[1];
maketex($phase);
exec("platex result.tex");
exec("dvipdfmx result.dvi");
exec("pdfcrop result.pdf");
exec("convert -density 300 result-crop.pdf -quality 90 result.png");
exec("mv ./result.* ./images");
$str = $phase."の発見状況";
$target = "./images/result.png";
//upload($str,$target);
?>
