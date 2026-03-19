<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    require("C:\\xampp\\htdocs\\default\\history2-func.php");
    require("C:\\xampp\\htdocs\\default\\public-func.php");
    
    $chn = 0;
    $date_start = "";
    $date_start_timestamp = 0;
    $date_stop = "";
    $date_stop_timestamp = 0;
    
    $time_buf=new DateTime();
    $time_buf->modify('-7 days');
    $date_start = $time_buf->format('Y-m-d');
    $date_start .= ' 00:00';
    $date_stop = date("Y-m-d H:i");
    
    $all_time = '';
    $all_val = '';
    $all_status = '';
    $all_s1 = '';
    $all_s2 = '';
    $all_s3 = '';
    $all_s1n = '';
    $all_s2n = '';
    $all_s3n = '';
    
    $submit_flag = false;
    $json_obj = '';
    $chn_type_flag = 0;
    $chn_type_unit = '';
    
    $alarm_type = 2;
    $alarm_type_str = '';
    $alarm_type_led = '';
    $alarm_type_table_str = '';
    
    $err_msg = "";
    
    if (isset($_POST["submit"]))
    {
        if ($_POST["submit"] == "submit")
        {
            $pattern1 = '/^([0-9]+)$/';
            $pattern2 = '/^([0-9Tt\\\-\:]+)$/';
            
            if (preg_match($pattern1, $pattern2) == TRUE)
            {
                
            }
            $submit_flag = true;
        }
        
        $chn = $_POST["chn"];
        $chn = intval($chn);
        
        $alarm_type = $_POST["alarm_type"];
        $alarm_type = intval($alarm_type);
        
        if ($alarm_type >=2 && $alarm_type <=4)
        {
            if ($alarm_type == 2)
            {
                $alarm_type_str = '預警';
                $alarm_type_led = '🟡';
            }
            else if ($alarm_type == 3)
            {
                $alarm_type_str = '警戒';
                $alarm_type_led = '🟠';
            }
            else if ($alarm_type == 4)
            {
                $alarm_type_str = '行動';
                $alarm_type_led = '🔴';
            }
        }
        else
        {
            $alarm_type = 2;
        }
        
        $date_start = $_POST["date_start"];
        $date_start_timestamp = strtotime($date_start);
        $date_start = date('Y-m-d H:i', $date_start_timestamp);
        
        $date_stop = $_POST["date_stop"];
        $date_stop_timestamp = strtotime($date_stop);
        $date_stop = date('Y-m-d H:i', $date_stop_timestamp);
        
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
                        $chn_type_unit = '未定義';
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
				    
				    $count_buf = 0;
				    while ($stmt->fetch())
				    {
				        if ($count_buf == 0)
				        {
				            $count_buf += 1;
				            $all_time .= "'" . $data_buf["time"] . "'";
				            $all_val .= "" . $data_buf["val"];
				            $all_status .= "" . $data_buf["status"];
				            $all_s1 .= "" . 2;
				            $all_s2 .= "" . 3;
				            $all_s3 .= "" . 4;
				            $all_s1n .= "-" . $data_buf["s1"];
				            $all_s2n .= "-" . $data_buf["s2"];
				            $all_s3n .= "-" . $data_buf["s3"];
				        }
				        else
				        {
				            $all_time .= ",'" . $data_buf["time"] . "'";
				            $all_val .= "," . $data_buf["val"];
				            $all_status .= "," . $data_buf["status"];
				            $all_s1 .= "," . 2;
				            $all_s2 .= "," . 3;
				            $all_s3 .= "," . 4;
				            $all_s1n .= ",-" . $data_buf["s1"];
				            $all_s2n .= ",-" . $data_buf["s2"];
				            $all_s3n .= ",-" . $data_buf["s3"];
				        }
				        $alarm_type_table_str .= '<tr><td>' . $data_buf["time"] . '</td><td>' . $alarm_type_str . $alarm_type_led . '</td><td>' . number_format($data_buf["val"], 3) . '</td></tr>';
				    }
					$stmt->close();
				}
				else
				{
					$stmt->close();
					// $submit_flag = false;
					$err_msg = "查無資料";
				}
			}
			$db_link->close();
		}
    }
    
?>
<!DOCTYPE html>
<html lang="zh-tw">
	<head>
		<title>警戒資料查詢 - <?php echo $system_name; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

