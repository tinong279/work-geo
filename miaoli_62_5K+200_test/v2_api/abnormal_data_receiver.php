<?php
// abnormal_data_receiver.php
require_once 'db.php';

// 接收來自 Python 的 POST 資料
$device = $_POST['device'] ?? null;

if (!$device) {
    echo json_encode(['msg' => '缺少裝置參數']);
    exit;
}

try {
    if ($device === 'utilityPower') {
        // 市電狀態：Python 傳過來的是 utilityPowerStatus
        $status = isset($_POST['utilityPowerStatus']) && $_POST['utilityPowerStatus'] == 0 ? 'OFF' : 'ON';
        $sql = "INSERT INTO sensor_record (device, value, trigger_time) VALUES ('utilityPower', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } elseif ($device === 'battery_voltage') {
        // 電池電壓：Python 傳過來的是 batteryVoltageStatus
        $voltage = $_POST['batteryVoltageStatus'] ?? 0;
        $sql = "INSERT INTO sensor_record (device, value, trigger_time) VALUES ('battery_voltage', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$voltage]);
    }

    echo json_encode(['status' => 'success', 'msg' => '資料已存入資料庫']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
