<?php
    
	$chn_list = [];
	$chn_list[] = '苗62鄉道 2.1K邊坡';
	$chn_list[] = '苗62鄉道 6.2K邊坡';
	$chn_list[] = '苗縣道126 23.1K';
	$chn_list[] = '苗縣道126 28.5K';
	
    function get_chn_option($chn)
    {
		global $chn_list;
		
		$res = '';
		
        for($i=0; $i<count($chn_list); $i++)
        {
			if ($chn == ($i+1))
			{
				$res .= '<option value="' . ($i+1) . '" selected="selected">' . $chn_list[$i] . '</option>';
			}
			else
			{
				$res .= '<option value="' . ($i+1) . '">' . $chn_list[$i] . '</option>';
			}
        }
		
		return $res;
    }
	
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
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
    
    $submit_flag = false;
    $json_obj = '';
    $chn_type_flag = 0;
    
    $err_msg = "";
    
	$html_table = '';
	$excel_txt = '';
	
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
        
		$site_id_1 = $chn . '-1';
		$site_id_2 = $chn . '-2';
		
        $date_start = $_POST["date_start"];
        $date_start_timestamp = strtotime($date_start);
        $date_start = date('Y-m-d H:i', $date_start_timestamp);
        
        $date_stop = $_POST["date_stop"];
        $date_stop_timestamp = strtotime($date_stop);
        $date_stop = date('Y-m-d H:i', $date_stop_timestamp);
        
$excel_txt .= '
<!DOCTYPE html>
<html lang="zh-tw">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<table>
<tr><td>地點</td><td>' . $chn_list[$chn-1] . '</td></tr>
<tr><td>開始時間</td><td>' . $date_start . '</td></tr>
<tr><td>結束時間</td><td>' . $date_stop . '</td></tr>
</table>
';
		
        require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
		if ($db_link == TRUE)
		{
			$db_link->query("SET NAMES \"utf8\"");
			
			$sql_query = "SELECT `sn`, `time`, `id`, `msg`, `path`, `site_id` FROM `line-notify-message-list` WHERE `status`=1 AND (`site_id`=? OR `site_id`=?) AND time>=? AND time<=? ORDER BY `sn` DESC";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == TRUE)
			{
				$stmt->bind_param("ssss", $site_id_1, $site_id_2, $date_start, $date_stop);
				$stmt->execute();
				$stmt->store_result();
				$data_count = $stmt->num_rows;
				if ($data_count > 0)
				{
					$stmt->bind_result
					(
				        $data_buf['sn'],
						$data_buf['time'],
						$data_buf['id'],
						$data_buf['msg'],
						$data_buf['path'],
						$data_buf['site_id']
				    );
				    
				    $count_buf = 0;
$str_buf1 = "
<table class='w3-table-all'>
  <tr>
    <th>時間</th>
    <th>描述</th>
	<th>照片</th>
  </tr>
";
$html_table .= $str_buf1;
$excel_txt .= $str_buf1;

				    while ($stmt->fetch())
				    {
$str_buf1 = '
  <tr>
    <td>' . $data_buf['time'] . '</td>
    <td>' . $data_buf['msg'] . '</td>
	<td><a target="_blank" href="/manager-page/' . $data_buf['path'] . '">通報照片</a></td>
  </tr>
';
$str_buf2 = '
  <tr>
    <td>' . $data_buf['time'] . '</td>
    <td>' . $data_buf['msg'] . '</td>
	<td><a target="_blank" href="http://210.71.231.250/miaoli62-line-notify/' . $data_buf['path'] . '">通報照片</a></td>
  </tr>
';
$html_table .= $str_buf1;
$excel_txt .= $str_buf2;
				    }
$str_buf1 = '
</table>
';
$html_table .= $str_buf1;
$excel_txt .= $str_buf1;

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
		
$excel_txt .= '
</body>
</html>
';
$excel_txt = base64_encode($excel_txt);
    }
    
?>
<!DOCTYPE html>
<html lang="zh-tw">
	<head>
		<title>時間區間查詢 - <?php echo $system_name; ?></title>
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
	
		<h1>時間區間查詢</h1>
		
		<div class="w3-card-4" style="margin-top:10px;">

			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">

                <form action="" method="post" name="form" id="form" onsubmit="">
                    <div class="w3-third">
                		<span class="w3-text-gray" style="font-size:16px;"><b>地點　　　　　　</b></span>
                		<select name="chn" id="chn" class="w3-select w3-border w3-round" style="font-size:16px;width:95%;">

<?php
    if ($submit_flag == false)
    {
        echo get_chn_option(1);
    }
    else
    {
        echo get_chn_option($chn);
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
                		<span class="w3-text-gray" style="font-size:16px;"><b>開始時間　　　　</b></span>
                		<input type="datetime-local" name="date_start" id="date_start" class="w3-input w3-border w3-round" style="font-size:16px;width:95%;" value="<?php echo $date_start3; ?>">
                    </div>
                    <div class="w3-third">
                		<span class="w3-text-gray" style="font-size:16px;"><b>結束時間　　　　</b></span>
                		<input type="datetime-local" name="date_stop" id="date_stop" class="w3-input w3-border w3-round" style="font-size:16px;width:95%;" value="<?php echo $date_stop3; ?>">
                    </div>
                    <div>
                		<button type="submit" name="submit" value="submit" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">查詢</button>
                		<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b><?php echo $err_msg; ?></b></span>
                    </div>
                </form>
                
			</div>

		</div>
		
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
        <div style="position:relative;width:100%;">

<?php echo $html_table; ?>

        </div>
		
<div onclick="downloadExcel()" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">下載</div>
<script>
<?php echo "var excel_txt = '" . $excel_txt . "';\n"; ?>
	function decodeBase64Utf8(base64String) {
	  // Decode the Base64 string to binary data
	  const binaryString = atob(base64String);

	  // Convert the binary data to a UTF-8 string
	  const bytes = new Uint8Array(binaryString.length);
	  for (let i = 0; i < binaryString.length; i++) {
		bytes[i] = binaryString.charCodeAt(i);
	  }
	  const decoder = new TextDecoder('utf-8');
	  return decoder.decode(bytes);
	}

	function downloadExcel() {
		// 創建一個Blob對象，並指定內容和類型
		const blob = new Blob([decodeBase64Utf8(excel_txt)], { type: 'text/plain' });

		// 創建一個鏈接元素
		const a = document.createElement('a');
		a.href = URL.createObjectURL(blob);
		a.download = 'data.xls';

		// 模擬點擊鏈接，觸發下載
		document.body.appendChild(a);
		a.click();

		// 下載後清理DOM和URL
		document.body.removeChild(a);
		URL.revokeObjectURL(a.href);
	}
</script>

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
