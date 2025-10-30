<?php
    
    $id = 1;
    $cms_dataList = [];
	$cms_dataList2 = [];
	$sys_msg = 0;
    //========================================
    if (isset($_GET['id']))
    {
        $id = intval($_GET['id']);
    }
    if (isset($_GET['sys_msg']))
    {
        $sys_msg = intval($_GET['sys_msg']);
    }
    //========================================
    require("../ConnMySQL.php");
    
    if ($db_link == TRUE) {
        $db_link->query("SET NAMES \"utf8\"");
    
        $sql_query = "SELECT `id`, `status`, `info`, `last-ping-echo-time`, `lat`, `lon` FROM `01-cms-status` WHERE `id`=? LIMIT 1";
        $stmt = $db_link->prepare($sql_query);
    
        if ($stmt == true) {
            $stmt->bind_param("i", $id); // 如果需要傳參數，取消註解並調整參數
            $stmt->execute();
    
            // 取代 get_result()，使用 bind_result() 獲取結果
            $stmt->store_result(); // 儲存結果集
            
            if ($stmt->num_rows == 0)
            {
                exit();
            }
            
            $stmt->bind_result($id, $status, $info, $last_ping_echo_time, $lat, $lon);
            $cms_dataList = [];
            while ($stmt->fetch()) {
                $cms_dataList[] = [
                    "id" => $id,
                    "status" => $status,
                    "info" => $info,
                    "last-ping-echo-time" => $last_ping_echo_time,
                    "lat" => $lat,
                    "lon" => $lon
                ];
            }
    
            // print_r($cms_dataList);
    
            $stmt->close();
        }
		
        $sql_query = "SELECT `sn`, `name`, `week`, `hour_start`, `hour_stop`, `user` FROM `03-cms-schedule` WHERE `status`=1 AND `id`=? ORDER BY `week` ASC, `hour_start` ASC;";
        $stmt = $db_link->prepare($sql_query);
    
        if ($stmt == true) {
            $stmt->bind_param("i", $id); // 如果需要傳參數，取消註解並調整參數
            $stmt->execute();
    
            // 取代 get_result()，使用 bind_result() 獲取結果
            $stmt->store_result(); // 儲存結果集
            
            if ($stmt->num_rows == 0)
            {
                // exit();
            }
            else
			{
				$stmt->bind_result($sn, $name, $week, $hour_start, $hour_stop, $user);
				$cms_dataList2 = [];
				while ($stmt->fetch()) {
					$cms_dataList2[] = [
						"sn" => $sn,
						"name" => $name,
						"week" => $week,
						"hour_start" => $hour_start,
						"hour_stop" => $hour_stop,
						"user" => $user
					];
				}
		
				// print_r($cms_dataList);
			}

    
            $stmt->close();
        }
		
		
        $db_link->close();
    }
    
    
?>

<!DOCTYPE html>
<html lang="zh-tw">

