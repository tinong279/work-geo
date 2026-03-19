<?php
    
    $sensor_timeout_value = 1800;
    
    function home_get_inclinometer_all()
    {
        $res = '【傾斜儀】' . "\n";
		
        // require("/home/geonerv2/geonerve-iot.com/s2/lora-offset.php");
		
    	require($_SERVER['DOCUMENT_ROOT'] . '/../' . "lora-offset.php");
		
		$res .= home_get_inclinometer_with_offset("2.1K邊坡 傾斜儀(左)", 1, $id_1_ad1_offset, $id_1_ad2_offset);
		$res .= home_get_inclinometer_with_offset("2.1K邊坡 傾斜儀(右)", 2, $id_2_ad1_offset, $id_2_ad2_offset);
		
		$res .= home_get_inclinometer_with_offset("6.2K邊坡 傾斜儀(左)", 11, $id_11_ad1_offset, $id_11_ad2_offset);
		$res .= home_get_inclinometer_with_offset("6.2K邊坡 傾斜儀(右)", 12, $id_12_ad1_offset, $id_12_ad2_offset);
		
		$res .= home_get_inclinometer_with_offset("23.5K邊坡 傾斜儀(左)", 21, $id_21_ad1_offset, $id_21_ad2_offset);
		$res .= home_get_inclinometer_with_offset("23.5K邊坡 傾斜儀(右)", 22, $id_22_ad1_offset, $id_22_ad2_offset);
		
		$res .= home_get_inclinometer_with_offset("28.6K邊坡 傾斜儀(左)", 31, $id_31_ad1_offset, $id_31_ad2_offset);
		$res .= home_get_inclinometer_with_offset("28.6K邊坡 傾斜儀(右)", 32, $id_32_ad1_offset, $id_32_ad2_offset);
		
    	return $res;
    }
    function home_get_raingauge_all()
    {
        $res = '【雨量】' . "\n";
        
		// require("/home/geonerv2/geonerve-iot.com/s2/lora-offset.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "lora-offset.php");
		
    	$res .= home_get_raingauge_with_offset("2.1K邊坡 雨量筒", 9, $id_9_count_offset);
		$res .= home_get_raingauge_with_offset("6.2K邊坡 雨量筒", 19, $id_19_count_offset);
		$res .= home_get_raingauge_with_offset("23.5K邊坡 雨量筒", 29, $id_29_count_offset);
		$res .= home_get_raingauge_with_offset("28.6K邊坡 雨量筒", 39, $id_39_count_offset);
		
    	return $res;
    }
    function home_get_inclinometer_with_offset($loc_name, $id_number, $ad1_offset, $ad2_offset)
    {
        $output = '';
        
        // require("/home/geonerv2/geonerve-iot.com/s2/ConnMySQL.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "ConnMySQL.php");
		
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

    $output .= get_status_circle_inclinometer($data_buf["_ad1"], strtotime($data_buf["time"]));
    $output .= $loc_name . ' X軸';
    $output .= ' ' . get_status_text_inclinometer($data_buf["_ad1"], strtotime($data_buf["time"]));
    // $output .= ', ' . sprintf("%01.2f", $data_buf["_ad1"]) . ', ' . $data_buf["time"] . '' . "\n";
	$output .= ', ' . $data_buf["time"] . '' . "\n";
    
    $output .= get_status_circle_inclinometer($data_buf["_ad2"], strtotime($data_buf["time"]));
    $output .= $loc_name . ' Y軸';
    $output .= ' ' . get_status_text_inclinometer($data_buf["_ad2"], strtotime($data_buf["time"]));
    // $output .= ', ' . sprintf("%01.2f", $data_buf["_ad2"]) . ', ' . $data_buf["time"] . '' . "\n";
	$output .= ', ' . $data_buf["time"] . '' . "\n";
    			}
    			$stmt->close();
            }
            $db_link->close();
    	}
    	return $output;
    }
    
    function home_get_raingauge_with_offset($loc_name, $id_number, $count_offset)
    {
        $output = '';
        
        // require("/home/geonerv2/geonerve-iot.com/s2/ConnMySQL.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "ConnMySQL.php");
		
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

    
    $output .= get_status_circle_raingauge2($id_number*100+3, strtotime($data_buf["time"]));
    $output .= $loc_name;
    $output .= ' ' . get_status_text_raingauge2($id_number*100+3, strtotime($data_buf["time"]));
    $output .= ', ' . $data_buf["time"] . "\n";
    			}
    			$stmt->close();
            }
            $db_link->close();
    	}
    	return $output;
    }
    //==========================================================================
    function get_status_circle_inclinometer($val, $time)
    {
        global $sensor_timeout_value;
        
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = abs($val);
		
        // require("/home/geonerv2/geonerve-iot.com/s2/variable.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "variable.php");
		
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_timeout_value)
        {
            $str_buf = '❌';
        }
        else
        {
            if ($val < $inclinometer_step1)
            {
                $str_buf = '✅';
            }
            else if ($inclinometer_step1 <= $val && $val < $inclinometer_step2)
            {
                $str_buf = '⚠️';
            }
            else if ($inclinometer_step2 <= $val && $val < $inclinometer_step3)
            {
                $str_buf = '⚠️';
            }
            else if ($inclinometer_step3 <= $val)
            {
                $str_buf = '⚠️';
            }
            else
            {
                $str_buf = '✅';
            }
        }
        return $str_buf;
    }
    //==========================================================================
    function get_status_text_inclinometer($val, $time)
    {
        global $sensor_timeout_value;
        
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = abs($val);
        
		// require("/home/geonerv2/geonerve-iot.com/s2/variable.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "variable.php");
		
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_timeout_value)
        {
            $str_buf = '斷線';
        }
        else
        {
            if ($val < $inclinometer_step1)
            {
                $str_buf = '安全';
            }
            else if ($inclinometer_step1 <= $val && $val < $inclinometer_step2)
            {
                $str_buf = '預警';
            }
            else if ($inclinometer_step2 <= $val && $val < $inclinometer_step3)
            {
                $str_buf = '警戒';
            }
            else if ($inclinometer_step3 <= $val)
            {
                $str_buf = '行動';
            }
            else
            {
                $str_buf = '安全';
            }
        }
        return $str_buf;
    }
    //==========================================================================
    function get_status_circle_raingauge2($chn_id, $time)
    {
        global $sensor_timeout_value;
        
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = 0;
        
        $time_buf=new DateTime();
        $time_buf->modify('-60 minutes');
        $time_buf = $time_buf->format('Y-m-d H:i:s');
        
        // require("/home/geonerv2/geonerve-iot.com/s2/ConnMySQL.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "ConnMySQL.php");
		
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            $sql_query = "SELECT SUM(val) FROM chndata WHERE chn_id=" . $chn_id . " AND time>='" . $time_buf . "' ORDER BY sn DESC;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count == 1)
				{
    				$stmt->bind_result($data_buf["total_val"]);
				    $stmt->fetch();
				    $val = $data_buf["total_val"];
				}
                $stmt->close();
            }
            $db_link->close();
    	}
    	
        // require("/home/geonerv2/geonerve-iot.com/s2/variable.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "variable.php");
		
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_timeout_value)
        {
            
            $str_buf = '❌';
        }
        else
        {
            if ($val < $raingauge_step1)
            {
                $str_buf = '✅';
            }
            else if ($raingauge_step1 <= $val && $val < $raingauge_step2)
            {
                $str_buf = '⚠️';
            }
            else if ($raingauge_step2 <= $val && $val < $raingauge_step3)
            {
                $str_buf = '⚠️';
            }
            else if ($raingauge_step3 <= $val)
            {
                $str_buf = '⚠️';
            }
            else
            {
                $str_buf = '✅';
            }
        }
        return $str_buf;
    }
    //==========================================================================
    function get_status_text_raingauge2($chn_id, $time)
    {
        global $sensor_timeout_value;
        
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = 0;
        
        $time_buf=new DateTime();
        $time_buf->modify('-60 minutes');
        $time_buf = $time_buf->format('Y-m-d H:i:s');
        
        // require("/home/geonerv2/geonerve-iot.com/s2/ConnMySQL.php");
		
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "ConnMySQL.php");
		
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            $sql_query = "SELECT SUM(val) FROM chndata WHERE chn_id=" . $chn_id . " AND time>='" . $time_buf . "' ORDER BY sn DESC;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count == 1)
				{
    				$stmt->bind_result($data_buf["total_val"]);
				    $stmt->fetch();
				    $val = $data_buf["total_val"];
				}
                $stmt->close();
            }
            $db_link->close();
    	}
    	
        // require("/home/geonerv2/geonerve-iot.com/s2/variable.php");
        
		require($_SERVER['DOCUMENT_ROOT'] . '/../' . "variable.php");
		
		$str_buf = '';
        
        if ($cal_buf1 >= $sensor_timeout_value)
        {
            
            $str_buf = '斷線';
        }
        else
        {
            if ($val < $raingauge_step1)
            {
                $str_buf = '安全';
            }
            else if ($raingauge_step1 <= $val && $val < $raingauge_step2)
            {
                $str_buf = '預警';
            }
            else if ($raingauge_step2 <= $val && $val < $raingauge_step3)
            {
                $str_buf = '警戒';
            }
            else if ($raingauge_step3 <= $val)
            {
                $str_buf = '行動';
            }
            else
            {
                $str_buf = '安全';
            }
        }
        return $str_buf;
    }
    //==========================================================================
    
?>