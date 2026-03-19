<?php
/* ==========================================================================
   1️⃣ 影像 Proxy 處理區塊 (處理 Axis 攝影機即時擷圖)
   ========================================================================== */

// 如果帶有 proxy=1 參數，則進入轉發模式，解決跨網域與帳密驗證問題
if (isset($_GET['proxy']) && $_GET['proxy'] == 1) {
    ini_set('display_errors', 0);
    error_reporting(0);

    $cam = $_GET['cam'] ?? '1';

    // 定義 Axis 攝影機的實體路徑
    $urls = [
        '1' => 'http://116.59.9.174:8020/axis-cgi/jpg/image.cgi?resolution=640x360',
        '2' => 'http://116.59.9.174:8030/axis-cgi/jpg/image.cgi?resolution=640x360',
    ];

    $username = "root";       // Axis 攝影機帳號
    $password = "qaz24238721"; // Axis 攝影機密碼

    if (!isset($urls[$cam])) exit;

    $ch = curl_init($urls[$cam]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $image = curl_exec($ch);
    curl_close($ch);

    header("Content-Type: image/jpeg");
    header("Content-Length: " . strlen($image));
    header("Cache-Control: no-cache");

    echo $image;
    exit;
}

/* ==========================================================================
   2️⃣ 系統載入與資料庫連線
   ========================================================================== */

// 載入 5K+200 站點專用的資料庫設定與連線檔案
require_once 'db.php';

/* ==========================================================================
   3️⃣ 警告截圖邏輯 (JSON 模式)
   ========================================================================== */

// 舊伺服器的圖片基礎網址
$base_url = "https://fvfpvx68.geonerve-iot.com/miaoli_62_5K_200/public/alertNotificationImg/";

$alert1_url = 'loading.gif';
$alert2_url = 'loading.gif';

if (isset($pdo)) {
    try {
        // --- 抓取 Camera 1 最新且「已確認 (alert_confirm=1)」的警告圖 ---
        $sql1 = "SELECT `image`, `trigger_time` 
                 FROM `alert_image` 
                 WHERE `camera_id` = 1 
                 AND `alert_confirm` = 1 
                 ORDER BY `trigger_time` DESC 
                 LIMIT 1";
        $stmt1 = $pdo->query($sql1);
        if ($row1 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            // 根據 trigger_time 格式化日期資料夾路徑
            $date_folder1 = date('Y-m-d', strtotime($row1['trigger_time']));
            $alert1_url = $base_url . $date_folder1 . '/' . $row1['image'];
        }

        // --- 抓取 Camera 2 最新且「已確認 (alert_confirm=1)」的警告圖 ---
        $sql2 = "SELECT `image`, `trigger_time` 
                 FROM `alert_image` 
                 WHERE `camera_id` = 2 
                 AND `alert_confirm` = 1 
                 ORDER BY `trigger_time` DESC 
                 LIMIT 1";
        $stmt2 = $pdo->query($sql2);
        if ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $date_folder2 = date('Y-m-d', strtotime($row2['trigger_time']));
            $alert2_url = $base_url . $date_folder2 . '/' . $row2['image'];
        }
    } catch (PDOException $e) {
        // 若有錯誤可視需求紀錄紀錄
    }
}

// 組合 JSON 資料回傳給前端 slope_5k200.php
$data = [
    'cam1'   => 'fetch_images.php?proxy=1&cam=1',
    'cam2'   => 'fetch_images.php?proxy=1&cam=2',
    'alert1' => $alert1_url,
    'alert2' => $alert2_url
];

header('Content-Type: application/json');
echo json_encode($data);
exit;
