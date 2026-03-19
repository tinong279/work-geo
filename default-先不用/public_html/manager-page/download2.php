<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    
    $chn = 0;
    $date_start = "";
    $date_stop = "";
    
    $json_obj = '';
    $chn_type_flag = 0;
    $chn_type_unit = '';
    $chn_name = '';
    
    $chn = $_GET["chn"];
    $chn = intval($chn);
    
    $alarm_type = $_GET["alarm_type"];
    $alarm_type = intval($alarm_type);
    
    $date_start = $_GET["date_start"];
    $date_start = date('Y-m-d H:i:s', $date_start);
    
    $date_stop = $_GET["date_stop"];
    $date_stop = date('Y-m-d H:i:s', $date_stop);
    
    require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
	if ($db_link == TRUE)
	{
		$db_link->query("SET NAMES \"utf8\"");
		
		$sql_query = "SELECT time, val, status, s1, s2, s3 FROM `chndata` WHERE chn_id=? AND time>=? AND time<=? AND status=? ORDER BY time ASC";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == TRUE)
		{
			$stmt->bind_param("issi", $chn, $date_start, $date_stop, $alarm_type);
			$stmt->execute();
			$stmt->store_result();
			$data_count = $stmt->num_rows;
			if ($data_count > 0)
			{
                $filename = "C:\\xampp\\htdocs\\default\\chn-setting.json";    
                $fp = fopen($filename, "r");   
                $json_obj = fread($fp, filesize($filename)); 
                $json_obj = json_decode($json_obj);
                fclose($fp);
                
                $chn_type_flag = $json_obj->{"chn_type"}->{$chn};
                $chn_name = $json_obj->{"chn_name"}->{$chn};
                
                if ($chn_type_flag == 1 || $chn_type_flag == 2)
                {
                    $chn_type_unit = '度';
                }
                else if ($chn_type_flag == 3)
                {
                    $chn_type_unit = 'mm';
                }
                else if ($chn_type_flag == 5)
                {
                    $chn_type_unit = '電壓';
                }
                else if ($chn_type_flag == 6)
                {
                    $chn_type_unit = 'RSSI';
                }
                else if ($chn_type_flag == 7)
                {
                    $chn_type_unit = 'SNR';
                }
                else
                {
                    $chn_type_unit = "未定義";
                }
                
				$stmt->bind_result
				(
			        $data_buf["time"],
			        $data_buf["val"],
			        $data_buf["status"],
			        $data_buf["s1"],
			        $data_buf["s2"],
			        $data_buf["s3"]
			    );
			    
                header("Content-Type: application/octet-stream");
                header("Content-Transfer-Encoding: binary\n");
                header('Content-Disposition: attachment; filename="' . $chn_name . '.xls"');

                echo "<!DOCTYPE html>\n";
                echo "<html lang='zh-tw'>\n";
                echo "<head>\n";
                echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n";
                echo "</head>\n";
                echo "<body>\n";
                
                echo "<table>\n";
                echo "<tr>";
                echo "<td>設備</td>";
                echo "<td>" . $chn_name . "</td>";
                echo "</tr>\n";
                echo "<tr>";
                echo "<td>開始時間</td>";
                echo "<td>" . $date_start . "</td>";
                echo "</tr>\n";
                echo "<tr>";
                echo "<td>結束時間</td>";
                echo "<td>" . $date_stop . "</td>";
                echo "</tr>\n";
                echo "</table>\n";
                
                echo "<table>\n";
                echo "<tr>";
                echo "<th>時間</th>";
                echo "<th>" . "警報狀態" . "</th>";
                echo "<th>" . $chn_type_unit . "</th>";
                echo "</tr>\n";
			    $count_buf = 0;
			    while ($stmt->fetch())
			    {
			        echo "<tr>";
			        echo "<td>" . $data_buf["time"] . "</td>";
			        
			        if ($data_buf["status"] == 1)
			        {
			            echo "<td>" . '正常' . "</td>";
			        }
			        else if ($data_buf["status"] == 2)
			        {
			            echo "<td>" . '預警' . "</td>";
			        }
			        else if ($data_buf["status"] == 3)
			        {
			            echo "<td>" . '警戒' . "</td>";
			        }
			        else if ($data_buf["status"] == 4)
			        {
			            echo "<td>" . '行動' . "</td>";
			        }
			        else
			        {
			            echo "<td>" . '未定義:' . $data_buf["status"] . "</td>";
			        }
			        echo "<td>" . $data_buf["val"] . "</td>";
			        echo "</tr>\n";
			    }
				$stmt->close();
			    echo "</table>\n";
			    echo "</body>\n";
			    echo "</html>\n";
			}
			else
			{
				$stmt->close();
			}
		}
		$db_link->close();
	}
	
?>
