<?php
	
	set_time_limit(15);
	//---------------------------------------------------
	try
	{
		$_time1 = new DateTime;
		$_time1 = $_time1->format('Y-m-d H:i:s');
		
		$url = "https://opendata.cwa.gov.tw/fileapi/v1/opendataapi/O-A0003-001?Authorization=CWA-827EFF14-6CBF-4820-9112-149A6B257B4E&format=JSON";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		
		$header[] = "cache-control: no-cache";
		$header[] = "pragma: no-cache";
		$header[] = "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.4844.74 Safari/537.36";
		$header[] = "cookie: _ga=GA1.2.12345678.9876543210";
		$header[] = "sec-fetch-site: none";
		$header[] = "sec-fetch-user: ?1";
		$header[] = "sec-ch-ua-platform: \"Windows\"";
		$header[] = "sec-ch-ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"99\", \"Google Chrome\";v=\"99\"";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		
		$res = curl_exec($ch);
		
		if ($res === false)
		{
			echo curl_error($ch);
			exit();
		}
		curl_close($ch);
		
		if (strlen($res) <= 0)
		{
			exit();
		}
		
		$res_json = json_decode($res, true);
		// $res_json = $res_json['cwaopendata']['dataset']['Station'];
		
		$export_data = '[';
		
		for ($i=0; $i<count($res_json['cwaopendata']['dataset']['Station']); $i++)
		{
			$arr_buf1 = $res_json['cwaopendata']['dataset']['Station'][$i];
			$str_buf0 = '';
			
			if ($i >0)
			{
				$str_buf0 .= ',';
			}
			$str_buf0 .= '[';
			$str_buf0 .= $arr_buf1['GeoInfo']['Coordinates'][1]['StationLatitude'] . ',';
			$str_buf0 .= $arr_buf1['GeoInfo']['Coordinates'][1]['StationLongitude'] . ',';
			// $str_buf0 .= '"' . $arr_buf1['WeatherElement']['Weather'] . '"';
			
			$str_buf1 = $arr_buf1['WeatherElement']['Weather'];
			
			if (strpos($str_buf1, '雨') !== false)
			{
				$str_buf0 .= '"' . '/map-data/rain.png' . '"';
			}
			else if (strpos($str_buf1, '陰') !== false)
			{
				$str_buf0 .= '"' . '/map-data/cloud.png' . '"';
			}
			else if (strpos($str_buf1, '-99') !== false)
			{
				continue;
			}
			else
			{
				$str_buf0 .= '"' . '/map-data/sun.png' . '"';
			}
			$str_buf0 .= ',';
			$str_buf0 .= '"' . $str_buf1 . '",';
			
			$str_buf0 .= '"' . $arr_buf1['StationName'] . '",';
			
			$str_buf0 .= '"' . $arr_buf1['StationId'] . '"';
			
			$str_buf0 .= ']';
			$str_buf0 .= "\n";
			
			$export_data .= $str_buf0;
		}
		
		$export_data .= ']';
		
		file_put_contents('O-A0003-001.json', $export_data);
	}
	catch (Exception $e)
	{
		
	}
	
?>
