<?php
// update_cms.php
set_time_limit(5);

// 1. 啟動 Session (用來抓取登入者的帳號)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. 引入你的設定檔 (讀取資料庫帳密)
$config = require('config.php');

// 3. 建立 PDO 資料庫連線
$pdo = null;
try {
    $dsn = "mysql:host=" . $config['db']['host'] . ";dbname=" . $config['db']['dbname'] . ";charset=" . $config['db']['charset'];
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 若連線失敗不中斷程式，讓硬體控制還能繼續執行
    // error_log("DB Connection failed: " . $e->getMessage());
}

// 4. 接收前端傳來的狀態 (1 = 開啟, 0 = 關閉)
$status_input = isset($_POST['status']) ? intval($_POST['status']) : -1;

if ($status_input !== 1 && $status_input !== 0) {
    echo "error_invalid_input";
    exit;
}

$success = false;
$action_info = ($status_input === 1) ? 'cms turn on' : 'cms turn off';

// 5. 寫入操作日誌 (Log) 到資料庫
if ($pdo) {
    // 根據你的截圖，source 欄位固定寫 '首頁手動操作'
    $source_info = '首頁手動操作';
    // 取得當下時間
    $current_time = date('Y-m-d H:i:s');

    try {
        // 改為寫入 cms_status 資料表，欄位對應: trigger_time, status, source
        $sql = "INSERT INTO `cms_status` (`trigger_time`, `status`, `source`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        // $status_input 剛好就是 1 或 0
        $stmt->execute([$current_time, $status_input, $source_info]);
    } catch (PDOException $e) {
        // 寫入日誌失敗忽略，繼續控制硬體
        // error_log("Log Insert Error: " . $e->getMessage());
    }
}

// 6. 透過 Modbus TCP 控制 ICP DAS ET-7000 設備
$target_ip = "116.59.9.174";
$target_port = 502; // Modbus 通訊埠
$timeout = 3;
$modbus_val = ($status_input === 1) ? 255 : 0;

// 建立 TCP 連線
$socket = @stream_socket_client("tcp://$target_ip:$target_port", $errno, $errstr, $timeout);

if ($socket) {
    // 發送 Modbus FC5 寫入 DO0
    $packet = pack('C*', 0, 0, 0, 0, 0, 6, 1, 5, 0, 0, $modbus_val, 0);
    fwrite($socket, $packet);

    stream_set_timeout($socket, 2);
    $response = fread($socket, 1024);

    if ($response) {
        $modbus_response = unpack('C*', $response);
        // 驗證回傳
        if (
            count($modbus_response) >= 12 &&
            $modbus_response[8] == 5 &&
            $modbus_response[11] == $modbus_val
        ) {
            $success = true;
        }
    }
    fclose($socket);
}

// 7. 回傳結果給前端 JS
if ($success) {
    echo "ok";
} else {
    echo "error";
}
