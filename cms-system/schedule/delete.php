<?php
	
	//========================================
	$sn = 0;
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
    if (isset($_POST['sn']))
    {
        $sn = intval($_POST['sn']);
    }
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
	require("../ConnMySQL.php");
	
	if ($db_link == TRUE)
	{
		$db_link->query("SET NAMES \"utf8\"");
		
        $sql_query = "SELECT `sn` FROM `03-cms-schedule` WHERE `sn`=? AND `status`=1;";
        $stmt = $db_link->prepare($sql_query);
        if ($stmt == true)
		{
            $stmt->bind_param("i", $sn); // 如果需要傳參數，取消註解並調整參數
            $stmt->execute();
            // 取代 get_result()，使用 bind_result() 獲取結果
            $stmt->store_result(); // 儲存結果集
            if ($stmt->num_rows == 1)
            {
				$stmt->close();
				
				$sql_query = "UPDATE `03-cms-schedule` SET `status`=0 WHERE `sn`=?";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == true)
				{
					$stmt->bind_param("i", $sn);
					$stmt->execute();
					$stmt->close();
					
					$sys_flag = 1;
					$sys_msg = '刪除成功';
				}
            }
			else
			{
				$sys_flag = 0;
				$sys_msg = '異常';
				$stmt->close();
			}
        }
		
		$db_link->close();
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
