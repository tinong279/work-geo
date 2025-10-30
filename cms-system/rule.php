<?php
	
	//========================================
	$currentTime = time();
	
	$car_empty = 0;
	$car_r1 = 0;
	$section_data = [];
	//========================================
	function get_cms_text_info($cms_id)
	{
		global $currentTime;
		$status = 0;
		$type = 0;
		$text_content = '';
		$text_content_img = '';
		
		$dataList = [];
		
		require("ConnMySQL.php");
		
		if ($db_link == TRUE)
		{
			$db_link->query("SET NAMES \"utf8\"");
			//========================================
			$sql_query = "SELECT `status`, `type`, `text-content`, `text-content-img`, `ip`, `stop-time` FROM `02-cms-real-time` WHERE `id`=" . $cms_id . ';';
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == true)
			{
				// $stmt->bind_param("i", $id, $week); // 如果需要傳參數，取消註解並調整參數
				$stmt->execute();
				// 取代 get_result()，使用 bind_result() 獲取結果
				$stmt->store_result(); // 儲存結果集
				if ($stmt->num_rows == 1)
				{
					$stmt->bind_result($status, $type, $text_content, $text_content_img, $ip, $stop_time);
					
					while ($stmt->fetch()) {

					}
				}
				$stmt->close();
				
				if ($status == 2)
				{
					$cal_buf1 = strtotime($stop_time);
					if ($cal_buf1 < $currentTime)
					{
						$status = 1;
						
						$sql_query = "UPDATE `01-cms-status` SET `status`=1 WHERE `id`=$cms_id;";
						$db_link->query($sql_query);
						
						$sql_query = "UPDATE `02-cms-real-time` SET `status`=1,`type`=0,`text-content`='[]',`text-content-img`='',`start-time`=CURRENT_TIMESTAMP(),`stop-time`=CURRENT_TIMESTAMP(),`updated`=0 WHERE `id`=$cms_id;";
						$db_link->query($sql_query);
					}
				}
				
				if ($status == 0)
				{
					$dataList[] = [
						"id" => $cms_id,
						"status" => $status,
						"type" => $type,
						"text_content" => [],
						"text_content_img" => '',
						"ip" => $ip
					];
				}
				else if ($status == 1)
				{
					$now_hour = intval(date('H', $currentTime));
					// echo $now_hour;
					
					$now_week = intval(date('w', $currentTime));
					// echo $now_week;
					
					$sql_query = "SELECT `text-content`, `text-content-img` FROM `03-cms-schedule` WHERE `status`=1 AND `id`=$cms_id AND `hour_start`<=$now_hour AND $now_hour<`hour_stop`;";
					$stmt = $db_link->prepare($sql_query);
					if ($stmt == true)
					{
						// $stmt->bind_param("i", $id, $week); // 如果需要傳參數，取消註解並調整參數
						$stmt->execute();
						// 取代 get_result()，使用 bind_result() 獲取結果
						$stmt->store_result(); // 儲存結果集
						if ($stmt->num_rows == 1)
						{
							$stmt->bind_result($text_content, $text_content_img);
							
							while ($stmt->fetch()) {

							}
						}
						$stmt->close();
					}
					
					$arr_buf1 = json_decode($text_content);
					
					$dataList[] = [
						"id" => $cms_id,
						"status" => $status,
						"type" => $type,
						"text_content" => $arr_buf1,
						"text_content_img" => $text_content_img,
						"ip" => $ip
					];
				}
				else if ($status == 2)
				{
					$arr_buf1 = json_decode($text_content);
					
					$dataList[] = [
						"id" => $cms_id,
						"status" => $status,
						"type" => $type,
						"text_content" => $arr_buf1,
						"text_content_img" => $text_content_img,
						"ip" => $ip
					];
				}
				
				// print_r($dataList);
			}
			//========================================
			$db_link->close();
		}
		return $dataList;
	}
	//========================================
	require("ConnMySQL.php");
	
	if ($db_link == TRUE)
	{
		$db_link->query("SET NAMES \"utf8\"");
		//========================================
		$sql_query = "SELECT `empty`, `r1` FROM `07-parking-list` WHERE `id`=1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == true)
		{
			// $stmt->bind_param("i", $id, $week); // 如果需要傳參數，取消註解並調整參數
			$stmt->execute();
			// 取代 get_result()，使用 bind_result() 獲取結果
			$stmt->store_result(); // 儲存結果集
			if ($stmt->num_rows == 1)
			{
				$stmt->bind_result($empty, $r1);
				$stmt->fetch();
				$car_empty = $empty;
				// echo $car_empty;
				$car_r1 = $r1;
				
				/*
				$stmt->bind_result($name_buf, $hour_start_buf, $hour_stop_buf);
				$schedule_dataList = [];
				while ($stmt->fetch()) {
					$schedule_dataList[] = [
						"name" => $name_buf,
						"start" => $hour_start_buf,
						"stop" => $hour_stop_buf
					];
				}
				*/
				
			}
			$stmt->close();
		}
		//========================================
		$sql_query = "SELECT `id`, `last-time`, `speed` FROM `06-section-list` ORDER BY `id` ASC;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == true)
		{
			// $stmt->bind_param("i", $id, $week); // 如果需要傳參數，取消註解並調整參數
			$stmt->execute();
			// 取代 get_result()，使用 bind_result() 獲取結果
			$stmt->store_result(); // 儲存結果集
			if ($stmt->num_rows > 0)
			{
				$stmt->bind_result($id, $time, $speed);
				$section_data = [];
				while ($stmt->fetch()) {
					$section_data[] = [
						"id" => $id,
						"time" => $time,
						"speed" => $speed
					];
				}
				// print_r($section_data);
			}
			$stmt->close();
		}
		//========================================
		$cms_data = [];
		$cms_data[] = get_cms_text_info(1)[0];
		$cms_data[] = get_cms_text_info(2)[0];
		$cms_data[] = get_cms_text_info(3)[0];
		$cms_data[] = get_cms_text_info(4)[0];
		$cms_data[] = get_cms_text_info(5)[0];
		$cms_data[] = get_cms_text_info(6)[0];
		$cms_data[] = get_cms_text_info(7)[0];
		$cms_data[] = get_cms_text_info(8)[0];
		$cms_data[] = get_cms_text_info(9)[0];
		$cms_data[] = get_cms_text_info(10)[0];
		
		$final_res = [
			"car_empty" => $car_empty,
			"car_r1" => $car_r1,
			"section_data" => $section_data,
			"cms_data" => $cms_data
		];
		
		header('Content-Type: application/json');
		echo json_encode($final_res);

		$db_link->close();
	}
	
?>
