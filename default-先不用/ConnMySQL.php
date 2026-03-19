<?php
// 資料庫主機設定
$db_host = "localhost";
$db_username = "root";  // 改成 root
$db_password = "";      // 改成空字串
$db_name = "miaoli-62";

// 建立連線 (建議拿掉 @ 方便除錯)
$db_link = new mysqli($db_host, $db_username, $db_password, $db_name);

// 檢查連線是否成功
if ($db_link->connect_error) {
	die("連線失敗: " . $db_link->connect_error);
}
