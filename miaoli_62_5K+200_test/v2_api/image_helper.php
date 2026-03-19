<?php
// image_helper.php

/**
 * 從遠端目錄抓取最新圖片
 * @param string $url 基礎網址
 * @param bool $recursive 是否進入日期子資料夾 (用於 alert 類型)
 */
function getLatestImageUrl($url, $recursive = false)
{
    if (!$url) return null;
    $targetUrl = $url;

    $html = @file_get_contents($url);
    if (!$html) return null;

    if ($recursive) {
        preg_match_all('/href="[^"]*(\d{4}-\d{2}-\d{2})\/"/i', $html, $matches);
        $folders = $matches[1] ?? [];
        if (empty($folders)) return null;

        rsort($folders);
        $latestFolder = $folders[0];
        $targetUrl = rtrim($url, '/') . '/' . $latestFolder . '/';
        $html = @file_get_contents($targetUrl);
        if (!$html) return null;
    }

    preg_match_all('/href="([^"]+\.jpg)"/i', $html, $matches);
    $images = $matches[1] ?? [];
    if (empty($images)) return null;

    rsort($images);
    $latestFile = basename($images[0]);

    return rtrim($targetUrl, '/') . '/' . $latestFile;
}

/**
 * 發送 Discord 通報
 */
function sendDiscordAlert($message, $imageUrl, $filename)
{
    $webhookUrl = "https://discord.com/api/webhooks/1465642891018895548/spJ8BtIAc93uuzpjspU0OE3HVGHOWL4MckRoJ3E5PwRj42V3J2O5ndS3emhPQj7iyZKL";

    // 注意：正式環境建議將 localhost 改為實體 IP
    $confirmUrl = "http://localhost/miaoli_62_5K+200_test/v2_api/preview_alert.php?image=" . urlencode($filename) . "&url=" . urlencode($imageUrl);

    $payload = json_encode([
        "content" => "🚨 **落石告警系統通報**",
        "embeds" => [[
            "title" => "⚠️ 需人工確認 (攝影機影像)",
            "description" => $message . "\n\n[🔗 點此進入網頁確認並通報](" . $confirmUrl . ")",
            "color" => 15158332,
            "image" => ["url" => $imageUrl],
            "footer" => ["text" => "未經人工確認前，主網頁監控畫面不會更新"],
            "timestamp" => date("c")
        ]]
    ]);

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * 從資料庫抓取最新一筆「已確認」的警告影像
 * 優先檢查本地子資料夾 local_alerts/ch1/ 或 local_alerts/ch2/
 */
function getDbConfirmedImage($camera_id, $pdo)
{
    $sql = "SELECT image, trigger_time FROM alert_image 
            WHERE camera_id = ? AND alert_confirm = 1 
            ORDER BY trigger_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$camera_id]);
    $row = $stmt->fetch();

    if ($row) {
        $filename = $row['image'];

        // --- ✅ 修正點：根據 camera_id 進入對應的 ch1 或 ch2 資料夾 ---
        // 因為你的 index.php 在 v2_api 內，local_alerts 也在同層
        $localPath = 'local_alerts/ch' . $camera_id . '/' . $filename;

        if (file_exists($localPath)) {
            // 如果本地子資料夾有圖，直接回傳該路徑
            return $localPath;
        }

        // --- 備援邏輯：本地沒圖（可能是舊資料未下載）才連回遠端 IP ---
        $dateFolder = date("Y-m-d", strtotime($row['trigger_time']));
        return "http://220.133.109.237:85/warning_img/ch{$camera_id}/line-notify/{$dateFolder}/{$filename}";
    }
    return "loading.gif";
}
