<?php
	
	//========================================
    $id = 0;
	$name = '';
	$week = 0;
	$start = 0;
	$stop = 0;
	
	$img = '';
	$text = '';
	//========================================
	$schedule_dataList = [];
	$sys_flag = 0;
	$sys_msg = '';
    //========================================
    if (isset($_POST['id']))
    {
        $id = intval($_POST['id']);
    }
    if (isset($_POST['name']))
    {
        $name = $_POST['name'];
    }
	if (isset($_POST['week']))
    {
        $week = intval($_POST['week']);
    }
	if (isset($_POST['start']))
    {
        $start = intval($_POST['start']);
    }
	if (isset($_POST['stop']))
    {
        $stop = intval($_POST['stop']);
    }
	if (isset($_POST['img']))
    {
        $img = $_POST['img'];
    }
	if (isset($_POST['text']))
    {
        $text = $_POST['text'];
    }
    //========================================
	function bmpToBase64($filePath)
	{
		// 檢查檔案是否存在
		if (!file_exists($filePath)) {
			throw new Exception("檔案不存在: $filePath");
		}

		// 嘗試讀取 BMP 圖片
		$image = imagecreatefrombmp($filePath);
		if (!$image) {
			throw new Exception("無法讀取 BMP 圖片: $filePath");
		}

		// 將圖片轉換成 PNG 並存入記憶體緩衝
		ob_start(); // 開啟輸出緩衝
		imagepng($image); // 將圖片轉換為 PNG 格式
		$imageData = ob_get_clean(); // 取得緩衝內容並清空緩衝

		// 清理圖片資源
		imagedestroy($image);

		// 將 PNG 資料轉換成 Base64 字串
		return base64_encode($imageData);
	}
	function base64ToImageFile($base64String, $outputFilePath) {
		// 移除 Base64 頭部資訊（如果有）
		if (strpos($base64String, 'base64,') !== false) {
			$base64String = explode('base64,', $base64String)[1];
		}

		// 解碼 Base64 字串為二進位資料
		$imageData = base64_decode($base64String);

		if ($imageData === false) {
			throw new Exception("Base64 解碼失敗");
		}

		// 將二進位資料寫入檔案
		$result = file_put_contents($outputFilePath, $imageData);

		if ($result === false) {
			throw new Exception("無法將圖片寫入檔案: $outputFilePath");
		}

		return $outputFilePath;
	}
	//========================================
	$res = 0;
	//========================================
	if ($start == $stop)
	{
		$sys_flag = 2;
		$sys_msg = '開始時間 不能等於 結束時間';
	}
	else if ($start > $stop)
	{
		$sys_flag = 2;
		$sys_msg = '開始時間 不能大於 結束時間';
	}
	else
	{
		require("../ConnMySQL.php");
		
		if ($db_link == TRUE)
		{
			$db_link->query("SET NAMES \"utf8\"");
			
			$sql_query = "SELECT `name`, `hour_start`, `hour_stop` FROM `03-cms-schedule` WHERE `status`=1 AND `id`=? AND `week`=? ORDER BY `sn` ASC;";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == true)
			{
				$stmt->bind_param("ii", $id, $week); // 如果需要傳參數，取消註解並調整參數
				$stmt->execute();
				// 取代 get_result()，使用 bind_result() 獲取結果
				$stmt->store_result(); // 儲存結果集
				if ($stmt->num_rows == 0)
				{
					// exit();
				}
				else
				{
					$stmt->bind_result($name_buf, $hour_start_buf, $hour_stop_buf);
					$schedule_dataList = [];
					while ($stmt->fetch()) {
						$schedule_dataList[] = [
							"name" => $name_buf,
							"start" => $hour_start_buf,
							"stop" => $hour_stop_buf
						];
					}
					
					// print_r($schedule_dataList);
				}
				$stmt->close();
			}
			
			$clash_name = '';
			
			$check_buf1 = False;
			for ($i=0; $i<count($schedule_dataList); $i++)
			{
				$arr_buf1 = $schedule_dataList[$i];
				
				if ($arr_buf1['start']==$start || $arr_buf1['stop']==$stop)
				{
						$check_buf1 = True;
						$clash_name = $arr_buf1['name'];
						break;
				}
				
				for ($j=$start; $j<=$stop; $j++)
				{
					if ($arr_buf1['start'] < $j && $j < $arr_buf1['stop'])
					{
						$check_buf1 = True;
						$clash_name = $arr_buf1['name'];
						break;
					}
					/*
					else
					{
						if ($arr_buf1['start'] < $stop && $stop <= $arr_buf1['stop'])
						{
							$check_buf1 = True;
							$clash_name = $arr_buf1['name'];
							break;
						}
						else
						{
							
						}
					}
					*/
				}
				
				if ($check_buf1 == True)
				{
					break;
				}
			}
			
			if ($check_buf1 == False)
			{
				$sys_flag = 1;
				$sys_msg = '新增排程成功';
				
				$root_path = $_SERVER['DOCUMENT_ROOT'];
				$bmpFile = $img; // BMP 圖片的路徑
				$base64String = bmpToBase64($root_path . $bmpFile);
				
				$sql_query = "INSERT IGNORE INTO `03-cms-schedule`(`id`, `name`, `week`, `hour_start`, `hour_stop`, `text-content`, `text-content-img`) VALUES (?, ?, ?, ?, ?, ?, ?);";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("isiiiss", $id, $name, $week, $start, $stop, $text, $base64String);
					$stmt->execute();
					$stmt->close();
				}
			}
			else
			{
				$sys_flag = 2;
				$sys_msg = '設定時間和「' . $clash_name . '」有重疊';
			}
			
			$db_link->close();
		}
	}
	//========================================
	$fin_res = '';
	$fin_res .= '{';
	$fin_res .= '"sys_flag":' . '' . $sys_flag . '' . ',';
	$fin_res .= '"sys_msg":' . '"' . $sys_msg . '"' . '';
	$fin_res .= '}';
	
	// 設定內容類型為 JSON
	header('Content-Type: application/json');

	// 將陣列轉換為 JSON 並輸出
	echo $fin_res;
	
?>
