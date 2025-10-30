<?php
	
	set_time_limit(30);
	//-------------------------------------------------------
	$url = "https://opendata.cwa.gov.tw/fileapi/v1/opendataapi/O-A0058-006?Authorization=CWA-827EFF14-6CBF-4820-9112-149A6B257B4E&downloadType=WEB&format=JSON";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	
	$header[] = '';
	$header[] = "Connection: keep-alive";
	$header[] = "Pragma: no-cache";
	$header[] = "Cache-Control: no-cache";
	$header[] = "Upgrade-Insecure-Requests: 1";
	$header[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36";
	$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
	$header[] = "Accept-Encoding: gzip, deflate";
	$header[] = "Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	$res = curl_exec($ch);
	
	if ($res === false)
	{
		//echo curl_error($ch);
		exit();
	}
	curl_close($ch);
	
	if (strlen($res) <= 0)
	{
		exit();
	}
	//-------------------------------------------------------
	$obj_buf = json_decode($res, true);
	$url = $obj_buf['cwaopendata']['dataset']['resource']['ProductURL'];
	//-------------------------------------------------------
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	
	$header[] = '';
	$header[] = "Connection: keep-alive";
	$header[] = "Pragma: no-cache";
	$header[] = "Cache-Control: no-cache";
	$header[] = "Upgrade-Insecure-Requests: 1";
	$header[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36";
	$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
	$header[] = "Accept-Encoding: gzip, deflate";
	$header[] = "Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	$res = curl_exec($ch);
	
	if ($res === false)
	{
		//echo curl_error($ch);
		exit();
	}
	curl_close($ch);
	
	if (strlen($res) <= 0)
	{
		exit();
	}
	
    $filepath = "O-A0058-003.png";
    $file = fopen($filepath, "w+");
    fputs($file, $res);
    fclose($file);
	//-------------------------------------------------------
	
?>
