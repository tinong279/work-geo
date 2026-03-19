<?php
// db.php
$config = require 'config.php';

// 設定時區，確保與 Laravel 專案邏輯一致
date_default_timezone_set('Asia/Taipei');

try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 開啟錯誤追蹤模式
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 預設以關聯陣列回傳
        PDO::ATTR_EMULATE_PREPARES   => false,                  // 關閉模擬預處理，增強安全性
    ];

    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);
} catch (\PDOException $e) {
    // 發生錯誤時，回傳 JSON 格式方便 API 測試
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['msg' => '資料庫連線失敗', 'error' => $e->getMessage()]);
    exit;
}
