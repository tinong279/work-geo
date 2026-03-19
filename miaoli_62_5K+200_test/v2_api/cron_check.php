<?php
// cron_check.php - 放在 v2_api 內
require_once 'db.php';
require_once 'image_helper.php';

// 設定不限時執行，避免爬蟲太久中斷
set_time_limit(0);

echo "開始巡邏: " . date("Y-m-d H:i:s") . "\n";

foreach (['1', '2'] as $camId) {
    // 偵測 Alert 警告圖
    $remote_alert = getLatestImageUrl(getCameraBaseUrl('alert', $camId), true);
    if ($remote_alert) {
        $filename = basename($remote_alert);
        $check = $pdo->prepare("SELECT id FROM alert_image WHERE image = ? AND camera_id = ?");
        $check->execute([$filename, $camId]);

        if (!$check->fetch()) {
            sendDiscordAlert("攝影機 {$camId} 偵測到異物", $remote_alert, $filename);
            $ins = $pdo->prepare("INSERT INTO alert_image (trigger_time, image, camera_id, alert_confirm) VALUES (NOW(), ?, ?, 0)");
            $ins->execute([$filename, $camId]);
            echo "找到新圖 Ch{$camId}: {$filename}\n";
        }
    }
}
echo "巡邏結束。\n";
