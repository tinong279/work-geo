<?php
// 初始化 cURL
$ch = curl_init();

// 設定 URL
curl_setopt($ch, CURLOPT_URL, "https://www.trimt-nsa.gov.tw/_api/zh-tw/smart-parking");curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

// 設定自訂的 HTTP Headers
$headers = [
    'Referer: https://www.trimt-nsa.gov.tw/zh-tw/lion/',
	'X-Requested-With: XMLHttpRequest'
	
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 返回傳輸結果而不是直接輸出
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 執行 cURL 請求
$response = curl_exec($ch);

// 檢查是否有錯誤
if(curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}

// 關閉 cURL
curl_close($ch);

    try
    {
        $test = json_decode($response, true);
        
        if ($test["success"] == true)
        {
            $filepath = "parking-01.json";
        	$file = fopen($filepath, "w+");
        	fputs($file, $response);
        	fclose($file);			$json_obj = json_decode($response, true);						for ($i=0; $i<count($json_obj['data']); $i++)			{				$arr_buf1 = $json_obj['data'][$i];								if ($arr_buf1['number'] == 'NJP001')				{					$id = 1;					$empty_car = $arr_buf1['empty_car'];										require("ConnMySQL.php");										if ($db_link == TRUE)					{						$db_link->query("SET NAMES \"utf8\"");											$sql_query = "UPDATE `07-parking-list` SET `empty`=?, `last-time`=CURRENT_TIMESTAMP() WHERE `id`=?;";						$stmt = $db_link->prepare($sql_query);						if ($stmt == true)						{							$stmt->bind_param("ii", $empty_car, $id);							$stmt->execute();							$stmt->close();						}						$db_link->close();					}										break;									}							}						
        }
    }
    catch (Exception $e)
    {
      // Handle the exception if the file cannot be opened
      // echo "Error: " . $e->getMessage();
    }



?>
