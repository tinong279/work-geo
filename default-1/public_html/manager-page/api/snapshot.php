<?php
//snapshot.php
set_time_limit(5);
session_write_close();
// require("../../../session.php");

$id = 0;
$url = '';

if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$id = intval($id);
}

if ($id > 0) {
	if ($id == 11) {
		$url = "http://111.70.30.250:8020/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 12) {
		$url = "http://111.70.30.250:8030/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 21) {
		$url = "http://111.70.30.251:8020/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 22) {
		$url = "http://111.70.30.251:8030/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 31) {
		$url = "http://111.70.30.252:8020/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 32) {
		$url = "http://111.70.30.252:8030/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 41) {
		$url = "http://111.70.30.253:8020/axis-cgi/jpg/image.cgi?resolution=640x360";
	} else if ($id == 42) {
		$url = "http://111.70.30.253:8030/axis-cgi/jpg/image.cgi?resolution=640x360";
	}


	$ch = curl_init();

	// 設定 cURL 選項
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 設定連接 timeout 為 10 秒
	curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 設定執行 timeout 為 10 秒

	// 執行 cURL 請求並取得結果
	$image_data = curl_exec($ch);

	if (curl_errno($ch)) {
		// 如果有錯誤，顯示錯誤資訊
		echo 'Error:' . curl_error($ch);
	} else {
		// 設定 header 來表示這是一個圖片
		header('Content-Type: image/jpeg');

		// 輸出圖片數據
		echo $image_data;
	}

	// 關閉 cURL 會話
	curl_close($ch);
} else {
	exit();
}
