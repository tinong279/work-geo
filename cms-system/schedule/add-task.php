<?php
    
    $id = 0;
    $cms_dataList = [];
	$cms_dataList2 = [];
	//========================================
	$sys_flag = 0;
	$sys_msg = '';
    //========================================
    if (isset($_GET['id']))
    {
        $id = intval($_GET['id']);
    }
    if (isset($_GET['sys_flag']))
    {
        $sys_flag = intval($_GET['sys_flag']);
    }
    if (isset($_GET['sys_msg']))
    {
        $sys_msg = $_GET['sys_msg'];
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
	
		var cms_dataList = <?php echo json_encode($cms_dataList); ?>;
		// console.log(cms_dataList);
		
		function checkForm()
		{
			document.getElementById('s_name').value = document.getElementById('name').value;
			document.getElementById('s_week').value = document.getElementById('week').value;
			document.getElementById('s_start').value = document.getElementById('start').value;
			document.getElementById('s_stop').value = document.getElementById('stop').value;
			
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
			
			document.getElementById('s_text').value = msg_data
			
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
			document.getElementById('s_img').value = data['url'];
			
			})
			.catch(error => {
			console.error('發生錯誤:', error);
			});
			
			document.getElementById('cms-preview').style.display='block';
			
			return false;
		}
		
		function checkForm2()
		{
			// 建立 FormData 資料
			const formData = new FormData();

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
			
			formData.append('id', <?php echo $id; ?>);
			formData.append('name', document.getElementById('name').value);
			formData.append('week', document.getElementById('week').value);
			formData.append('start', document.getElementById('start').value);
			formData.append('stop', document.getElementById('stop').value);
			
			formData.append('img', document.getElementById('s_img').value);
			formData.append('text', msg_data);

			fetch('add.php', {
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
				window.location.href = 'index.php?id=<?php echo $id; ?>';
			}
			else
			{
				alert(data['sys_msg']);
			}
			
			})
			.catch(error => {
			console.error('發生錯誤:', error);
			});
			
			document.getElementById('cms-preview').style.display='none';
			
			return false;
		}
		
		function checkForm3()
		{
			document.getElementById('s_name').value = document.getElementById('name').value;
			document.getElementById('s_week').value = document.getElementById('week').value;
			document.getElementById('s_start').value = document.getElementById('start').value;
			document.getElementById('s_stop').value = document.getElementById('stop').value;
			
			const imgElement = document.getElementById('cms-preview-img');
			imgElement.src = '';
			//----------------------------------------------------
            let formData = new FormData();
            let fileInput = document.getElementById("image");
            // let title = document.getElementById("title").value;
            // let description = document.getElementById("description").value;

			// alert(fileInput.files.length);
            if (fileInput.files.length === 0) {
                alert("請選擇圖片！");
                return false;
            }

            // 添加文本參數
            // formData.append("title", title);
            // formData.append("description", description);
            
            // 添加圖片文件
            formData.append("image", fileInput.files[0]);

            // 發送 AJAX 請求
            fetch("/api/04_cms-upload-img.php", {
                method: "POST",
                body: formData
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
				document.getElementById('s_img').value = data['url'];
            })
            .catch(error => console.error("錯誤：", error));
			
			document.getElementById('cms-preview').style.display='block';
			//----------------------------------------------------
		}
		
	</script>
	<style>
	</style>
	<title>新增排程</title>
	

</head>

<body>

	<div class="w3-top" style="z-index:15;">
		<div class="w3-bar w3-card" id="Navbar" class="" style="background-color:#606060;height:51px;">
			<div href="/" class="w3-wide" style="position:absolute;top:8px;left:10px;text-decoration:none;"><b style="font-size:24px;color:white;">新增排程</b></div>
			<div class="w3-right" style="font-size:16px;">
				<!--
				<a href="/" class="w3-bar-item w3-button w3-hide-small w3-hide-medium"><span style="color:white;"><b>AAA</b></span></a>
				-->
			</div>
		</div>
	</div>

<div style="padding-top:51px;"></div>




	<div class="w3-container w3-padding-large" style="margin-left:0px;">
	
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


<!--
                <form action="" method="post" name="form" onsubmit="return checkForm()">
                    
<div>
<span class="w3-text-gray" style="font-size:16px;"><b>排程名稱</b></span>
<input name="name" id="name" class="w3-input w3-border w3-round" type="text" maxlength="32" style="font-size:16px;">
</div>

<div class="w3-rest">

<div class="w3-third">
<span class="w3-text-gray" style="font-size:16px;width:100%;"><b>每週</b></span>
<select name="week" id="week" class="w3-select w3-border w3-round" style="font-size:16px;">
<option value="0">星期日</option>
<option value="1">星期一</option>
<option value="2">星期二</option>
<option value="3">星期三</option>
<option value="4">星期四</option>
<option value="5">星期五</option>
<option value="6">星期六</option>
</select>
</div>

<div class="w3-third">
<span class="w3-text-gray" style="font-size:16px;width:100%;"><b>開始時間</b></span>
<select name="start" id="start" class="w3-select w3-border w3-round" style="font-size:16px;">
<option value="0">00:00</option>
<option value="1">01:00</option>
<option value="2">02:00</option>
<option value="3">03:00</option>
<option value="4">04:00</option>
<option value="5">05:00</option>
<option value="6">06:00</option>
<option value="7">07:00</option>
<option value="8">08:00</option>
<option value="9">09:00</option>
<option value="10">10:00</option>
<option value="11">11:00</option>
<option value="12">12:00</option>
<option value="13">13:00</option>
<option value="14">14:00</option>
<option value="15">15:00</option>
<option value="16">16:00</option>
<option value="17">17:00</option>
<option value="18">18:00</option>
<option value="19">19:00</option>
<option value="20">20:00</option>
<option value="21">21:00</option>
<option value="22">22:00</option>
<option value="23">23:00</option>
</select>
</div>

