<?php

header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

$id=$_GET['id'];

$default="<center>Thank you for using my plugin! Visit my website <a href='http://wpmarketing.org'>http://wpmarketing.org</a></center>";
if ( ! function_exists('curl_init')) {
	echo $default;
	exit ;
}

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, "http://wpmarketing.org/plugin_feed.php?id=$id"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
curl_close($ch);     


if ($output !== false) 
	echo $output;
else 
	echo $default;
	

