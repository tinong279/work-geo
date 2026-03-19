<?php
    
    //===============================================================
    require("C:\\xampp\\htdocs\\default\\function.php");
    //===============================================================
    $m_token = "a2e7176e8ada09cf8a3c3a23556cf68700ef248b622a582512261538ad5551e8dbae1498352d7f421c50d4c6d7b9fd163300441c363fd441dd30b6ad278e7641";
    $token = "";
    $msg = "";
    //===============================================================
    if (isset($_POST["token"]))
    {
        $token = $_POST["token"];
    }
    if (isset($_POST["msg"]))
    {
        $msg = $_POST["msg"];
    }
    //===============================================================
    if ($m_token === $token && strlen($msg) > 0)
    {
        $_array_buf = explode(" ", $msg);
        if (count($_array_buf) === 3)
        {
            $_data = $_array_buf[0];
            $_rssi = $_array_buf[1];
            $_snr = $_array_buf[2];
            $_rssi = floatval($_rssi);
            $_snr = floatval($_snr);
            if (strlen($_data) == 24)
            {
                $str_buf = $_data[22] . $_data[23];
                if ($str_buf == "5a")
                {
                    $_id = $_data[0] . $_data[1];
                    $_id = hexdec($_id);
                    $_ad1 = $_data[2] . $_data[3] . $_data[4] . $_data[5];
                    $_ad1 = hex2dec_2bytes_ones_complement($_ad1);
                    $_ad1 *= 5;
                    $_ad2 = $_data[6] . $_data[7] . $_data[8] . $_data[9];
                    $_ad2 = hex2dec_2bytes_ones_complement($_ad2);
                    $_ad2 *= 5;
                    $_count = $_data[10] . $_data[11] . $_data[12] . $_data[13];
                    $_count = hexdec($_count);
                    $_battery = $_data[18] . $_data[19] . $_data[20] . $_data[21];
                    $_battery = hexdec($_battery);
                    $_battery /= 100;
                	$time = new DateTime;
                	$time = $time->format('Y-m-d H:i:s');
					
					if ($_id == 1 || $_id == 2)
					{
						if ($_SERVER['REMOTE_ADDR'] == '111.70.30.250')
						{
							exit();
						}
						// exit();
					}
					else if ($_id == 21 || $_id == 22)
					{
						if ($_SERVER['REMOTE_ADDR'] == '111.70.30.252')
						{
							exit();
						}
						// exit();
					}
					else if ($_id == 31 || $_id == 32)
					{
						if ($_SERVER['REMOTE_ADDR'] == '111.70.30.253')
						{
							exit();
						}
						// exit();
					}
					
                    require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
                	if ($db_link == TRUE)
                	{
                        $db_link->query("SET NAMES \"utf8\"");
                        $sql_query = "INSERT INTO `rawdata`(time, _id, _ad1, _ad2, _count, _battery, _data, _rssi, _snr) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
                        $stmt = $db_link->prepare($sql_query);
                        if ($stmt == TRUE)
                        {
                            $stmt->bind_param("siddidsdd", $time, $_id, $_ad1, $_ad2, $_count, $_battery, $_data, $_rssi, $_snr);
                            $stmt->execute();
                            $stmt->close();
                        }
                        filter_lora_id_to_chndata($db_link, $time, $_id, $_ad1, $_ad2, $_count, $_battery, $_rssi, $_snr);
                        $db_link->close();
                	}
                }
            }
        }
    }
    //----------------------------------------------------------------------
    function filter_lora_id_to_chndata($db_link, $time, $id, $ad1, $ad2, $count, $battery, $rssi, $snr)
    {
        require("C:\\xampp\\htdocs\\default\\variable.php");
        require("C:\\xampp\\htdocs\\default\\lora-offset.php");
		
		if ($id == 1)
        {
            $offset1 = $id_1_ad1_offset;
            $offset2 = $id_1_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			$cal_buf1 = calc_chn_avg($db_link, 101, $cal_buf1);
			$cal_buf2 = calc_chn_avg($db_link, 102, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
			
            write_chndata($db_link, $time, 101, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 102, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
			
            write_chndata($db_link, $time, 105, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 106, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 107, $snr, 0, 0, 0, 0);
            
			// 2023-10-04
            write_chndata($db_link, $time, 201, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 202, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 205, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 206, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 207, $snr, 0, 0, 0, 0);
			
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(101);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(101);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(101);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(101) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(101) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(1);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(102);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(102);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(102);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(102) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(102) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(1);
					}
                }
            }
			
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 2)
        {
            $offset1 = $id_2_ad1_offset;
            $offset2 = $id_2_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			$cal_buf1 = calc_chn_avg($db_link, 201, $cal_buf1);
			$cal_buf2 = calc_chn_avg($db_link, 202, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 201, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 202, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 205, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 206, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 207, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(201);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(201);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(201);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(201) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(201) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(1);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(202);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(202);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(202);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(202) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(202) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(1);
					}
                }
            }
            
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 9)
        {
            $offset3 = $id_9_count_offset;
            $cal_buf3 = ($count * 0.5) + $offset3;
			
			write_rain_gauge_data($db_link, $time, 903, $cal_buf3);
			
            //$status_code_buf3 = get_status_code_raingauge($cal_buf3);
            //$status_code_buf3 = get_status_code_raingauge2($cal_buf3, 903);
			
			$arr_buf1 = get_status_code_raingauge3(903);
			$status_code_buf3 = $arr_buf1[0];
			
            write_chndata($db_link, $time, 903, $cal_buf3, $status_code_buf3, $raingauge_step1, $raingauge_step2, $raingauge_step3);
            write_chndata($db_link, $time, 905, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 906, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 907, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            if ($status_code_buf3 > 1)
            {
                if ($cal_buf3 >= 200)
                {
                    
                }
                else
                {
                    $str_buf1 = "";
                    $line_alarm_flag += 1;
                    $str_buf1 .= get_chn_name(903) . " " . $arr_buf1[1];
                    $str_buf1 .= get_status_description($status_code_buf3);
                    $str_buf1 .= " " . number_format($arr_buf1[2], 0) . "mm";
					
					if (query_last_status_as_same(903) != $status_code_buf3)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(1);
					}
                }
            }
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 11)
        {
            $offset1 = $id_11_ad1_offset;
            $offset2 = $id_11_ad2_offset;
            $cal_buf1 = ($ad1 / 2.8) + $offset1;
            $cal_buf2 = ($ad2 / 2.8) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			$cal_buf1 = calc_chn_avg($db_link, 1101, $cal_buf1);
			$cal_buf2 = calc_chn_avg($db_link, 1102, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 1101, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 1102, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 1105, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 1106, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 1107, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(1101);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(1101);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(1101);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(1101) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(1101) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(2);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(1102);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(1102);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(1102);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(1102) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(1102) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(2);
					}
                }
            }

            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 12)
        {
            $offset1 = $id_12_ad1_offset;
            $offset2 = $id_12_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			$cal_buf1 = calc_chn_avg($db_link, 1201, $cal_buf1);
			$cal_buf2 = calc_chn_avg($db_link, 1202, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 1201, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 1202, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 1205, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 1206, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 1207, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(1201);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(1201);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(1201);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(1201) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(1201) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(2);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(1202);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(1202);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(1202);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(1202) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(1202) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(2);
					}
                }
            }
            
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 19)
        {
            $offset3 = $id_19_count_offset;
            $cal_buf3 = ($count * 0.5) + $offset3;
			
			write_rain_gauge_data($db_link, $time, 1903, $cal_buf3);
			
            //$status_code_buf3 = get_status_code_raingauge($cal_buf3);
            //$status_code_buf3 = get_status_code_raingauge2($cal_buf3, 1903);
			
			$arr_buf1 = get_status_code_raingauge3(1903);
			$status_code_buf3 = $arr_buf1[0];
			
            write_chndata($db_link, $time, 1903, $cal_buf3, $status_code_buf3, $raingauge_step1, $raingauge_step2, $raingauge_step3);
            write_chndata($db_link, $time, 1905, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 1906, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 1907, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            if ($status_code_buf3 > 1)
            {
                if ($cal_buf3 >= 200)
                {
                    
                }
                else
                {
                    $str_buf1 = "";
                    $line_alarm_flag += 1;
                    $str_buf1 .= get_chn_name(1903) . " " . $arr_buf1[1];
                    $str_buf1 .= get_status_description($status_code_buf3);
                    $str_buf1 .= " " . number_format($arr_buf1[2], 0) . "mm";
					
					if (query_last_status_as_same(1903) != $status_code_buf3)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(2);
					}
                }
            }
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 21)
        {
            $offset1 = $id_21_ad1_offset;
            $offset2 = $id_21_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			// $cal_buf1 = calc_chn_avg($db_link, 2101, $cal_buf1);
			// $cal_buf2 = calc_chn_avg($db_link, 2102, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 2101, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 2102, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 2105, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 2106, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 2107, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(2101);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(2101);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(2101);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(2101) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(2101) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(3);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(2102);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(2102);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(2102);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(2102) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(2102) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(3);
					}
                }
            }

            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 22)
        {
            $offset1 = $id_22_ad1_offset;
            $offset2 = $id_22_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			// $cal_buf1 = calc_chn_avg($db_link, 2201, $cal_buf1);
			// $cal_buf2 = calc_chn_avg($db_link, 2202, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 2201, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 2202, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 2205, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 2206, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 2207, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(2201);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(2201);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(2201);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(2201) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(2201) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(3);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(2202);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(2202);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(2202);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(2202) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(2202) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(3);
					}
                }
            }
            
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 29)
        {
            $offset3 = $id_29_count_offset;
            $cal_buf3 = ($count * 0.5) + $offset3;
			
			write_rain_gauge_data($db_link, $time, 2903, $cal_buf3);
			
            //$status_code_buf3 = get_status_code_raingauge($cal_buf3);
            //$status_code_buf3 = get_status_code_raingauge2($cal_buf3, 2903);
			
			$arr_buf1 = get_status_code_raingauge3(2903);
			$status_code_buf3 = $arr_buf1[0];
			
            write_chndata($db_link, $time, 2903, $cal_buf3, $status_code_buf3, $raingauge_step1, $raingauge_step2, $raingauge_step3);
            write_chndata($db_link, $time, 2905, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 2906, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 2907, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            if ($status_code_buf3 > 1)
            {
                if ($cal_buf3 >= 200)
                {
                    
                }
                else
                {
                    $str_buf1 = "";
                    $line_alarm_flag += 1;
                    $str_buf1 .= get_chn_name(2903) . " " . $arr_buf1[1];
                    $str_buf1 .= get_status_description($status_code_buf3);
                    $str_buf1 .= " " . number_format($arr_buf1[2], 0) . "mm";
					
					if (query_last_status_as_same(2903) != $status_code_buf3)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(3);
					}
                }
            }
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 31)
        {
            $offset1 = $id_31_ad1_offset;
            $offset2 = $id_31_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			// $cal_buf1 = calc_chn_avg($db_link, 3101, $cal_buf1);
			// $cal_buf2 = calc_chn_avg($db_link, 3102, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 3101, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 3102, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 3105, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 3106, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 3107, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(3101);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(3101);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(3101);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(3101) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(3101) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(4);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(3102);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(3102);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(3102);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(3102) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(3102) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(4);
					}
                }
            }

            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 32)
        {
            $offset1 = $id_32_ad1_offset;
            $offset2 = $id_32_ad2_offset;
            $cal_buf1 = ($ad1 / 0.28) + $offset1;
            $cal_buf2 = ($ad2 / 0.28) + $offset2;
			
			if ($cal_buf1 > $inclinometer_max)
			{
				$cal_buf1 = $inclinometer_max;
			}
			else if ($cal_buf1 < (0-$inclinometer_max))
			{
				$cal_buf1 = (0-$inclinometer_max);
			}
			if ($cal_buf2 > $inclinometer_max)
			{
				$cal_buf2 = $inclinometer_max;
			}
			else if ($cal_buf2 < (0-$inclinometer_max))
			{
				$cal_buf2 = (0-$inclinometer_max);
			}
			
			// $cal_buf1 = calc_chn_avg($db_link, 3201, $cal_buf1);
			// $cal_buf2 = calc_chn_avg($db_link, 3202, $cal_buf2);
			
            $status_code_buf1 = get_status_code_inclinometer($cal_buf1);
            $status_code_buf2 = get_status_code_inclinometer($cal_buf2);
            write_chndata($db_link, $time, 3201, $cal_buf1, $status_code_buf1, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 3202, $cal_buf2, $status_code_buf2, $inclinometer_step1, $inclinometer_step2, $inclinometer_step3);
            write_chndata($db_link, $time, 3205, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 3206, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 3207, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(3201);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(3201);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(3201);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf1 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(3201) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf1);
					$str_buf1 .= " " . number_format($cal_buf1, 2) . "度";
					
					if (query_last_status_as_same(3201) != $status_code_buf1)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(4);
					}
                }
            }
            
            $chn_alarm_count_buf1 = get_chn_alarm_step1_5t(3202);
            $chn_alarm_count_buf2 = get_chn_alarm_step2_5t(3202);
            $chn_alarm_count_buf3 = get_chn_alarm_step3_5t(3202);
            // if ($chn_alarm_count_buf1 == 0 && $chn_alarm_count_buf2 == 0 && $chn_alarm_count_buf3 == 0)
            if (1 == 1)
			{
                if ($status_code_buf2 > 1)
                {
					$str_buf1 = "";
					$line_alarm_flag += 1;
					$str_buf1 .= get_chn_name(3202) . " 達到";
					$str_buf1 .= get_status_description($status_code_buf2);
					$str_buf1 .= " " . number_format($cal_buf2, 2) . "度";
					
					if (query_last_status_as_same(3202) != $status_code_buf2)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(4);
					}
                }
            }
            
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else if ($id == 39)
        {
            $offset3 = $id_39_count_offset;
            $cal_buf3 = ($count * 0.5) + $offset3;
			
			write_rain_gauge_data($db_link, $time, 3903, $cal_buf3);
			
            //$status_code_buf3 = get_status_code_raingauge($cal_buf3);
            //$status_code_buf3 = get_status_code_raingauge2($cal_buf3, 3903);
			
			$arr_buf1 = get_status_code_raingauge3(3903);
			$status_code_buf3 = $arr_buf1[0];
			
            write_chndata($db_link, $time, 3903, $cal_buf3, $status_code_buf3, $raingauge_step1, $raingauge_step2, $raingauge_step3);
            write_chndata($db_link, $time, 3905, $battery, 0, 0, 0, 0);
            write_chndata($db_link, $time, 3906, $rssi, 0, 0, 0, 0);
            write_chndata($db_link, $time, 3907, $snr, 0, 0, 0, 0);
            
            $line_alarm_flag = 0;
            $str_buf1 = "\n";
            if ($status_code_buf3 > 1)
            {
                if ($cal_buf3 >= 200)
                {
                    
                }
                else
                {
                    $str_buf1 = "";
                    $line_alarm_flag += 1;
                    $str_buf1 .= get_chn_name(3903) . " " . $arr_buf1[1];
                    $str_buf1 .= get_status_description($status_code_buf3);
                    $str_buf1 .= " " . number_format($arr_buf1[2], 0) . "mm";
					
					if (query_last_status_as_same(3903) != $status_code_buf3)
					{
						Send_Line_Notify($str_buf1);
						Open_CMS(4);
					}
                }
            }
            // if ($line_alarm_flag > 0)
            // {
                // Send_Line_Notify($str_buf1);
            // }
        }
        else
        {
            
        }
    }
    function write_rain_gauge_data($db_link, $time, $chn_id, $val)
    {
        $sql_query = "INSERT INTO `rain-gauge`(`time`, `chn_id`, `val`) VALUES (?, ?, ?);";
        $stmt = $db_link->prepare($sql_query);
        if ($stmt == TRUE)
        {
            $stmt->bind_param("sid", $time, $chn_id, $val);
            $stmt->execute();
            $stmt->close();
        }
    }
    function write_chndata($db_link, $time, $chn_id, $val, $status, $s1_val, $s2_val, $s3_val)
    {
        $sql_query = "INSERT INTO `chndata`(time, chn_id, val, status, s1, s2, s3) VALUES (?, ?, ?, ?, ?, ?, ?);";
        $stmt = $db_link->prepare($sql_query);
        if ($stmt == TRUE)
        {
            $stmt->bind_param("sididdd", $time, $chn_id, $val, $status, $s1_val, $s2_val, $s3_val);
            $stmt->execute();
            $stmt->close();
        }
    }
    function calc_chn_avg($db_link, $chn_id, $val)
    {
		$res = 0;
		
		$sql_query = "SELECT `val` FROM `chndata` WHERE `chn_id`=? ORDER BY `sn` DESC LIMIT 19;";
		$stmt = $db_link->prepare($sql_query);
        if ($stmt == TRUE)
        {
            $stmt->bind_param("i", $chn_id);
			$stmt->execute();
			$stmt->bind_result($data_buf["val"]);
			
			$cal_buf = 0;
			$count_buf = 0;
			while($stmt->fetch())
			{
				$cal_buf += $data_buf["val"];
				$count_buf += 1;
			}
            $stmt->close();
			
			$cal_buf += $val;
			$count_buf += 1;
			$cal_buf /= $count_buf;
			
			$res = $cal_buf;
        }
		
		return $res;
    }
    function Send_Line_Notify($msg)
    {
		try
		{
			$msg .= "\n" . "https://miaoli62-county126.miaoli.gov.tw/manager-page/";
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
				// 'Authorization: Bearer utozIWAQJCeW360KossFZBj5joZMu6n3hybumviz25g'
			);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("message" => $msg)));
			$output = curl_exec($ch);
			curl_close($ch);
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
    function Open_CMS($site_id)
    {
		$url = '';
		
		if ($site_id == 1)
		{
			// $url = 'https://www.miaoli62.com/manager-page/api/cms-on1-rdws.php';
			$url = 'http://127.0.0.1/manager-page/api/cms-on1-rdws.php';
		}
		else if ($site_id == 2)
		{
			// $url = 'https://www.miaoli62.com/manager-page/api/cms-on2-rdws.php';
			$url = 'http://127.0.0.1/manager-page/api/cms-on2-rdws.php';
		}
		else if ($site_id == 3)
		{
			// $url = 'https://www.miaoli62.com/manager-page/api/cms-on3-rdws.php';
			$url = 'http://127.0.0.1/manager-page/api/cms-on3-rdws.php';
		}
		else if ($site_id == 4)
		{
			// $url = 'https://www.miaoli62.com/manager-page/api/cms-on4-rdws.php';
			$url = 'http://127.0.0.1/manager-page/api/cms-on4-rdws.php';
		}
		
		$url .= '?token=7654354354675426652656';
		
		try
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			
			$output = curl_exec($ch);
			curl_close($ch);
		}
		catch (Exception $e)
		{
			
		}
    }
	
    function get_chn_name($chn)
    {
        $filename = "C:\\xampp\\htdocs\\default\\chn-setting.json";    
        $fp = fopen($filename, "r");   
        $json_obj = fread($fp, filesize($filename)); 
        $json_obj = json_decode($json_obj);
        fclose($fp);
        $chn_name = $json_obj->{"chn_name"}->{$chn};
		
		$time_buf1 = new DateTime;
		$time_buf1 = $time_buf1->format('Y-m-d H:i:s');
		
		$str_buf1 = '';
		$str_buf2 = '';
		
		/*
		$str_buf1 = explode(" ", $chn_name);
		$str_buf2 = '';
		
		for ($i=0; $i<count($str_buf1); $i++)
		{
			if ($i == 0)
			{
				$str_buf2 = $str_buf1[$i] . ' ' . $time_buf1;
			}
			else
			{
				$str_buf2 .= ' ' . $str_buf1[$i];
			}
		}
		*/
		
		$str_buf2 .= $time_buf1 . "\n" . $chn_name;
		
        return $str_buf2;
    }
    function get_status_description($val)
    {
        $str_buf = "";
        switch ($val)
        {
            case 2:
                $str_buf .= "預警";
                break;
            case 3:
                $str_buf .= "警戒";
                break;
            case 4:
                $str_buf .= "行動";
                break;
            default:
                $str_buf .= "未定義";
        }
        return $str_buf;
    }
    function query_last_status_as_same($chn_id)
    {
		$res = 0;
		require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
		if ($db_link == TRUE)
		{
			$db_link->query("SET NAMES \"utf8\"");
			$sql_query = "SELECT `status` FROM `chndata` WHERE `chn_id`=? ORDER BY `sn` DESC LIMIT 2;";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == TRUE)
			{
				$stmt->bind_param("i", $chn_id);
				$stmt->execute();
				
				$stmt->bind_result($data_buf["status"]);
				$stmt->fetch();
				$stmt->fetch();
				
				$res = $data_buf["status"];
				
				$stmt->close();
			}
			$db_link->close();
		}
		return $res;
    }
    //----------------------------------------------------------------------
    
?>
