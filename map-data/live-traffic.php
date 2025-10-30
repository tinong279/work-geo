<?php
	
	set_time_limit(45);
	//---------------------------------------------------
	$url = "https://tisvcloud.freeway.gov.tw/history/motc20/LiveTraffic.xml";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	
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
	curl_setopt($ch, CURLOPT_ENCODING , '');
	
	$res = curl_exec($ch);
	
	if ($res === false)
	{
		// echo curl_error($ch);
		exit();
	}
	curl_close($ch);
	
	if (strlen($res) <= 0)
	{
		exit();
	}
	//---------------------------------------------------
	$xml = simplexml_load_string($res);
	$json_obj = json_encode($xml);
	$json_obj = json_decode($json_obj, true);
	$json_obj = $json_obj['LiveTraffics']['LiveTraffic'];
	
	$section_json = [];
	$file1 = fopen('section-shape.json', 'r');
	if ($file1) {
		// 讀取檔案內容
		$jsonString = fread($file1, filesize('section-shape.json'));
		
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
	
	// print_r($section_json);
	// exit();
	
	$final_res = '';
	$check_flag1 = false;
	
	$final_res .= '[';
	for($i=0; $i<count($json_obj); $i++)
	{
		$json_obj[$i]['SectionID'] = intval($json_obj[$i]['SectionID']);
		$str_buf1 = json_encode($json_obj[$i]);
		$cal_buf1 = $json_obj[$i]['SectionID'];
		$speed_buf1 = $json_obj[$i]['TravelSpeed'];
		$speed_buf1 = strval($speed_buf1);
		$speed_buf1 = floatval($speed_buf1);
		
		for ($j=0; $j<count($section_json); $j++)
		{
			if ($json_obj[$i]['SectionID'] == $section_json[$j][0])
			{
				if ($check_flag1 == true)
				{
					$final_res .= ',';
				}
				else
				{
					$check_flag1 = true;
				}
				
				$final_res .= '[' . $json_obj[$i]['SectionID'] . ',' . json_encode($json_obj[$i]) . ']';
				break;
			}
		}
	}
	$final_res .= ']';
	
    try
    {
        $test = json_decode($final_res, true);
        if (count($test) > 0)
        {
        	$filepath = "live-traffic.json";
        	$file = fopen($filepath, "w+");
        	fputs($file, $final_res);
        	fclose($file);
        }
    }
    catch (Exception $e)
    {
      // Handle the exception if the file cannot be opened
      // echo "Error: " . $e->getMessage();
    }
	
?>