<?php get_css_js_link() ?>

		<script language="javascript">
		
		</script>
		
		<style>
			table, td, th
			{
				border: 1px solid #cccccc;
				border-collapse: collapse;
			}
			.th
			{
				text-align:center;
				vertical-align: middle;
			}
			.td
			{
				vertical-align: middle;
			}
		</style>
	</head>
	<body>

	<!-- Top -->
	<div class="w3-top" style="background-color:#343a40;">
		<div class="w3-bar w3-large">
			<a class="w3-bar-item w3-button w3-left w3-large w3-hide-large w3-padding-16" style="color:white;" href="javascript:void(0)" onclick="w3_open()">&#9776;</a>
			<label class="w3-bar-item w3-padding-16" style="color:white;font-size:18px;"><?php echo $system_name; ?></label>
			<a class="w3-bar-item w3-button w3-right w3-large w3-padding-16" style="color:white;" title="登出" href="logout.php">
				<svg style="height:20px;filter:invert(0%);" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M512 128v256c0 53.02-42.98 96-96 96h-72C330.7 480 320 469.3 320 456c0-13.26 10.75-24 24-24H416c26.4 0 48-21.6 48-48V128c0-26.4-21.6-48-48-48h-72C330.7 80 320 69.25 320 56C320 42.74 330.7 32 344 32H416C469 32 512 74.98 512 128zM367.9 273.9L215.5 407.6C209.3 413.1 201.3 416 193.3 416c-4.688 0-9.406-.9687-13.84-2.969C167.6 407.7 160 396.1 160 383.3V328H40C17.94 328 0 310.1 0 288V224c0-22.06 17.94-40 40-40H160V128.7c0-12.75 7.625-24.41 19.41-29.72C191.5 93.56 205.7 95.69 215.5 104.4l152.4 133.6C373.1 242.6 376 249.1 376 256S373.1 269.4 367.9 273.9zM315.8 256L208 161.1V232h-160v48h160v70.03L315.8 256z"></path>
				</svg>
			</a>
		</div>
	</div>

	<!-- Sidebar -->
	<div class="w3-sidebar w3-bar-block w3-collapse w3-animate-left" style="z-index:3;width:240px;background-color:#212529;" id="mySidebar">
	
		<div>

<?php get_sidebar_html(2); ?>

			<div style="margin-bottom:100px;"></div>
		</div>
		
	</div>
	
	<div class="w3-overlay w3-hide-large" onclick="w3_close()" id="myOverlay"></div>
	
	<div class="w3-main w3-container w3-padding-large" style="margin-left:240px;margin-top:59px;">
	
		<h1>警戒資料查詢</h1>
		
		<div class="w3-card-4" style="margin-top:10px;">

			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">

                <form action="" method="post" name="form" id="form" onsubmit="">
                    <div class="w3-third">
                		<span class="w3-text-gray" style="font-size:16px;"><b>設備　　　　　　</b></span>
                		<select name="chn" id="chn" class="w3-select w3-border w3-round" style="font-size:16px;width:95%;">

<?php
    if ($submit_flag == false)
    {
        get_chn_option(0);
    }
    else
    {
        get_chn_option($chn);
    }
    
    $date_start2 = strtotime($date_start);
    $date_start3 = date('Y-m-d', $date_start2);
    $date_start3 .= "T";
    $date_start3 .= date('H:i', $date_start2);
    
    $date_stop2 = strtotime($date_stop);
    $date_stop3 = date('Y-m-d', $date_stop2);
    $date_stop3 .= "T";
    $date_stop3 .= date('H:i', $date_stop2);
?>

                		</select>
                    </div>

                    
                    <div class="w3-third">
                        <span class="w3-text-gray" style="font-size:16px;"><b>警戒類型　　　　</b></span>
                        <select name="alarm_type" id="alarm_type" class="w3-select w3-border w3-round" style="font-size:16px;width:95%;">
                            
<?php

    if ($submit_flag == true)
    {
        $str_buf1 = '';
        if ($alarm_type == 2)
        {
            $str_buf1 .= '<option value="2" selected="selected">黃(預警)</option>';
            $str_buf1 .= '<option value="3">橙(警戒)</option>';
            $str_buf1 .= '<option value="4">紅(行動)</option>';
        }
        else if ($alarm_type == 3)
        {
            $str_buf1 .= '<option value="2">黃(預警)</option>';
            $str_buf1 .= '<option value="3" selected="selected">橙(警戒)</option>';
            $str_buf1 .= '<option value="4">紅(行動)</option>';
        }
        else if ($alarm_type == 4)
        {
            $str_buf1 .= '<option value="2">黃(預警)</option>';
            $str_buf1 .= '<option value="3">橙(警戒)</option>';
            $str_buf1 .= '<option value="4" selected="selected">紅(行動)</option>';
        }
        else
        {
            $str_buf1 .= '<option value="2">黃(預警)</option>';
            $str_buf1 .= '<option value="3">橙(警戒)</option>';
            $str_buf1 .= '<option value="4">紅(行動)</option>';
        }
        echo $str_buf1;
    }
    else
    {
        $str_buf1 = '';
        $str_buf1 .= '<option value="2">黃(預警)</option>';
        $str_buf1 .= '<option value="3">橙(警戒)</option>';
        $str_buf1 .= '<option value="4">紅(行動)</option>';
        echo $str_buf1;
    }

