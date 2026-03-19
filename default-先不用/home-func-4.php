<?php
    
    require("C:\\xampp\\htdocs\\default\\function.php");
    
    function home_get_inclinometer_all()
    {
        require("C:\\xampp\\htdocs\\default\\lora-offset.php");
		
		home_get_inclinometer_with_offset_31("28.6K 傾斜儀(左)");
		home_get_inclinometer_with_offset_32("28.6K 傾斜儀(右)");
		
    	// home_get_inclinometer_with_offset("28.6K 傾斜儀(左)", 31, $id_31_ad1_offset, $id_31_ad2_offset);
    	// home_get_inclinometer_with_offset("28.6K 傾斜儀(右)", 32, $id_32_ad1_offset, $id_32_ad2_offset);
    }
    function home_get_raingauge_all()
    {
        require("C:\\xampp\\htdocs\\default\\lora-offset.php");
    	home_get_raingauge_with_offset("28.6K 雨量筒", 39, $id_39_count_offset);
    }
	
    function home_get_inclinometer_with_offset_31($loc_name)
    {
		require("C:\\xampp\\htdocs\\default\\variable.php");
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            
			$time = '';
			$ad1 = 0;
			$ad2 = 0;
			$battery = 0;
			
            $sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=3101 ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				$stmt->bind_result($data_buf2["time"], $data_buf2["val"]);
					$stmt->fetch();
					$time = $data_buf2["time"];
					$ad1 = $data_buf2["val"];
    			}
    			$stmt->close();
            }
			
            $sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=3102 ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				$stmt->bind_result($data_buf2["time"], $data_buf2["val"]);
					$stmt->fetch();
					// $time = $data_buf2["time"];
					$ad2 = $data_buf2["val"];
    			}
    			$stmt->close();
            }
			
            $sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=3105 ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				$stmt->bind_result($data_buf2["time"], $data_buf2["val"]);
					$stmt->fetch();
					// $time = $data_buf2["time"];
					$battery = $data_buf2["val"];
    			}
    			$stmt->close();
            }
			
            $db_link->close();
			
			$output = '';
			$output .= '<tr>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' X軸' . '</td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($ad1, strtotime($time)) . '</span></td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $ad1) . '</td>' . "\n";
			$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $battery) . '</td>' . "\n";
			$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . $time . '</td>' . "\n";
			$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . '10分鐘一筆資料' . '</td>' . "\n";
			$output .= '</tr>' . "\n";
			$output .= '<tr>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' Y軸' . '</td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($ad2, strtotime($time)) . '</span></td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $ad2) . '</td>' . "\n";
			$output .= '</tr>' . "\n";
			echo $output;
    	}
    }
    function home_get_inclinometer_with_offset_32($loc_name)
    {
		require("C:\\xampp\\htdocs\\default\\variable.php");
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            
			$time = '';
			$ad1 = 0;
			$ad2 = 0;
			$battery = 0;
			
            $sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=3201 ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				$stmt->bind_result($data_buf2["time"], $data_buf2["val"]);
					$stmt->fetch();
					$time = $data_buf2["time"];
					$ad1 = $data_buf2["val"];
    			}
    			$stmt->close();
            }
			
            $sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=3202 ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				$stmt->bind_result($data_buf2["time"], $data_buf2["val"]);
					$stmt->fetch();
					// $time = $data_buf2["time"];
					$ad2 = $data_buf2["val"];
    			}
    			$stmt->close();
            }
			
            $sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=3205 ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				$stmt->bind_result($data_buf2["time"], $data_buf2["val"]);
					$stmt->fetch();
					// $time = $data_buf2["time"];
					$battery = $data_buf2["val"];
    			}
    			$stmt->close();
            }
			
            $db_link->close();
			
			$output = '';
			$output .= '<tr>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' X軸' . '</td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($ad1, strtotime($time)) . '</span></td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $ad1) . '</td>' . "\n";
			$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $battery) . '</td>' . "\n";
			$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . $time . '</td>' . "\n";
			$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . '10分鐘一筆資料' . '</td>' . "\n";
			$output .= '</tr>' . "\n";
			$output .= '<tr>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' Y軸' . '</td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($ad2, strtotime($time)) . '</span></td>' . "\n";
			$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $ad2) . '</td>' . "\n";
			$output .= '</tr>' . "\n";
			echo $output;
    	}
    }
	
    function home_get_inclinometer_with_offset($loc_name, $id_number, $ad1_offset, $ad2_offset)
    {
		require("C:\\xampp\\htdocs\\default\\variable.php");
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            
            $sql_query = "SELECT time, _id, _ad1, _ad2, _count, _battery, _rssi, _snr FROM `rawdata` WHERE _id=? ORDER BY sn DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->bind_param("i", $id_number);
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				
    				$stmt->bind_result(
    				    $data_buf["time"],
    				    $data_buf["_id"],
    				    $data_buf["_ad1"],
    				    $data_buf["_ad2"],
    				    $data_buf["_count"],
    				    $data_buf["_battery"],
    				    $data_buf["_rssi"],
    				    $data_buf["_snr"]
    				    );
    				while ($stmt->fetch())
    				{
    				    
    				}
    				$data_buf["_ad1"] /= 0.28;
    				$data_buf["_ad1"] += $ad1_offset;
    				$data_buf["_ad2"] /= 0.28;
    				$data_buf["_ad2"] += $ad2_offset;

					if ($data_buf["_ad1"] > $inclinometer_max)
					{
						$data_buf["_ad1"] = $inclinometer_max;
					}
					else if ($data_buf["_ad1"] < (0-$inclinometer_max))
					{
						$data_buf["_ad1"] = (0-$inclinometer_max);
					}
					
					if ($data_buf["_ad2"] > $inclinometer_max)
					{
						$data_buf["_ad2"] = $inclinometer_max;
					}
					else if ($data_buf["_ad2"] < (0-$inclinometer_max))
					{
						$data_buf["_ad2"] = (0-$inclinometer_max);
					}

    $output = '';
    $output .= '<tr>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' X軸' . '</td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($data_buf["_ad1"], strtotime($data_buf["time"])) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $data_buf["_ad1"]) . '</td>' . "\n";
    $output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $data_buf["_battery"]) . '</td>' . "\n";
    $output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . $data_buf["time"] . '</td>' . "\n";
    $output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . '10分鐘一筆資料' . '</td>' . "\n";
    $output .= '</tr>' . "\n";
    $output .= '<tr>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' Y軸' . '</td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($data_buf["_ad2"], strtotime($data_buf["time"])) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $data_buf["_ad2"]) . '</td>' . "\n";
    $output .= '</tr>' . "\n";
    echo $output;

    			}
    			$stmt->close();
            }
            $db_link->close();
    	}
    }
    
    function home_get_raingauge_with_offset($loc_name, $id_number, $count_offset)
    {
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            
            $sql_query = "SELECT time, _id, _ad1, _ad2, _count, _battery, _rssi, _snr FROM `rawdata` WHERE _id=? ORDER BY sn DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->bind_param("i", $id_number);
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				
    				$stmt->bind_result(
    				    $data_buf["time"],
    				    $data_buf["_id"],
    				    $data_buf["_ad1"],
    				    $data_buf["_ad2"],
    				    $data_buf["_count"],
    				    $data_buf["_battery"],
    				    $data_buf["_rssi"],
    				    $data_buf["_snr"]
    				    );
    				while ($stmt->fetch())
    				{
    				    
    				}
    				$data_buf["_count"] *= 0.5;
    				$data_buf["_count"] += $count_offset;

    $output = '';
    $output .= '<tr>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . '' . '</td>' . "\n";
    //$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_raingauge($data_buf["_count"]) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_raingauge2(3903, strtotime($data_buf["time"])) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $data_buf["_count"]) . '</td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $data_buf["_battery"]) . '</td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["time"] . '</td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . '10分鐘一筆資料' . '</td>' . "\n";
    $output .= '</tr>' . "\n";
    echo $output;

    			}
    			$stmt->close();
            }
            $db_link->close();
    	}
    }
	
    function home_get_line_power($id_number)
    {
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            
            $sql_query = "SELECT `time`, `_data` FROM `rawdata` WHERE `_id`=? ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->bind_param("i", $id_number);
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				
    				$stmt->bind_result(
    				    $data_buf["time"],
    				    $data_buf["_data"]);
						
    				while ($stmt->fetch())
    				{
    				    
    				}

    $output = '';
    $output .= '<tr>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">市電監測</td>' . "\n";
    //$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_raingauge($data_buf["_count"]) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_linepower(strval($data_buf["_data"]), strtotime($data_buf["time"])) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["time"] . '</td>' . "\n";
    $output .= '</tr>' . "\n";
    echo $output;

    			}
    			$stmt->close();
            }
            $db_link->close();
    	}
    }
	
    function home_get_battery_voltage($id_number)
    {
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            
            $sql_query = "SELECT `time`, `_data` FROM `rawdata` WHERE _id=? ORDER BY `sn` DESC LIMIT 1;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->bind_param("i", $id_number);
                $stmt->execute();
    			$stmt->store_result();
    			$data_count = $stmt->num_rows;
    			if ($data_count == 1)
    			{
    				
    				$stmt->bind_result(
    				    $data_buf["time"],
    				    $data_buf["value"]);
						
    				while ($stmt->fetch())
    				{
    				    
    				}
					
					$cal_buf1 = floatval($data_buf["value"]);
					$cal_buf1 /= 65535;
					$cal_buf1 *= 20;

    $output = '';
    $output .= '<tr>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">電池電壓</td>' . "\n";
    //$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_raingauge($data_buf["_count"]) . '</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_batteryvoltage($cal_buf1, strtotime($data_buf["time"])) . ' ' . number_format($cal_buf1, 2) . ' V</span></td>' . "\n";
    $output .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["time"] . '</td>' . "\n";
    $output .= '</tr>' . "\n";
    echo $output;

    			}
    			$stmt->close();
            }
            $db_link->close();
    	}
    }
    
?>