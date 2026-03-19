<?php

set_time_limit(45);
//---------------------------------------------------
$url = "https://admin.icitl.com/api/taoyuan/getallroutedata";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// $header[] = "Host: 1968.freeway.gov.tw";
$header[] = "Connection: keep-alive";
$header[] = "Pragma: no-cache";
$header[] = "Cache-Control: no-cache";
$header[] = "Upgrade-Insecure-Requests: 1";
$header[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36";
$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
$header[] = "Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7";

// $header[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0";
// $header[] = "Accept: */*";
// $header[] = "Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.5,en;q=0.3";
// $header[] = "X-Requested-With: XMLHttpRequest";
// $header[] = "DNT: 1";
// $header[] = "Connection: keep-alive";
// $header[] = "Referer: https://1968.freeway.gov.tw/n_notify";
// $header[] = "Sec-Fetch-Dest: empty";
// $header[] = "Sec-Fetch-Mode: cors";
// $header[] = "Sec-Fetch-Site: same-origin";

curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_ENCODING, '');

$res = curl_exec($ch);

if ($res === false) {
	// echo curl_error($ch);
	exit();
}
curl_close($ch);

if (strlen($res) <= 0) {
	exit();
}
//---------------------------------------------------
$folderName = 'log-1/' . date("Y-m"); // 生成當前年-月格式的資料夾名稱，例如 "2024-11"
$directoryPath = $folderName;
try {
	// 檢查資料夾是否已存在，若不存在則建立
	if (!file_exists($directoryPath)) {
		if (mkdir($directoryPath, 0777, true)) {
			// echo "資料夾 '$folderName' 已成功建立。";
		} else {
			// echo "無法建立資料夾 '$folderName'。";
		}
	} else {
		// echo "資料夾 '$folderName' 已經存在。";
	}
} catch (Exception $e) {
	// Handle the exception if the file cannot be opened
	// echo "Error: " . $e->getMessage();
}
//---------------------------------------------------
$filename = $folderName . '/' . round(time()) . '.json';
file_put_contents($filename, $res);
//---------------------------------------------------
$json_obj = json_decode($res, true);

$section_json = [];
$file1 = fopen('miaoli-01.json', 'r');
if ($file1) {
	// 讀取檔案內容
	$jsonString = fread($file1, filesize('miaoli-01.json'));

	// 關閉檔案
	fclose($file1);

	// 解析 JSON 字串
	$section_json = json_decode($jsonString, true);

	// 如果解析成功，則顯示資料
	// if ($data !== null) {
	// print_r($data);
	// } else {
	// echo "解析 JSON 時出現錯誤。";
	// }
} else {
	// echo "無法開啟檔案。";
}

$final_res = '';
$check_flag1 = false;

$final_res .= '[';
for ($i = 0; $i < count($json_obj); $i++) {
	$SectionID = intval($json_obj[$i]['grab_travel_route_id']);
	$TravelTime = intval($json_obj[$i]['duration_value']);

	/*
		$TravelSpeed = intval($json_obj[$i]['distance_value']);
		$TravelSpeed /= $TravelTime;
		$TravelSpeed *= 3600;
		$TravelSpeed /= 1000;
		$TravelSpeed = number_format($TravelSpeed, 0);
		*/

	$TravelSpeed = floatval($json_obj[$i]['distance_text']) * 60 / floatval($json_obj[$i]['duration_text']);
	$TravelSpeed = number_format($TravelSpeed, 0);

	$CongestionLevel = 0;
	if ($TravelSpeed > 31) {
		$CongestionLevel = 1;
	} else if ($TravelSpeed >= 15) {
		$CongestionLevel = 2;
	} else {
		$CongestionLevel = 3;
	}

	$DataCollectTime2 = $json_obj[$i]['created_at'];

	for ($j = 0; $j < count($section_json); $j++) {
		if ($SectionID == $section_json[$j][0]) {
			if ($check_flag1 == true) {
				$final_res .= ',';
			} else {
				$check_flag1 = true;
			}

			$final_res .= '[' . $SectionID . ',';

			$final_res .= '{';
			$final_res .= '"' . 'SectionID' . '":' . '' . $SectionID . '' . ',';
			$final_res .= '"' . 'TravelTime' . '":' . '"' . $TravelTime . '"' . ',';
			$final_res .= '"' . 'TravelSpeed' . '":' . '"' . $TravelSpeed . '"' . ',';
			$final_res .= '"' . 'CongestionLevel' . '":' . '"' . $CongestionLevel . '"' . ',';
			$final_res .= '"' . 'DataCollectTime2' . '":' . '"' . $DataCollectTime2 . '"' . '';

			$final_res .= '}';

			$final_res .= ']';

			break;
		}
	}


	require("ConnMySQL.php");

	if ($db_link == TRUE) {
		$db_link->query("SET NAMES \"utf8\"");

		$sql_query = "UPDATE `06-section-list` SET `last-time`=CURRENT_TIMESTAMP(),`speed`=?, `raw`=? WHERE `id`=?";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == true) {
			$stmt->bind_param("dsi", $TravelSpeed, $res, $SectionID);
			$stmt->execute();
			$stmt->close();
		}
		$db_link->close();
	}
}
$final_res .= ']';

try {
	$test = json_decode($final_res, true);

	if (count($test) > 0) {
		$filepath = "miaoli-01-live-traffic.json";
		$file = fopen($filepath, "w+");
		fputs($file, $final_res);
		fclose($file);
	}
} catch (Exception $e) {
	// Handle the exception if the file cannot be opened
	// echo "Error: " . $e->getMessage();
}
