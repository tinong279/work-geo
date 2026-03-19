<?php
require_once 'db.php';

header('Content-Type: application/json');

// 1️⃣ 抓最新市電
$power = $pdo->query("
    SELECT * FROM sensor_record
    WHERE device = 'utilityPower'
    ORDER BY trigger_time DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

// 2️⃣ 抓最新電池電壓
$battery = $pdo->query("
    SELECT * FROM sensor_record
    WHERE device = 'battery_voltage'
    ORDER BY trigger_time DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

// 3️⃣ 組回傳資料
$data = [
    "power" => [
        "status" => ($power && $power['value'] == 1) ? "正常" : "異常",
        "time"   => $power['trigger_time'] ?? null
    ],
    "battery" => [
        "voltage" => $battery['voltage'] ?? 0,
        "time"    => $battery['trigger_time'] ?? null
    ]
];

echo json_encode($data);
exit;
