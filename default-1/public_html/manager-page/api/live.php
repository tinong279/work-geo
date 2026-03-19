<?php

$stream_time = 600;

set_time_limit($stream_time);

require("../../../session.php");

$id = 0;
$url = '';

if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$id = intval($id);
}

if ($id > 0) {
	if ($id == 11) {
		$url = "http://111.70.30.250:8020/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 12) {
		$url = "http://111.70.30.250:8030/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 21) {
		$url = "http://111.70.30.251:8020/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 22) {
		$url = "http://111.70.30.251:8030/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 31) {
		$url = "http://111.70.30.252:8020/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 32) {
		$url = "http://111.70.30.252:8030/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 41) {
		$url = "http://111.70.30.253:8020/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	} else if ($id == 42) {
		$url = "http://111.70.30.253:8030/axis-cgi/mjpg/video.cgi?fps=1&resolution=640x360";
	}

	header('Cache-Control: no-cache');
	header('Connection: close');
	header('Content-type: multipart/x-mixed-replace; boundary=myboundary');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, $stream_time);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	$result = curl_exec($ch);
	echo $result;
	curl_close($ch);
} else {
	exit();
}