?>
                        </select>
                    </div>

                    <div class="w3-third">
                		<span class="w3-text-gray" style="font-size:16px;"><b>開始時間　　　　</b></span>
                		<input type="datetime-local" name="date_start" id="date_start" class="w3-input w3-border w3-round" style="font-size:16px;width:95%;" value="<?php echo $date_start3; ?>">
                    </div>
                    <div class="w3-third">
                		<span class="w3-text-gray" style="font-size:16px;"><b>結束時間　　　　</b></span>
                		<input type="datetime-local" name="date_stop" id="date_stop" class="w3-input w3-border w3-round" style="font-size:16px;width:95%;" value="<?php echo $date_stop3; ?>">
                    </div>
                    
                    <div class="w3-third">
                        <div style="margin-top:16px;"></div>
                		<button type="submit" name="submit" value="submit" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">查詢</button>
                		<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b><?php echo $err_msg; ?></b></span>
                    </div>
                </form>
                
			</div>

		</div>
		
<?php
    if ($submit_flag == false)
    {
        //echo "<!--";
    }
    echo "<!--";
?>
<div class="w3-card-4" style="margin-top:20px;min-height:240px;">
    <header class="w3-container w3-light-gray w3-padding-large w3-border">
        <svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
            <path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
        </svg>
        <span style="font-size:16px;margin-left:4px;">圖表 詳細資料</span>
    </header>
    <div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
        <div style="position:relative;width:100%;">
            <canvas id="history_chart" height="480"></canvas></canvas>
        </div>
        <a href="download2.php?<?php echo 'chn=' . $chn . '&date_start=' . $date_start_timestamp . '&date_stop=' . $date_stop_timestamp; ?>" target="_blank" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">下載</a>
        <a href="datatable2.php?<?php echo 'chn=' . $chn . '&date_start=' . $date_start_timestamp . '&date_stop=' . $date_stop_timestamp; ?>" target="_blank" class="w3-button w3-padding w3-round w3-blue" style="width:100px;font-size:16px;margin-top:8px;">原始數據</a>
    </div>
</div>
<script>
const x = [<?php echo $all_time; ?>];
const y = [<?php echo $all_status; ?>];
const s1 = [<?php echo $all_s1; ?>];
const s2 = [<?php echo $all_s2; ?>];
const s3 = [<?php echo $all_s3; ?>];
const s1n = [<?php echo $all_s1n; ?>];
const s2n = [<?php echo $all_s2n; ?>];
const s3n = [<?php echo $all_s3n; ?>];

const y_val = [<?php echo $all_val; ?>];
const y_val_unit = '<?php echo $chn_type_unit; ?>';

<?php
    $str_buf = "";
    if ($submit_flag == true)
    {
        $str_buf = $json_obj->{"chn_name"}->{$chn};
    }
?>
const chn_name = '<?php echo $str_buf; ?>';

