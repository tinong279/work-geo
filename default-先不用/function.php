<?php
    
    //----------------------------------------------------------------------
    function hex2dec_2bytes_ones_complement($source)
    {
        $cal_buf = hexdec($source);
        if ($cal_buf >= 32768)
        {
            $cal_buf -= 65536;
        }
        $cal_buf /= 32767;
        return $cal_buf;
    }
    //----------------------------------------------------------------------
    function get_status_circle_inclinometer($val, $time)
    {
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = abs($val);
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_lost_time_limit)
        {
            $str_buf = $black_circle;
        }
        else
        {
            if ($val < $inclinometer_step1)
            {
                $str_buf = $green_circle;
            }
            else if ($inclinometer_step1 <= $val && $val < $inclinometer_step2)
            {
                $str_buf = $yellow_circle;
            }
            else if ($inclinometer_step2 <= $val && $val < $inclinometer_step3)
            {
                $str_buf = $orange_circle;
            }
            else if ($inclinometer_step3 <= $val)
            {
                $str_buf = $red_circle;
            }
            else
            {
                $str_buf = $green_circle;
            }
        }
        return $str_buf;
    }
    function get_status_code_inclinometer($val)
    {
        $val = abs($val);
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $cal_buf = 0;
        if ($val < $inclinometer_step1)
        {
            $cal_buf = 1;
        }
        else if ($inclinometer_step1 <= $val && $val < $inclinometer_step2)
        {
            $cal_buf = 2;
        }
        else if ($inclinometer_step2 <= $val && $val < $inclinometer_step3)
        {
            $cal_buf = 3;
        }
        else if ($inclinometer_step3 <= $val)
        {
            $cal_buf = 4;
        }
        else
        {
            $cal_buf = 0;
        }
        return $cal_buf;
    }
    //----------------------------------------------------------------------
    function get_status_circle_raingauge($val, $time)
    {
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_lost_time_limit)
        {
            $str_buf = $black_circle;
        }
        else
        {
            if ($val < $raingauge_step1)
            {
                $str_buf = $green_circle;
            }
            else if ($raingauge_step1 <= $val && $val < $raingauge_step2)
            {
                $str_buf = $yellow_circle;
            }
            else if ($raingauge_step2 <= $val && $val < $raingauge_step3)
            {
                $str_buf = $orange_circle;
            }
            else if ($raingauge_step3 <= $val)
            {
                $str_buf = $red_circle;
            }
            else
            {
                $str_buf = $green_circle;
            }
        }
        return $str_buf;
    }
    function get_status_code_raingauge($val)
    {
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $cal_buf = 0;
        if ($val < $raingauge_step1)
        {
            $cal_buf = 1;
        }
        else if ($raingauge_step1 <= $val && $val < $raingauge_step2)
        {
            $cal_buf = 2;
        }
        else if ($raingauge_step2 <= $val && $val < $raingauge_step3)
        {
            $cal_buf = 3;
        }
        else if ($raingauge_step3 <= $val)
        {
            $cal_buf = 4;
        }
        else
        {
            $cal_buf = 0;
        }
        return $cal_buf;
    }
    //----------------------------------------------------------------------
    function get_status_circle_raingauge2($chn_id, $time)
    {
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = 0;
        
        $time_buf=new DateTime();
        $time_buf->modify('-60 minutes');
        $time_buf = $time_buf->format('Y-m-d H:i:s');
        
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
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
    	
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_lost_time_limit)
        {
            
            $str_buf = $black_circle;
        }
        else
        {
            if ($val < $raingauge_step1)
            {
                $str_buf = $green_circle;
            }
            else if ($raingauge_step1 <= $val && $val < $raingauge_step2)
            {
                $str_buf = $yellow_circle;
            }
            else if ($raingauge_step2 <= $val && $val < $raingauge_step3)
            {
                $str_buf = $orange_circle;
            }
            else if ($raingauge_step3 <= $val)
            {
                $str_buf = $red_circle;
            }
            else
            {
                $str_buf = $green_circle;
            }
        }
        return $str_buf;
    }
    function get_status_code_raingauge2($val, $chn_id)
    {
        $time_buf=new DateTime();
        $time_buf->modify('-60 minutes');
        $time_buf = $time_buf->format('Y-m-d H:i:s');
        
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
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
				    $val += $data_buf["total_val"];
				}
                $stmt->close();
            }
            $db_link->close();
    	}
        
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $cal_buf = 0;
        if ($val < $raingauge_step1)
        {
            $cal_buf = 1;
        }
        else if ($raingauge_step1 <= $val && $val < $raingauge_step2)
        {
            $cal_buf = 2;
        }
        else if ($raingauge_step2 <= $val && $val < $raingauge_step3)
        {
            $cal_buf = 3;
        }
        else if ($raingauge_step3 <= $val)
        {
            $cal_buf = 4;
        }
        else
        {
            $cal_buf = 0;
        }
        return $cal_buf;
    }
    function get_status_code_raingauge3($chn_id)
    {
		$val_10 = 0;
        $time_buf1 = new DateTime();
        $time_buf1->modify('-10 minutes');
        $time_buf1 = $time_buf1->format('Y-m-d H:i:s');
        
		$val_60 = 0;
        $time_buf2 = new DateTime();
        $time_buf2->modify('-60 minutes');
        $time_buf2 = $time_buf2->format('Y-m-d H:i:s');
		
		$val_1440 = 0;
        $time_buf3 = new DateTime();
        $time_buf3->modify('-1440 minutes');
        $time_buf3 = $time_buf3->format('Y-m-d H:i:s');
		
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
			
            $sql_query = "DELETE FROM `rain-gauge` WHERE `time`<'" . $time_buf3 . "';";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
                $stmt->close();
            }
			
            $sql_query = "SELECT SUM(val) FROM `rain-gauge` WHERE chn_id=" . $chn_id . " AND time>='" . $time_buf1 . "' ORDER BY sn DESC;";
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
				    $val_10 = $data_buf["total_val"];
				}
                $stmt->close();
            }
			
            $sql_query = "SELECT SUM(val) FROM `rain-gauge` WHERE chn_id=" . $chn_id . " AND time>='" . $time_buf2 . "' ORDER BY sn DESC;";
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
				    $val_60 = $data_buf["total_val"];
				}
                $stmt->close();
            }
			
            $sql_query = "SELECT SUM(val) FROM `rain-gauge` WHERE chn_id=" . $chn_id . " AND time>='" . $time_buf3 . "' ORDER BY sn DESC;";
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
				    $val_1440 = $data_buf["total_val"];
				}
                $stmt->close();
            }
			
            $db_link->close();
    	}
        //---------------------------------------------------------
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $status_10 = 1;
		$status_60 = 1;
		$status_1440 = 1;
		
		$status_final = 1;
		$status_desc_final = '';
		$val_final = 0;
		//---------------------------------------------------------
        if ($raingauge_step1_10m <= $val_10)
        {
            $status_10 = 2;
        }
		//---------------------------------------------------------
        if ($val_60 < $raingauge_step1)
        {
            $status_60 = 1;
        }
        else if ($raingauge_step1 <= $val_60 && $val_60 < $raingauge_step2)
        {
            $status_60 = 2;
        }
        else if ($raingauge_step2 <= $val_60 && $val_60 < $raingauge_step3)
        {
            $status_60 = 3;
        }
        else if ($raingauge_step3 <= $val_60)
        {
            $status_60 = 4;
        }
		//---------------------------------------------------------
        if ($val_1440 < $raingauge_step1_1440m)
        {
            $status_1440 = 1;
        }
        else if ($raingauge_step1_1440m <= $val_1440 && $val_1440 < $raingauge_step2_1440m)
        {
            $status_1440 = 2;
        }
        else if ($raingauge_step2_1440m <= $val_1440 && $val_1440 < $raingauge_step3_1440m)
        {
            $status_1440 = 3;
        }
        else if ($raingauge_step3_1440m <= $val_1440)
        {
            $status_1440 = 4;
        }
		//---------------------------------------------------------
		if ($status_10 > $status_final)
		{
			$status_final = $status_10;
			$status_desc_final = '10分鐘雨量達到';
			$val_final = $val_10;
		}
		if ($status_60 > $status_final)
		{
			$status_final = $status_60;
			$status_desc_final = '1小時雨量達到';
			$val_final = $val_60;
		}
		if ($status_1440 > $status_final)
		{
			$status_final = $status_1440;
			$status_desc_final = '24小時雨量達到';
			$val_final = $val_1440;
		}
		//---------------------------------------------------------
        return array($status_final, $status_desc_final, $val_final);
    }
    function get_total_value_raingauge2($chn_id)
    {
        $val = 0;
        
        $time_buf=new DateTime();
        $time_buf->modify('-60 minutes');
        $time_buf = $time_buf->format('Y-m-d H:i:s');
        
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
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
    	
        return $val;
    }
    //----------------------------------------------------------------------
    function get_chn_alarm_step1_5t($chn_id)
    {
        $result = 0;
        
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            $sql_query = "SELECT status FROM chndata WHERE chn_id=" . $chn_id . " ORDER BY sn DESC LIMIT 6;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count > 0)
				{
    				$stmt->bind_result($data_buf["status"]);
				    while ($stmt->fetch())
				    {
				        if ($data_buf["status"] == 2)
				        {
				            $result = 1;
				        }
				        else
				        {
				            $result = 0;
				            break;
				        }
				    }
				}
                $stmt->close();
            }
            $db_link->close();
    	}
    	
        return $result;
    }
    function get_chn_alarm_step2_5t($chn_id)
    {
        $result = 0;
        
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            $sql_query = "SELECT status FROM chndata WHERE chn_id=" . $chn_id . " ORDER BY sn DESC LIMIT 6;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count > 0)
				{
    				$stmt->bind_result($data_buf["status"]);
				    while ($stmt->fetch())
				    {
				        if ($data_buf["status"] == 3)
				        {
				            $result = 1;
				        }
				        else
				        {
				            $result = 0;
				            break;
				        }
				    }
				}
                $stmt->close();
            }
            $db_link->close();
    	}
    	
        return $result;
    }
    function get_chn_alarm_step3_5t($chn_id)
    {
        $result = 0;
        
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    	if ($db_link == TRUE)
    	{
            $db_link->query("SET NAMES \"utf8\"");
            $sql_query = "SELECT status FROM chndata WHERE chn_id=" . $chn_id . " ORDER BY sn DESC LIMIT 6;";
            $stmt = $db_link->prepare($sql_query);
            if ($stmt == TRUE)
            {
                $stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count > 0)
				{
    				$stmt->bind_result($data_buf["status"]);
				    while ($stmt->fetch())
				    {
				        if ($data_buf["status"] == 4)
				        {
				            $result = 1;
				        }
				        else
				        {
				            $result = 0;
				            break;
				        }
				    }
				}
                $stmt->close();
            }
            $db_link->close();
    	}
    	
        return $result;
    }
    //----------------------------------------------------------------------
    function get_status_circle_linepower($val, $time)
    {
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        $val = abs($val);
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_lost_time_limit)
        {
            $str_buf = $black_circle;
        }
        else
        {
            if ($val == 0)
            {
                $str_buf = $red_circle;
            }
            else
            {
                $str_buf = $green_circle;
            }
        }
        return $str_buf;
    }
	//----------------------------------------------------------------------
    function get_status_circle_batteryvoltage($val, $time)
    {
        $cal_buf1 = time();
        $cal_buf1 -= $time;
        
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $str_buf = '';
        
        if ($cal_buf1 >= $sensor_lost_time_limit)
        {
            $str_buf = $black_circle;
        }
        else
        {
            if ($val < $battery_voltage_min)
            {
                $str_buf = $red_circle;
            }
            else
            {
                $str_buf = $green_circle;
            }
        }
        return $str_buf;
    }
	//----------------------------------------------------------------------
    function get_status_code_batteryvoltage($val)
    {
        require("C:\\xampp\\htdocs\\default\\variable.php");
        $cal_buf = 0;
		
		if ($val < $battery_voltage_min)
		{
			$cal_buf = 3;
		}
		else
		{
			$cal_buf = 1;
		}
        return $cal_buf;
    }
	//----------------------------------------------------------------------
	
?>
