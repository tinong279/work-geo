<?php

/**
 * 檔案名稱：fetch-dahu-live.php
 * 功能：計算大湖 API 即時時速與擁擠度，產生地圖數據檔
 */

set_time_limit(45);

$url = "https://admin.icitl.com/api/dahu/getallroutedata";

// 1. 抓取 API 資料
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$res = curl_exec($ch);
curl_close($ch);

if (!$res || strlen($res) <= 0) exit("Error: 抓取失敗");

// 2. 存檔備份 (比照原本 log-1 邏輯，改為 log-dahu)
$folderName = 'log-dahu/' . date("Y-m");
if (!file_exists($folderName)) {
    mkdir($folderName, 0777, true);
}
$logFilename = $folderName . '/' . time() . '.json';
file_put_contents($logFilename, $res);

$json_obj = json_decode($res, true);

// 3. 檢查大湖座標檔是否存在 (用於比對 ID)
$coordsFile = 'dahu-coords.json';
if (!file_exists($coordsFile)) exit("Error: 找不到 dahu-coords.json，請先執行 convert_dahu.php");
$coords_json = json_decode(file_get_contents($coordsFile), true);
$coords_ids = array_column($coords_json, 0);

$final_traffic = [];

foreach ($json_obj as $item) {
    $SectionID = intval($item['grab_travel_route_id']);

    // 只處理在座標檔裡有定義的路段
    if (in_array($SectionID, $coords_ids)) {

        // 時速算法：(距離km * 60) / 時間min
        $dist = floatval($item['distance_text']);
        $time_min = floatval($item['duration_text']);

        if ($time_min > 0) {
            $TravelSpeed = round($dist * 60 / $time_min);
        } else {
            $TravelSpeed = 0;
        }

        // 擁擠等級判定 (CongestionLevel)
        // 1: 順暢(綠), 2: 車多(橘), 3: 壅塞(紅)
        $level = "1";
        if ($TravelSpeed < 15) {
            $level = "3";
        } else if ($TravelSpeed < 31) {
            $level = "2";
        }

        // 符合原本地圖格式的陣列結構
        $final_traffic[] = [
            $SectionID,
            [
                "SectionID" => $SectionID,
                "TravelTime" => intval($item['duration_value']),
                "TravelSpeed" => strval($TravelSpeed),
                "CongestionLevel" => $level,
                "DataCollectTime2" => $item['created_at']
            ]
        ];
    }
}

// 4. 寫入 JSON 檔給地圖前端讀取
if (count($final_traffic) > 0) {
    file_put_contents("dahu-live-traffic.json", json_encode($final_traffic, JSON_UNESCAPED_UNICODE));
    echo "即時路況更新成功： " . count($final_traffic) . " 筆資料。";
} else {
    echo "比對失敗：沒有匹配的 ID。";
}
