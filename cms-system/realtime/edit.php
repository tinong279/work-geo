<?php
    
    $id = 0;
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
		
        $sql_query = "SELECT `status`, `type`, `text-content`, `text-content-img`, `start-time`, `stop-time`, `updated` FROM `02-cms-real-time` WHERE `id`=? LIMIT 1;";
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
            
            $stmt->bind_result($status, $type, $text_content, $text_content_img, $start_time, $stop_time, $updated);
            $cms_dataList2 = [];
            while ($stmt->fetch()) {
                $cms_dataList2[] = [
                    "status" => $status,
                    "type" => $type,
					"text_content" => $text_content,
					"text_content_img" => $text_content_img,
					"start_time" => $start_time,
					"stop_time" => $stop_time,
					"updated" => $updated
                ];
            }
    
            // print_r($cms_dataList);
    
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
                            $("#cms-preview").removeAttr("style").hide();
							$("#cms-send-setting").removeAttr("style").hide();
                        }
                    }
                );
            });
	
		var sys_msg = <?php echo $sys_msg; ?>;
		
		var cms_dataList = <?php echo json_encode($cms_dataList); ?>;
		// console.log(cms_dataList);
		
		var cms_dataList2 = <?php echo json_encode($cms_dataList2); ?>;
		// console.log(cms_dataList2);
		
		var text_content = <?php echo $cms_dataList2[0]['text_content']; ?>;
		// console.log(text_content);
		if (text_content.length == 0)
		{
			for (var i=0; i<18; i++)
			{
				text_content.push('');
			}
		}
		// console.log(text_content);
		
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
				let fileInput = document.getElementById("image");
				
				if (fileInput.files.length === 0) {
					alert("請選擇圖片！");
					return false;
				}
				
				// 添加圖片文件
				formData.append("image", fileInput.files[0]);
				
				var msg_data = '';

				msg_data += '[';
				/*
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
				*/
				msg_data += ']';
				
				document.getElementById('msg_text').value = msg_data
				
				//formData.append('msg', msg_data);
				//formData.append('type', '2');

				fetch('/api/04_cms-upload-img.php', {
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
		
		
	</script>
	<style>
	</style>
	<title>即時訊息發布設定</title>
	

</head>

<body>

	<div class="w3-top" style="z-index:15;">
		<div class="w3-bar w3-card" id="Navbar" class="" style="background-color:#606060;height:51px;">
			<div href="/" class="w3-wide" style="position:absolute;top:8px;left:10px;text-decoration:none;"><b style="font-size:24px;color:white;">即時訊息發布設定</b></div>
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
	
    <a href="index.php" class="w3-padding-small w3-bar-item w3-button"><img src="/img/stacks_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:4px;">狀態總覽</span></a>
	
	<!-- ====================================================================== -->
	
    <a href="edit.php?id=1" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-1</span></a>
	<a href="edit.php?id=2" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-2</span></a>
	<a href="edit.php?id=3" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-3</span></a>
	<a href="edit.php?id=4" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-4</span></a>
	<a href="edit.php?id=5" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-5</span></a>
	<a href="edit.php?id=6" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-6</span></a>
	<a href="edit.php?id=7" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-7</span></a>
	<a href="edit.php?id=8" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-8</span></a>
	<a href="edit.php?id=9" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-9</span></a>
	<a href="edit.php?id=10" class="w3-bar-item w3-button"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-10</span></a>
	
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

                <form action="" method="post" name="form" onSubmit="return checkForm()">
                    
                    <div class="w3-half">
					
                		<span class="w3-text-gray" style="font-size:16px;"><b>系統控制</b></span>

<select name="status" id="status" class="w3-select w3-border w3-round" style="font-size:16px;width:95%;">
<option value="0">停用</option>
<option value="1">自動</option>
<option value="2">手動</option>
</select>
<script>
document.getElementById('status').value = cms_dataList[0]['status'];

if (cms_dataList[0]['status'] == 2)
{
	
}

</script>
                    </div>
                    <div class="w3-half">
                		<span class="w3-text-gray" style="font-size:16px;"><b>發布時間設定</b></span>
<select name="time" id="time" class="w3-select w3-border w3-round" style="font-size:16px;width:95%;">
<option value="30">30 分鐘</option>
<option value="60">1 小時</option>
<option value="120">2 小時</option>
<option value="180">3 小時</option>
<option value="240">4 小時</option>
<option value="300">5 小時</option>
<option value="360">6 小時</option>
</select>
                    </div>


<div class="w3-col w3-padding">
    選擇圖片：
    <input type="file" id="image" name="image">
</div>

<!--
<div class="w3-half">

					
                    <div class="w3-quarter">
                		<span class="w3-text-gray" style="font-size:16px;"><b>第一行</b></span>
<input name="c1r1" id="c1r1" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r2" id="c1r2" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r3" id="c1r3" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r4" id="c1r4" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r5" id="c1r5" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r6" id="c1r6" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r7" id="c1r7" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r8" id="c1r8" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c1r9" id="c1r9" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
                    </div>
                    <div class="w3-quarter">
                		<span class="w3-text-gray" style="font-size:16px;"><b>第二行</b></span>
<input name="c2r1" id="c2r1" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r2" id="c2r2" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r3" id="c2r3" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r4" id="c2r4" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r5" id="c2r5" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r6" id="c2r6" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r7" id="c2r7" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r8" id="c2r8" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
<input name="c2r9" id="c2r9" class="w3-input w3-border w3-round" type="text" maxlength="4" style="font-size:16px;width:60px;text-align: center;">
                    </div>
</div>

<script>
document.getElementById('c1r1').value = text_content[0];
document.getElementById('c1r2').value = text_content[1];
document.getElementById('c1r3').value = text_content[2];
document.getElementById('c1r4').value = text_content[3];
document.getElementById('c1r5').value = text_content[4];
document.getElementById('c1r6').value = text_content[5];
document.getElementById('c1r7').value = text_content[6];
document.getElementById('c1r8').value = text_content[7];
document.getElementById('c1r9').value = text_content[8];

document.getElementById('c2r1').value = text_content[9];
document.getElementById('c2r2').value = text_content[10];
document.getElementById('c2r3').value = text_content[11];
document.getElementById('c2r4').value = text_content[12];
document.getElementById('c2r5').value = text_content[13];
document.getElementById('c2r6').value = text_content[14];
document.getElementById('c2r7').value = text_content[15];
document.getElementById('c2r8').value = text_content[16];
document.getElementById('c2r9').value = text_content[17];
</script>
-->



					
                    <div class="w3-col">
                		<button type="submit" name="submit" value="submit" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">送出</button>
                		<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b></b></span>
                    </div>
                    
                </form>
                
			</div>

		</div>





		<!--
		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
		
		</footer>
		-->
	</div>

    <div class="w3-padding-32"></div>

	<!-- ====================================================================== -->
	<div id="cms-send-setting" class="w3-modal">
		<div class="w3-modal-content">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:18px; vertical-align:middle;">變更控制</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('cms-send-setting').style.display='none';return false;">&times;</a>
			</div>
			<div class="w3-content w3-padding">

<form action="add-task.php" method="post" style="font-size:16px;">

<div id="set_status_desc" class="w3-center" style="font-size:24px;"></div>

<input type="hidden" id="set_status1" name="status" value="0">
<input type="hidden" name="type" value="0">
<input type="hidden" name="msg" value="">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" id="time1" name="time" value="0">

<hr>

<div class="w3-center">
<button class="w3-red w3-btn" style="width:120px;margin-bottom:12px;">確定變更</button>
<button class="w3-blue w3-btn" style="width:120px;margin-bottom:12px;" onclick="document.getElementById('cms-send-setting').style.display='none';return false;">取消</button>
</div>
</form>

			</div>
		</div>
	</div>
	<!-- ====================================================================== -->
	<div id="cms-preview" class="w3-modal">
		<div class="w3-modal-content">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:18px; vertical-align:middle;">CMS預覽</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('cms-preview').style.display='none';return false;">&times;</a>
			</div>
			<div class="w3-content w3-padding">
			<div class="w3-center">
			<img id="cms-preview-img" src=""/>
			</div>
<form action="add-task.php" method="post" style="font-size:16px;">


<input type="hidden" id="set_status2" name="status" value="0">
<input type="hidden" name="type" value="2">
<input type="hidden" id="set_msg" name="msg" value="0">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" id="time2" name="time" value="0">
<input type="hidden" id="msg_text" name="msg_text" value="">

<hr>

<div class="w3-center">
<button class="w3-red w3-btn" style="width:120px;margin-bottom:12px;">確定變更</button>
<button class="w3-blue w3-btn" style="width:120px;margin-bottom:12px;" onclick="document.getElementById('cms-preview').style.display='none';return false;">取消</button>
</div>
</form>

			</div>
		</div>
	</div>
	<!-- ====================================================================== -->
	<div id="sys-msg" class="w3-modal">
		<div class="w3-modal-content">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:18px; vertical-align:middle;">系統訊息</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('sys-msg').style.display='none';return false;">&times;</a>
			</div>
			<div class="w3-content w3-padding">

<?php
	if ($sys_msg == 1)
	{
		echo '<div id="set_status_desc" class="w3-center" style="font-size:24px;">變更成功</div>';
	}
?>

<div class="w3-center">
<button class="w3-blue w3-btn" style="width:120px;margin-bottom:12px;" onclick="document.getElementById('sys-msg').style.display='none';return false;">確定</button>
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
		
<?php
	if ($sys_msg == 1)
	{
		echo "document.getElementById('sys-msg').style.display='block';";
	}
?>

    </script>
</body>
</html>