<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link rel="stylesheet" href="/css/w3-4.15.css"/>
	<script src="/js/jquery-3.6.0.js"></script>
	<script>
            $(function(){
                $(window).keydown(
                    function (event)
                    {
                        if (event.keyCode == 27)
                        {
                            $("#task_add_ui").removeAttr("style").hide();
                        }
                    }
                );
            });
	
		var sys_msg = <?php echo $sys_msg; ?>;
		
		var cms_dataList = <?php echo json_encode($cms_dataList); ?>;
		// console.log(cms_dataList);
		
		var delete_sn = 0;
		
		function checkForm()
		{
			var status = document.getElementById('status').value;
			var time = document.getElementById('time').value;
			
			if (status == 0)
			{
				document.getElementById('set_status1').value = status;
				document.getElementById('time1').value = time;
				document.getElementById('set_status_desc').innerHTML  = '將CMS控制變更為<br>「停用」';
				document.getElementById('cms-send-setting').style.display='block';
			}
			else if(status == 1)
			{
				document.getElementById('set_status1').value = status;
				document.getElementById('time1').value = time;
				document.getElementById('set_status_desc').innerHTML  = '將CMS控制變更為<br>「自動」';
				document.getElementById('cms-send-setting').style.display='block';
			}
			else if(status == 2)
			{
				document.getElementById('set_status2').value = status;
				document.getElementById('time2').value = time;
				const imgElement = document.getElementById('cms-preview-img');
				imgElement.src = '';

				// 建立 FormData 資料
				const formData = new FormData();

				var msg_data = '';

				msg_data += '[';
				msg_data += '"' + document.getElementById('c1r1').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r2').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r3').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r4').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r5').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r6').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r7').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r8').value + '"' + ',';
				msg_data += '"' + document.getElementById('c1r9').value + '"' + ',';

				msg_data += '"' + document.getElementById('c2r1').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r2').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r3').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r4').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r5').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r6').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r7').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r8').value + '"' + ',';
				msg_data += '"' + document.getElementById('c2r9').value + '"' + '';
				msg_data += ']';
				
				document.getElementById('msg_text').value = msg_data
				
				formData.append('msg', msg_data);
				formData.append('type', '2');

				fetch('/api/02_cms-text-to-bmp.php', {
				method: 'POST',
				body: formData // 傳送 FormData 資料
				})
				.then(response => {
				if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json(); // 將伺服器返回的資料解析為 JSON
				})
				.then(data => {
				console.log('接收到的 JSON 資料:', data);


				imgElement.src = data['url'];
				document.getElementById('set_msg').value = data['url'];
				
				})
				.catch(error => {
				console.error('發生錯誤:', error);
				});
				
				document.getElementById('cms-preview').style.display='block';
			}
			return false;
		}
        function show_delete_ui(sn)
        {
			delete_sn = sn;
			
			document.getElementById('cms-preview').style.display='block';
        }
		
        function send_delete_req()
        {
			document.getElementById('cms-preview').style.display='none';
			
			// 建立 FormData 資料
			const formData = new FormData();

			formData.append('sn', delete_sn);

			fetch('delete.php', {
			method: 'POST',
			body: formData // 傳送 FormData 資料
			})
			.then(response => {
			if (!response.ok) {
			throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json(); // 將伺服器返回的資料解析為 JSON
			})
			.then(data => {
			console.log('接收到的 JSON 資料:', data);
			
			if (data['sys_flag'] == 1)
			{
				alert(data['sys_msg']);
				location.reload();
			}
			else
			{
				alert(data['sys_msg']);
			}
			
			})
			.catch(error => {
			console.error('發生錯誤:', error);
			});
			
        }
	</script>
	<style>
	</style>
	<title>排程訊息發布設定</title>
	

</head>

<body>

	<div class="w3-top" style="z-index:15;">
		<div class="w3-bar w3-card" id="Navbar" class="" style="background-color:#606060;height:51px;">
			<div href="/" class="w3-wide" style="position:absolute;top:8px;left:10px;text-decoration:none;"><b style="font-size:24px;color:white;">排程訊息發布設定</b></div>
			<div class="w3-right" style="font-size:16px;">
				<!--
				<a href="/" class="w3-bar-item w3-button w3-hide-small w3-hide-medium"><span style="color:white;"><b>AAA</b></span></a>
				-->
			</div>
		</div>
	</div>

<div style="padding-top:51px;"></div>

<div class="w3-sidebar w3-bar-block w3-card w3-animate-left w3-light-gray" style="display:block;left:0px;top:51px;z-index:16;font-size:14px;border:1px solid gray;width:160px;" id="Sidebar">
	<div style="height:10px;"></div>
	
	<!-- ====================================================================== -->
	
	
	
	<!-- ====================================================================== -->
	
    <a href="index.php?id=1" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-1</span></a>
	<a href="index.php?id=2" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-2</span></a>
	<a href="index.php?id=3" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-3</span></a>
	<a href="index.php?id=4" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-4</span></a>
	<a href="index.php?id=5" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-5</span></a>
	<a href="index.php?id=6" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-6</span></a>
	<a href="index.php?id=7" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-7</span></a>
	<a href="index.php?id=8" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-8</span></a>
	<a href="index.php?id=9" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-9</span></a>
	<a href="index.php?id=10" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-10</span></a>
	
	<!-- ====================================================================== -->
    <div class="w3-padding-32"></div>

</div>


	<div class="w3-container w3-padding-large" style="margin-left:160px;">
	
	<h1>
<?php
    $str_buf = 'CMS-';
    $str_buf .= $id . ' ';
    $str_buf .= $cms_dataList[0]['info'] . ' ';
    
    echo $str_buf;
?>
	</h1>

<div class="w3-card-4" style="margin-top:10px;">
	<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
		<div>
			<a href="add-task.php?id=<?php echo $id; ?>" class="w3-button w3-padding w3-round w3-blue" style="width:100px;font-size:16px;">新增</a>
			<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b></b></span>
		</div>
	</div>
</div>

<div class="w3-card-4" style="margin-top:20px;">
<header class="w3-container w3-light-gray w3-padding-large w3-border">
<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
</svg>
<span style="font-size:16px;margin-left:4px;">排程清單</span>
</header>
<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
<table class="w3-table-all">
<tbody>
<tr>
<th style="text-align:center;vertical-align:middle;">名稱</th>
<th style="text-align:center;vertical-align:middle;">星期</th>
<th style="text-align:center;vertical-align:middle;">開始時間</th>
<th style="text-align:center;vertical-align:middle;">結束時間</th>
<th style="text-align:center;vertical-align:middle;width:120px;">功能</th>
</tr>

<?php
	
	$weekly = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
	
	$str_buf1 = '';
	
	for ($i=0; $i<count($cms_dataList2); $i++)
	{
		$arr_buf1 = $cms_dataList2[$i];
		
		$str_buf1 .= '<tr>' . "\n";
		$str_buf1 .= '<td style="text-align:center;vertical-align:middle;">' . $arr_buf1['name'] . '</td>' . "\n";
		$str_buf1 .= '<td style="text-align:center;vertical-align:middle;">' . $weekly[$arr_buf1['week']] . '</td>' . "\n";
		$str_buf1 .= '<td style="text-align:center;vertical-align:middle;">' . str_pad($arr_buf1['hour_start'], 2, "0", STR_PAD_LEFT) . ' 點</td>' . "\n";
		$str_buf1 .= '<td style="text-align:center;vertical-align:middle;">' . str_pad($arr_buf1['hour_stop'], 2, "0", STR_PAD_LEFT) . ' 點</td>' . "\n";

		//$str_buf1 .= '<td style="text-align:center;vertical-align:middle;">' . $arr_buf1['hour_start'] . ' 點</td>' . "\n";
		//$str_buf1 .= '<td style="text-align:center;vertical-align:middle;">' . $arr_buf1['hour_stop'] . ' 點</td>' . "\n";
		
		$str_buf1 .= '
<td style="text-align:center;vertical-align:middle;">
	<button onclick="show_delete_ui(' . $arr_buf1['sn'] . ');" class="w3-button w3-round w3-red" style="width:60px;font-size:12px;margin-left:2px;margin-right:2px;">刪除</button>
</td>
';
					
		$str_buf1 .= '</tr>' . "\n";
		
		
		
	}
	
	echo $str_buf1;
	
?>

</tbody>
</table>
</div>
</div>







		<!--
		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
		
		</footer>
		-->
	</div>

    <div class="w3-padding-32"></div>

	<!-- ====================================================================== -->
	<div id="cms-preview" class="w3-modal">
		<div class="w3-modal-content" >
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:18px; vertical-align:middle;">刪除排程</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('cms-preview').style.display='none';return false;">&times;</a>
			</div>
			<div class="w3-content w3-padding">
			<div class="w3-center">
			
			</div>



<hr>
<div class="w3-center">
<button onclick="send_delete_req();" class="w3-red w3-btn" style="width:120px;margin-bottom:12px;">確定</button>
<button class="w3-blue w3-btn" style="width:120px;margin-bottom:12px;" onclick="document.getElementById('cms-preview').style.display='none';return false;">取消</button>
</div>

			</div>
		</div>
	</div>
	<!-- ====================================================================== -->

    <script>
        var Sidebar = document.getElementById("Sidebar");
        function w3_open()
        {
        	if (Sidebar.style.display === "block")
        	{
        		Sidebar.style.display = "none";
        	}
        	else
        	{
        		Sidebar.style.display = "block";
        	}
        }
        function w3_close()
        {
        	Sidebar.style.display = "none";
        }
    </script>
</body>
</html>
