<?php
require "mkimg.php";
require "Methods.php";
$phase = $argv[1];
maketex($phase);
exec("platex result.tex");
exec("dvipdfmx result.dvi");
exec("pdfcrop result.pdf");
exec("convert -density 300 result-crop.pdf -quality 90 result.png");
upload($phase."の発見状況","./result.png","");
?>
