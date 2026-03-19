<?php
	
	function get_db_last_sn()
	{
		$res = 0;
		require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
		if ($db_link == TRUE)
		{
			$db_link->query("SET NAMES \"utf8\"");
			$sql_query = "SELECT `sn` FROM `line-notify-message-list` WHERE `status`=1 ORDER BY `sn` DESC LIMIT 1;";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == TRUE)
			{
				$stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count == 1)
				{
					$stmt->bind_result($data_buf["sn"]);
					$stmt->fetch();
					$res = $data_buf["sn"];
				}
				$stmt->close();
			}
			$db_link->close();
		}
		return $res;
	}

	function get_remote_db_sn($curr_sn)
	{
		$res = '';

		// Initialize a cURL session
		$curl = curl_init();

		// Set the URL you want to send a GET request to
		$url = "http://210.71.231.250/miaoli62-line-notify/get-alarm2.php?sn=" . $curr_sn;

		// Set cURL options
		curl_setopt($curl, CURLOPT_URL, $url);    // Set the URL
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return the transfer as a string
		curl_setopt($curl, CURLOPT_HEADER, false); // Do not include the header in the output

		// Execute the cURL session
		$response = curl_exec($curl);

		// Check for errors
		if ($response === false) {
			// echo 'Curl error: ' . curl_error($curl);
		} else {
			// Print the response if no errors occurred
			// echo 'Response: ' . $response;
			$res = $response;
		}

		// Close the cURL session
		curl_close($curl);

		return $res;
	}
	
	function get_img($img_path)
	{
		$url = "http://210.71.231.250/miaoli62-line-notify/" . $img_path;
		// echo $url;
		
		// 初始化cURL
		$ch = curl_init($url);

		// 設定cURL選項
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

		// 執行cURL並獲取圖片內容
		$image = curl_exec($ch);

		// 檢查是否發生錯誤
		if(curl_errno($ch)) {
			// echo 'Error:' . curl_error($ch);
		} else {
			// 圖片儲存的路徑和文件名
			$save_to = $_SERVER['DOCUMENT_ROOT'] . '/manager-page/' . $img_path;
			
			$dir = dirname($save_to);
			if (!file_exists($dir)) {
				// 嘗試建立資料夾
				mkdir($dir, 0777, true); // 注意: 這裡的0755是資料夾權限，true表示允許建立多層資料夾
			}
			
			// 打開檔案
			$fp = fopen($save_to, 'wb');
			// 將圖片內容寫入檔案
			fwrite($fp, $image);
			// 關閉檔案
			fclose($fp);
		}

		// 關閉cURL
		curl_close($ch);
	}
	
	$curr_sn = get_db_last_sn();
	$remote_data = get_remote_db_sn($curr_sn);
	
	try
	{
		// JSON 字符串
		// $jsonString = '{"count":1,"sn":1,"time":"2023-10-30 13:25:06","id":"84a415e395ad","msg":"苗縣道126 23.1K CCTV2","path":"alarm/2023-10-30/84a415e395ad.jpg","site_id":"3-2"}';
		$jsonString = $remote_data;
		$data = json_decode($jsonString, true);
		
		if ($data['count'] > 0)
		{
			require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
			
			if ($db_link == TRUE)
			{
				$db_link->query("SET NAMES \"utf8\"");
				$sql_query = "INSERT INTO `line-notify-message-list` (`sn`, `time`, `id`, `msg`, `path`, `site_id`, `status`) VALUES (?, ?, ?, ?, ?, ?, 1);";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == TRUE)
				{
					$stmt->bind_param("isssss", $data['sn'], $data['time'], $data['id'], $data['msg'], $data['path'], $data['site_id']);
					$stmt->execute();
					$stmt->close();
				}
				$db_link->close();
				get_img($data['path']);
			}
			// echo "資料已成功寫入資料庫。";
		}
	}
	catch (PDOException $e)
	{
		// die("資料庫連接失敗: " . $e->getMessage());
	}
	
?>
