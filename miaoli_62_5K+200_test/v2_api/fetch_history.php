<?php
// fetch_history.php 修正版：間隔 1 小時
require_once 'db.php';

$type = $_GET['type'] ?? 'utilityPower';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

try {
    // 使用 DATE_FORMAT 將時間縮減到「小時」，並透過 GROUP BY 達成 1 小時一筆
    $sql = "SELECT 
                value, 
                DATE_FORMAT(trigger_time, '%Y-%m-%d %H:00:00') as trigger_time 
            FROM sensor_record 
            WHERE device = ? 
              AND trigger_time BETWEEN ? AND ? 
            GROUP BY DATE_FORMAT(trigger_time, '%H %d %m %Y') 
            ORDER BY trigger_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$type, $start, $end]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
