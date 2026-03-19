<?php
// do_confirm.php
require_once 'db.php';

$filename = $_GET['image'] ?? null;
$remoteUrl = $_GET['url'] ?? null;
// 關鍵：接收來自 preview_alert.php 的 cam_id，若沒傳則預設為 1
$camId = $_GET['cam_id'] ?? '1';

if ($filename && $remoteUrl) {
    try {
        // 1. 設定動態儲存路徑：local_alerts/ch1/ 或 local_alerts/ch2/
        $saveDir = 'local_alerts/ch' . $camId . '/';

        // 自動檢查並建立子資料夾
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        $localPath = $saveDir . $filename;

        // 2. 執行下載動作
        $imgData = @file_get_contents($remoteUrl);

        if ($imgData !== false) {
            // 寫入檔案
            file_put_contents($localPath, $imgData);

            // 3. 更新資料庫狀態為 1 (正式發佈)
            $sql = "UPDATE alert_image SET alert_confirm = 1 WHERE image = ? AND alert_confirm = 0";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$filename]);

            // 確認成功後，彈出視窗並跳回主頁面
            echo "<script>alert('攝影機 {$camId} 告警已發佈並下載！'); window.location.href='index.php';</script>";
        } else {
            throw new Exception("無法下載遠端圖片。");
        }
    } catch (Exception $e) {
        die("處理失敗：" . $e->getMessage());
    }
} else {
    die("缺少必要參數：image, url 或 cam_id");
}
