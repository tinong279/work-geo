<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    
    $chn = 0;
    $date_start = "";
    $date_stop = "";
    
    $json_obj = '';
    $chn_type_flag = 0;
    $chn_type_unit = "";
    $chn_name = '';
    
    $chn = $_GET["chn"];
    $chn = intval($chn);
    
    $date_start = $_GET["date_start"];
    $date_start = date('Y-m-d H:i:s', $date_start);
    
    $date_stop = $_GET["date_stop"];
    $date_stop = date('Y-m-d H:i:s', $date_stop);
    
    require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
	if ($db_link == TRUE)
	{
		$db_link->query("SET NAMES \"utf8\"");
		
		$sql_query = "SELECT time, val, status, s1, s2, s3 FROM `chndata` WHERE chn_id=? AND time>=? AND time<=? ORDER BY time ASC";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == TRUE)
		{
			$stmt->bind_param("iss", $chn, $date_start, $date_stop);
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

$str_buf = '
<!DOCTYPE html>
<html lang="zh-tw">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
#top_Btn
{
	width: 60px;
	position: fixed;
	bottom: 80px;
	right: 30px;
	z-index: 99;
	font-size: 12px;
	border: none;
	outline: none;
	background-color: gray;
	color: white;
	cursor: pointer;
	padding: 15px;
	border-radius: 4px;
}
#bottom_Btn
{
	width: 60px;
	position: fixed;
	bottom: 20px;
	right: 30px;
	z-index: 99;
	font-size: 12px;
	border: none;
	outline: none;
	background-color: gray;
	color: white;
	cursor: pointer;
	padding: 15px;
	border-radius: 4px;
}
</style>
';
echo $str_buf;

get_css_js_link();

$str_buf = '<title>' . $chn_name . '</title>' .
'
</head>
<body>
<button id="top_Btn" class="w3-button" style="text-align:center;vertical-align:middle;" onclick="location.href=\'#top\'">▲</button>
<button id="bottom_Btn" class="w3-button" style="text-align:center;vertical-align:middle;" onclick="location.href=\'#bottom\'">▼</button>
<a name="top" id="top"></a>
<div class="w3-content" style="max-width:480px;">
<div class="w3-padding">
';
echo $str_buf;
$str_buf = '<div>設備：' . $chn_name . '</div>' . "\n";
$str_buf .= '<div>開始時間：' . $date_start . '</div>' . "\n";
$str_buf .= '<div>結束時間：' . $date_stop . '</div>' . "\n";
$str_buf .=
'<table class="w3-table-all">
<tbody>
<tr>';
$str_buf .= '<th style="vertical-align:middle;">' . '時間' . '</th>';
$str_buf .= '<th style="vertical-align:middle;">' . '警報狀態' . '</th>';
$str_buf .= '<th style="vertical-align:middle;">' . $chn_type_unit . '</th>';
$str_buf .= '</tr>';
echo $str_buf;

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

$str_buf =
'</tbody>
</table>
</div>
</div>
<a name="bottom" id="bottom"></a>
</body>
</html>
';
echo $str_buf;

			}
			else
			{
				$stmt->close();
			}
		}
		$db_link->close();
	}
	
?>
