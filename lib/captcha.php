<?php
session_start();
$ranStr = md5(microtime());
$ranStr = substr($ranStr, 0, 6);
$_SESSION["HUE_CAPCHA"]=$ranStr;
$im = imagecreatetruecolor(70, 28);
$bg = imagecolorallocate($im, 22, 86, 165); /* background color blue */
$fg = imagecolorallocate($im, 255, 255, 255);/* text color white */
imagefill($im, 0, 0, $bg);
imagestring($im, 6, 6, 6,  $ranStr, $fg);
header("Cache-Control: no-cache, must-revalidate");
header('Content-type: image/png');
imagepng($im);
imagedestroy($im);
?>


