<?php
	
    $id = 0;
	$status = 0;
	$type = 0;
	$time = 0;
	$msg = '';
	$msg_text = '';
    //========================================
    if (isset($_POST['id']))
    {
        $id = intval($_POST['id']);
    }
    if (isset($_POST['status']))
    {
        $status = intval($_POST['status']);
    }
    if (isset($_POST['type']))
    {
        $type = intval($_POST['type']);
    }
    if (isset($_POST['time']))
    {
        $time = intval($_POST['time']);
    }
    if (isset($_POST['msg']))
    {
        $msg = $_POST['msg'];
    }
    if (isset($_POST['msg_text']))
    {
        $msg_text = $_POST['msg_text'];
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
	if ($status==2 && $type == 2)
	{
		try
		{
			$root_path = $_SERVER['DOCUMENT_ROOT'];
			$bmpFile = $msg; // BMP 圖片的路徑
			$base64String = bmpToBase64($root_path . $bmpFile);
			
			// 建立目前時間的 DateTime 物件
			$currentDateTime = new DateTime();
			$updatedDateTime1 = $currentDateTime->format('Y/m/d H:i:s');
			
			// 增加 30 分鐘
			$currentDateTime->modify('+' . $time . ' minutes');

			// 格式化為 yyyy/mm/dd hh:mm:ss
			$updatedDateTime2 = $currentDateTime->format('Y/m/d H:i:s');

			require("../ConnMySQL.php");
			
			if ($db_link == TRUE)
			{
				$db_link->query("SET NAMES \"utf8\"");
			
				$sql_query = "UPDATE `02-cms-real-time` SET `status`=?, `type`=?, `text-content-img`=?, `updated`=0, `start-time`=?, `stop-time`=?, `text-content`=? WHERE `id`=?;";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("iissssi", $status, $type, $base64String, $updatedDateTime1, $updatedDateTime2, $msg_text, $id);
					$stmt->execute();
					$stmt->close();
				}
				
				$sql_query = "INSERT IGNORE INTO `05-cms-real-time-task-list` (`id`, `text-content-img`, `start-time`, `stop-time`, `status`, `type`, `text-content`) VALUES (?, ?, ?, ?, ?, ?, ?)";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("isssiis", $id, $base64String, $updatedDateTime1, $updatedDateTime2, $status, $type, $msg_text);
					$stmt->execute();
					$stmt->close();
				}
				
				$sql_query = "UPDATE `01-cms-status` SET `status`=? WHERE `id`=?";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("ii", $status, $id);
					$stmt->execute();
					$stmt->close();
				}
				
				$db_link->close();
				
				$res = 1;
			}
		}
		catch (Exception $e)
		{
			// echo "錯誤: " . $e->getMessage();
		}
	}
	else if ($status==0 || $status==1)
	{
		try
		{
			require("../ConnMySQL.php");
			
			if ($db_link == TRUE)
			{
				$db_link->query("SET NAMES \"utf8\"");
			
				$sql_query = "UPDATE `02-cms-real-time` SET `status`=?, `type`=?, `text-content`='[]', `text-content-img`=?, `updated`=0 WHERE `id`=?;";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("iisi", $status, $type, $msg, $id);
					$stmt->execute();
					$stmt->close();
				}
				
				$sql_query = "INSERT IGNORE INTO `05-cms-real-time-task-list` (`id`, `text-content-img`, `status`, `type`, `text-content`) VALUES (?, ?, ?, ?, '[]')";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("isii", $id, $msg, $status, $type);
					$stmt->execute();
					$stmt->close();
				}
				
				$sql_query = "UPDATE `01-cms-status` SET `status`=? WHERE `id`=?";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("ii", $status, $id);
					$stmt->execute();
					$stmt->close();
				}
				
				$db_link->close();
				
				$res = 1;
			}
		}
		catch (Exception $e)
		{
			// echo "錯誤: " . $e->getMessage();
		}
	}
	
	if ($res == 1)
	{
		header("Location: edit.php?sys_msg=1&id=" . $id);
	}
	else
	{
		
	}
	
?>
