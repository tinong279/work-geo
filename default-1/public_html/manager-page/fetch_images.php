<?php
// fetch_images.php

// 1. 解開 Session 鎖定，避免卡死伺服器
session_write_close();
header('Content-Type: application/json');

// 2. 取得時間戳記
$t = time();

// 3. 設定外部政府伺服器的警告圖片絕對網址
$alert1_url = "http://192.168.60.79/manager-page/alarm/1-1.jpg?t=" . $t;
$alert2_url = "http://192.168.60.79/manager-page/alarm/1-2.jpg?t=" . $t;

// 4. 將資料打包 (即時擷圖維持本地 API，警告擷圖指向 192.168.60.79)
$response = [
    "cam1"   => "api/snapshot.php?id=11&t=" . $t,
    "cam2"   => "api/snapshot.php?id=12&t=" . $t,
    "alert1" => $alert1_url,
    "alert2" => $alert2_url
];

echo json_encode($response);
exit;
