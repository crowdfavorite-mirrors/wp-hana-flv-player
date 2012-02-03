<?php
	// Open Source FLV Player
	// Documentation can be found at http://www.osflv.com/Documentation.html

	include ('flash/flash.php');
	flvheader();
	flv("video.flv", -1, -1, '', '', false, false);
?>