var config =
{
    type: 'line',
    data:
    {
        labels: x,
        datasets:
        [
            {
                backgroundColor: ['rgb(75, 192, 192)'],
                borderColor: 'rgb(75, 192, 192)',
                label: chn_name,
                data: y,
                fill: false,
                tension: 0,
            },
<?php
    if ($chn_type_flag == 1 || $chn_type_flag == 2 || $chn_type_flag == 3)
    {
        
    }
    else
    {
        echo "/*";
    }
?>
            {
                backgroundColor: ['rgb(255, 255, 0)'],
                borderColor: 'rgb(255, 255, 0)',
                label: '2:預警',
                fill: false,
                data: s1,
                pointRadius: 0,
                pointHitRadius: 0,
                pointHoverRadius: 0,
                tension: 0,
                borderDash: [10, 5],
            },
            {
                backgroundColor: ['rgb(255, 165, 0)'],
                borderColor: 'rgb(255, 165, 0)',
                label: '3:警戒',
                fill: false,
                data: s2,
                pointRadius: 0,
                pointHitRadius: 0,
                pointHoverRadius: 0,
                tension: 0,
                borderDash: [10, 5],
            },
            {
                backgroundColor: ['rgb(255, 0, 0)'],
                borderColor: 'rgb(255, 0, 0)',
                label: '4:行動',
                fill: false,
                data: s3,
                pointRadius: 0,
                pointHitRadius: 0,
                pointHoverRadius: 0,
                tension: 0,
                borderDash: [10, 5],
            },
<?php
    if ($chn_type_flag == 1 || $chn_type_flag == 2 || $chn_type_flag == 3)
    {
        
    }
    else
    {
        echo "*/";
    }
?>
        ],
    },
    options:
    {
        
        
        tooltips:
        {
            callbacks:
            {
                label: function(tooltipItem, data)
                {
                    var label = data.datasets[tooltipItem.datasetIndex].label || '';
                    if (label)
                    {
                        label += ': ';
                    }
                    if (tooltipItem.yLabel == 1)
                    {
                        label += '正常 (' + y_val[tooltipItem.index].toFixed(2) + ' ' + y_val_unit + ')';
                    }
                    else if (tooltipItem.yLabel == 2)
                    {
                        label += '預警 (' + y_val[tooltipItem.index].toFixed(2) + ' ' + y_val_unit + ')';
                    }
                    else if (tooltipItem.yLabel == 3)
                    {
                        label += '警戒 (' + y_val[tooltipItem.index].toFixed(2) + ' ' + y_val_unit + ')';
                    }
                    else if (tooltipItem.yLabel == 4)
                    {
                        label += '行動 (' + y_val[tooltipItem.index].toFixed(2) + ' ' + y_val_unit + ')';
                    }
                    else
                    {
                        label += '未定義:' + tooltipItem.yLabel;
                    }
                    return label;
                }
            },
        },
        animation: false,
        responsive: true,
        maintainAspectRatio: false,
        title:
        {
            display: true,
            text: '<?php echo $date_start . " ~ " . $date_stop; ?>',
            fontSize: 16,
        },
        scales:
        {
            yAxes:
            [{
                ticks:
                {
                    min: 0,
                    max: 5,
                    maxTicksLimit: 12,
                    fontSize: 16,
                },
                scaleLabel:
                {
                    display: true,
                    labelString: '警報程度',
                    fontSize: 16,
                },
            }],
            xAxes:
            [{
                ticks:
                {
                    autoSkip: true,
                    maxTicksLimit: 12,
                    fontSize: 16,
                },
                type: 'time',
                scaleLabel:
                {
                    display: true,
                    labelString: '時間',
                    fontSize: 16,
                },
            }],
        },
        legend:
        {
            labels:
            {
                fontSize: 16,
                /*
                filter: function(item, chart)
                {
                    return item.text.includes(chn_name);
                },
                */
            },
        },
    },
};
var ctx = document.getElementById('history_chart').getContext('2d');
var myChart = new Chart(ctx, config);
</script>
<?php
    if ($submit_flag == false)
    {
        //echo "-->";
    }
    echo "-->";
?>


<?php
    if ($submit_flag == false)
    {
        echo "<!--";
    }
?>

<div class="w3-card-4" style="margin-top:20px;">
    <header class="w3-container w3-light-gray w3-padding-large w3-border">
        <svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
            <path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
        </svg>
        <span style="font-size:16px;margin-left:4px;">詳細資料</span>
    </header>
    <div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">

<table class="w3-table-all">
<tbody>
<tr><th style="vertical-align:middle;">時間</th><th style="vertical-align:middle;">警報狀態</th><th style="vertical-align:middle;"><?php echo $chn_type_unit; ?></th></tr>

<?php

    echo $alarm_type_table_str;
    
?>

</tbody>
</table>

        <a href="download2.php?<?php echo 'chn=' . $chn . '&date_start=' . $date_start_timestamp . '&date_stop=' . $date_stop_timestamp . '&alarm_type=' . $alarm_type; ?>" target="_blank" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">下載</a>
    </div>
</div>

<?php
    if ($submit_flag == false)
    {
        echo "-->";
    }
?>

		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
				Copyright © 基能科技股份有限公司
		</footer>
		
	</div>
	
	<script>
    	function w3_open()
    	{
    	  document.getElementById("mySidebar").style.display = "block";
    	  document.getElementById("myOverlay").style.display = "block";
    	}
    	function w3_close()
    	{
    	  document.getElementById("mySidebar").style.display = "none";
    	  document.getElementById("myOverlay").style.display = "none";
    	}
	</script>
	</body>
</html>