<div class="w3-third">
<span class="w3-text-gray" style="font-size:16px;width:100%;"><b>結束時間</b></span>
<select name="stop" id="stop" class="w3-select w3-border w3-round" style="font-size:16px;">
<option value="1">01:00</option>
<option value="2">02:00</option>
<option value="3">03:00</option>
<option value="4">04:00</option>
<option value="5">05:00</option>
<option value="6">06:00</option>
<option value="7">07:00</option>
<option value="8">08:00</option>
<option value="9">09:00</option>
<option value="10">10:00</option>
<option value="11">11:00</option>
<option value="12">12:00</option>
<option value="13">13:00</option>
<option value="14">14:00</option>
<option value="15">15:00</option>
<option value="16">16:00</option>
<option value="17">17:00</option>
<option value="18">18:00</option>
<option value="19">19:00</option>
<option value="20">20:00</option>
<option value="21">21:00</option>
<option value="22">22:00</option>
<option value="23">23:00</option>
<option value="24">24:00</option>
</select>
</div>

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

</script>



                    <div class="w3-col">
                		<button type="submit" name="submit" value="submit" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">確認</button>
                		<a href="index.php?id=<?php echo $id; ?>" class="w3-button w3-padding w3-round w3-orange" style="width:80px;font-size:16px;margin-top:8px;">取消</a>
                		<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b></b></span>
                    </div>



                </form>
-->


                <div id="uploadForm">
                    
<div>
<span class="w3-text-gray" style="font-size:16px;"><b>排程名稱</b></span>
<input name="name" id="name" class="w3-input w3-border w3-round" type="text" maxlength="32" style="font-size:16px;">
</div>

<div class="w3-rest">

<div class="w3-third">
<span class="w3-text-gray" style="font-size:16px;width:100%;"><b>每週</b></span>
<select name="week" id="week" class="w3-select w3-border w3-round" style="font-size:16px;">
<option value="0">星期日</option>
<option value="1">星期一</option>
<option value="2">星期二</option>
<option value="3">星期三</option>
<option value="4">星期四</option>
<option value="5">星期五</option>
<option value="6">星期六</option>
</select>
</div>

<div class="w3-third">
<span class="w3-text-gray" style="font-size:16px;width:100%;"><b>開始時間</b></span>
<select name="start" id="start" class="w3-select w3-border w3-round" style="font-size:16px;">
<option value="0">00:00</option>
<option value="1">01:00</option>
<option value="2">02:00</option>
<option value="3">03:00</option>
<option value="4">04:00</option>
<option value="5">05:00</option>
<option value="6">06:00</option>
<option value="7">07:00</option>
<option value="8">08:00</option>
<option value="9">09:00</option>
<option value="10">10:00</option>
<option value="11">11:00</option>
<option value="12">12:00</option>
<option value="13">13:00</option>
<option value="14">14:00</option>
<option value="15">15:00</option>
<option value="16">16:00</option>
<option value="17">17:00</option>
<option value="18">18:00</option>
<option value="19">19:00</option>
<option value="20">20:00</option>
<option value="21">21:00</option>
<option value="22">22:00</option>
<option value="23">23:00</option>
</select>
</div>

<div class="w3-third">
<span class="w3-text-gray" style="font-size:16px;width:100%;"><b>結束時間</b></span>
<select name="stop" id="stop" class="w3-select w3-border w3-round" style="font-size:16px;">
<option value="1">01:00</option>
<option value="2">02:00</option>
<option value="3">03:00</option>
<option value="4">04:00</option>
<option value="5">05:00</option>
<option value="6">06:00</option>
<option value="7">07:00</option>
<option value="8">08:00</option>
<option value="9">09:00</option>
<option value="10">10:00</option>
<option value="11">11:00</option>
<option value="12">12:00</option>
<option value="13">13:00</option>
<option value="14">14:00</option>
<option value="15">15:00</option>
<option value="16">16:00</option>
<option value="17">17:00</option>
<option value="18">18:00</option>
<option value="19">19:00</option>
<option value="20">20:00</option>
<option value="21">21:00</option>
<option value="22">22:00</option>
<option value="23">23:00</option>
<option value="24">24:00</option>
</select>
</div>

<div class="w3-col w3-padding">
    選擇圖片：
    <input type="file" id="image" name="image" required>
</div>

                    <div class="w3-col">
                		<button onclick="checkForm3()" class="w3-button w3-padding w3-round w3-blue" style="width:80px;font-size:16px;margin-top:8px;">確認</button>
                		<a href="index.php?id=<?php echo $id; ?>" class="w3-button w3-padding w3-round w3-orange" style="width:80px;font-size:16px;margin-top:8px;">取消</a>
                		<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b></b></span>
                    </div>



                </div>


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
		<div class="w3-modal-content">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:18px; vertical-align:middle;">CMS預覽</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('cms-preview').style.display='none';return false;">&times;</a>
			</div>
			<div class="w3-content w3-padding">
			<div class="w3-center">
			<img id="cms-preview-img" src=""/>
			</div>
<form action="add.php" method="post" style="font-size:16px;" onsubmit="return checkForm2()">

<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" id="s_name" name="name" value="">
<input type="hidden" id="s_week" name="week" value="">
<input type="hidden" id="s_start" name="start" value="">
<input type="hidden" id="s_stop" name="stop" value="">
<input type="hidden" id="s_img" name="img" value="">
<input type="hidden" id="s_text" name="text" value="">

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
		


    </script>
</body>
</html>
