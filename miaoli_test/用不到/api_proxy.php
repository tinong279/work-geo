<?php
header('Content-Type: application/json');

// 1. 抓取高公局 XML (測試時請確認伺服器可連外網)
$xml_url = "https://tisvcloud.freeway.gov.tw/history/motc20/LiveTraffic.xml";
$xml_content = file_get_contents($xml_url);
$xml = simplexml_load_string($xml_content);

// 2. 讀取座標對照檔 (這需要你預先準備好的 SectionID 座標檔)
$geo_data = json_decode(file_get_contents('section_coords.json'), true);

$output = [];

foreach ($xml->Info as $info) {
    $sid = (string)$info['sectionid'];
    $speed = intval($info['travelspeed']);

    // 邏輯判定顏色 (你照片中框起來的邏輯)
    if ($speed >= 60) {
        $color = "#00ff00"; // 暢通 (綠)
    } elseif ($speed >= 40) {
        $color = "#ffff00"; // 緩行 (黃)
    } else {
        $color = "#ff0000"; // 壅塞 (紅)
    }

    // 只處理我們有座標的路段
    if (isset($geo_data[$sid])) {
        $output[] = [
            "id" => $sid,
            "speed" => $speed,
            "color" => $color,
            "path" => $geo_data[$sid] // 座標陣列
        ];
    }
}

echo json_encode($output);
