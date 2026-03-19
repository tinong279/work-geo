<?php
    
    set_time_limit(120);
    
    // require("/home/geonerv2/geonerve-iot.com/s2/variable.php");
	
	require($_SERVER['DOCUMENT_ROOT'] . '/../' . "variable.php");
	
    require("func.php");
    //==========================================================================
    function Send_Line_Notify($msg)
    {
        try
        {
			/*
			$url = "https://notify-api.line.me/api/notify";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt
			(
				$ch, CURLOPT_HTTPHEADER,
				array
				(
					'Authorization: Bearer 6w4kbZmvF33GeVYm7qT6iBCwEIFWI0j7seVZRZc5lVX'
				)
			);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("message" => $msg)));
			$output = curl_exec($ch);
			curl_close($ch);*/
        }
        catch (Exception $e)
        {
            
        }
        //========================================================================
        try
        {
			// $channelAccessToken = 'bVWt78cs6ljRcga018g3y4tV79RKZhd/tBPObPo8QM0ZFfQx+WqM0gvtPnfXBloB0beuAhdiLOHgibAgiurQ5m5GcM0DwI9TiQEkXfrFYyqHsP7I6fV0odaWYY2pjS5VVPfJ30xIAPONAFNFFlExBwdB04t89/1O/w1cDnyilFU='; // 設定你的 LINE Channel Access Token
			$channelAccessToken = 'glX3icAiaofTHlHPkACZfZvH+6ZRoFA/19Di6qOyPLANTq7JKcExgHl1PbIYrM4fWzlQKKIWE0t+Yrq9Vl2SyKzvSyl4dyTK9HUtd4iPYIlAK2GJMOkuwcqRUqXrUB9f+SpYj5NPH8gMVh4X6JNLWAdB04t89/1O/w1cDnyilFU=';
			$userId = 'C4ebde4f1ed025c5298f6f2dca4d0c00e'; // 設定接收者的 ID
			
			// 設定要發送的訊息
			$data = [
				'to' => $userId,
				'messages' => [
					[
						'type' => 'text',
						'text' => $msg
					]
				]
			];
			
			// 將陣列轉為 JSON
			$postData = json_encode($data);
			
			// 初始化 cURL
			$ch = curl_init('https://api.line.me/v2/bot/message/push');
			
			// 設定 cURL 選項
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Authorization: Bearer ' . $channelAccessToken
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, true); // 顯示詳細請求資訊
			
			// 執行 cURL 請求
			$response = curl_exec($ch);
			
			// 檢查錯誤
			if (curl_errno($ch)) {
				// echo 'cURL 錯誤: ' . curl_error($ch);
			}
			
			// 關閉 cURL
			curl_close($ch);
        }
        catch (Exception $e)
        {
            
        }
        //========================================================================
        try
        {
			$url = "https://discord.com/api/webhooks/1341340139573743646/O2srrIcXbhXgIZyWSmg10U26F1lY0LfGBQMys5UWReIoWdfSDHsJIzV9jlmhhP-ypQaF";
			
			$data = [
				"content" => $msg
			];
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$r = curl_exec($ch);
			
			curl_close($ch);
        }
        catch (Exception $e)
        {
            
        }
    }
    //==========================================================================
    $res = "\n";
    $res .= '通報時間:' . date('Y-m-d H:i:s', time()) . "\n";
    // $res .= '最新一筆資料時間:' . date('Y-m-d H:i:s', time()) . "\n";
    
    $res .= "\n";
    
    // $res .= home_get_inclinometer_all();
    $res .= home_get_raingauge_all();
    
    $res .= "\n";
    
    $res .= '【預警值】' . "\n";
    // $res .= '雙向傾斜儀 ' . $inclinometer_step1 . "度\n";
    $res .= '雨量筒 ' . $raingauge_step1 . "mm\n";
    
    $res .= '【警戒值】' . "\n";
    // $res .= '雙向傾斜儀 ' . $inclinometer_step2 . "度\n";
    $res .= '雨量筒 ' . $raingauge_step2 . "mm\n";
    
    $res .= '【行動值】' . "\n";
    // $res .= '雙向傾斜儀 ' . $inclinometer_step3 . "度\n";
    $res .= '雨量筒 ' . $raingauge_step3 . "mm\n";
    
    // echo $res;
    
    if (mb_strpos($res, "❌") != false)
    {
        $data = "1";
        file_put_contents('record.txt', $data);
    }
    else
    {
        $data = "0";
        file_put_contents('record.txt', $data);
    }
    
    Send_Line_Notify($res);
    
?>
