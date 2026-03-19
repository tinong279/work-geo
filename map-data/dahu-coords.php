<?php

/**
 * 檔案名稱：convert_dahu.php
 * 功能：抓取大湖 API，將 overview_polyline 解碼為座標點列，產生地理資訊檔
 */

// Google Polyline 演算法解碼函式
function decodePolyline($encoded)
{
    $length = strlen($encoded);
    $index = 0;
    $points = [];
    $lat = 0;
    $lng = 0;

    while ($index < $length) {
        $b = 0;
        $shift = 0;
        $result = 0;
        do {
            $b = ord($encoded[$index++]) - 63;
            $result |= ($b & 0x1f) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
        $lat += $dlat;

        $shift = 0;
        $result = 0;
        do {
            $b = ord($encoded[$index++]) - 63;
            $result |= ($b & 0x1f) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
        $lng += $dlng;

        $points[] = [round($lat * 1e-5, 6), round($lng * 1e-5, 6)];
    }
    return $points;
}

// 1. 設定來源 URL
$apiUrl = "https://admin.icitl.com/api/dahu/getallroutedata";

// 2. 抓取 API 資料
$res = file_get_contents($apiUrl);
if ($res === false) die("Error: 無法連線至 API 伺服器");

$data = json_decode($res, true);
if (!$data) die("Error: API 回傳格式錯誤");

$final_geojson = [];

foreach ($data as $item) {
    // 嚴格比對：取得路段 ID
    $sid = intval($item['grab_travel_route_id']);

    // 解析 full_data 內的 overview_polyline
    $full_data = json_decode($item['full_data'], true);

    if (isset($full_data['routes'][0]['overview_polyline']['points'])) {
        $encodedString = $full_data['routes'][0]['overview_polyline']['points'];

        // 核心解碼動作
        $path = decodePolyline($encodedString);

        // 格式化為：[ID, [座標陣列]]
        $final_geojson[] = [$sid, $path];
    }
}

// 3. 儲存檔案
$filepath = "dahu-coords.json";
file_put_contents($filepath, json_encode($final_geojson, JSON_UNESCAPED_UNICODE));

echo "--- 座標轉換成功 ---<br>";
echo "已生成檔案：{$filepath}<br>";
echo "共計路段： " . count($final_geojson) . " 條";
