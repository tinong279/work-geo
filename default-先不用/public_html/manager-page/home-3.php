<?php
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    require("C:\\xampp\\htdocs\\default\\home-func-3.php");
    require("C:\\xampp\\htdocs\\default\\public-func.php");
?>
<!DOCTYPE html>
<html lang="zh-tw">
	<head>
		<title>苗縣道126 23.5K邊坡 即時監測數據 - <?php echo $system_name; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

<?php get_css_js_link() ?>

        <script language="javascript">
            $(function(){
                $(window).keydown(
                    function (event)
                    {
                        if (event.keyCode == 27)
                        {
                            $("#locpic_01").removeAttr("style").hide();
							cms_control_ui_close();
							document.getElementById('cms_response').style.display='none';
                        }
                    }
                );
            });
			
		function cms_control_ui_close()
		{
			$('#cms_set_response').html("");
			document.getElementById('cms_control').style.display='none';
		}
		
		function cms_on()
		{
			cms_control_ui_close();
			
			document.getElementById('cms_connecting').style.display='block';
			$('#cms_connecting_str').html("連線中");
			
			$.get("api/cms-on3.php?token1=<?php echo $_SESSION['token1']; ?>", function(data, status)
			{
				document.getElementById('cms_connecting').style.display='none';
				document.getElementById('cms_response').style.display='block';
				
				if (status == "success" && data == "ok")
				{
					$('#cms_set_response').html("開啟成功");
					$("#cms_status_ui").attr("src", "img/on1.png");
				}
				else
				{
					$('#cms_set_response').html("連線異常");
					$("#cms_status_ui").attr("src", "img/off1.png");
				}
			});
		}
		
		function cms_off()
		{
			cms_control_ui_close();
			
			document.getElementById('cms_connecting').style.display='block';
			$('#cms_connecting_str').html("連線中");
			
			$.get("api/cms-off3.php?token1=<?php echo $_SESSION['token1']; ?>", function(data, status)
			{
				document.getElementById('cms_connecting').style.display='none';
				document.getElementById('cms_response').style.display='block';
				
				if (status == "success" && data == "ok")
				{
					$('#cms_set_response').html("關閉成功");
					$("#cms_status_ui").attr("src", "img/off1.png");
				}
				else
				{
					$('#cms_set_response').html("連線異常");
					$("#cms_status_ui").attr("src", "img/off1.png");
				}
			});
		}
			
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

<?php get_sidebar_html(1); ?>

			<div style="margin-bottom:100px;"></div>
		</div>
	</div>
	
	<div class="w3-overlay w3-hide-large" onclick="w3_close()" id="myOverlay"></div>
	
	<div class="w3-main w3-container w3-padding-large" style="margin-left:240px;margin-top:59px;">
	
		<h1>苗縣道126 23.5K邊坡 即時監測數據</h1>
		
		<div class="w3-card-4" style="margin-top:10px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 576 512">
					<path d="M288 0c-69.59 0-126 56.41-126 126 0 56.26 82.35 158.8 113.9 196.02 6.39 7.54 17.82 7.54 24.2 0C331.65 284.8 414 182.26 414 126 414 56.41 357.59 0 288 0zm0 168c-23.2 0-42-18.8-42-42s18.8-42 42-42 42 18.8 42 42-18.8 42-42 42zM20.12 215.95A32.006 32.006 0 0 0 0 245.66v250.32c0 11.32 11.43 19.06 21.94 14.86L160 448V214.92c-8.84-15.98-16.07-31.54-21.25-46.42L20.12 215.95zM288 359.67c-14.07 0-27.38-6.18-36.51-16.96-19.66-23.2-40.57-49.62-59.49-76.72v182l192 64V266c-18.92 27.09-39.82 53.52-59.49 76.72-9.13 10.77-22.44 16.95-36.51 16.95zm266.06-198.51L416 224v288l139.88-55.95A31.996 31.996 0 0 0 576 426.34V176.02c0-11.32-11.43-19.06-21.94-14.86z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">地圖</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">

<div id="map" style="height:60vh;z-index:0;"></div>
<script type="text/javascript">
    var marker_obj = [];
    var map_marker_list =
    [
        {
            position:
            {
                lat: 24.5910116,
                lng: 120.9107537,
            },
            label:
            {
                text: "苗縣道126 23.5K邊坡",
            },
            locpic_num: "locpic_01"
        },
    ];
    
    var map = L.map('map', {attributionControl:true, zoom:17, zoomControl:true,}).setView([map_marker_list[0].position.lat, map_marker_list[0].position.lng], 17);
    
    const tiles = L.tileLayer('https://wmts.nlsc.gov.tw/wmts/EMAP/default/GoogleMapsCompatible/{z}/{y}/{x}', {
    	
    }).addTo(map);
    
    const marker_icon = L.icon({
    	iconUrl: 'img/map-marker.png',
    	iconSize:[48, 48],
    });
    
    map_marker_list.map(item => L.marker(new L.LatLng(item['position']['lat'], item['position']['lng']), {icon: marker_icon, zIndexOffset:5}).on('click', function(e){Marker_onClick(item.locpic_num)}).addTo(map))
    .forEach(item => marker_obj.push(item));
    
    function Marker_onClick(obj_id)
    {
        document.getElementById(obj_id).style.display='block';
    }
</script>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:20px;width:100%;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">即時擷圖</span>
			</header>
			
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" >
                <div class="w3-half" style="">
					<!--
                    <img style="width:100%;object-fit:fill;margin-top:14px;" src="live/3-1.jpg<?php echo '?t=' . rand() ?>" loading="lazy"></img>
					-->
					<a href="/manager-page/api/live.php?id=31" target="_blank">
					<img loading="lazy" style="width:100%;object-fit:fill;margin-top:14px;" data-src="/manager-page/api/snapshot.php?id=31" src="/manager-page/api/snapshot.php?id=31" onerror="this.src='live/3-1.jpg';" class="video_obj" />
					</a>
				</div>
                <div class="w3-half" style="">
					<!--
                    <img style="width:100%;object-fit:fill;margin-top:14px;margin-bottom:14px;" src="live/3-2.jpg<?php echo '?t=' . rand() ?>" loading="lazy"></img>
					-->
					<a href="/manager-page/api/live.php?id=32" target="_blank">
					<img loading="lazy" style="width:100%;object-fit:fill;margin-top:14px;margin-bottom:14px;" data-src="/manager-page/api/snapshot.php?id=32" src="/manager-page/api/snapshot.php?id=32" onerror="this.src='live/3-2.jpg';" class="video_obj" />
					</a>
				</div>
			</div>
		</div>
		
		<div class="w3-card-4" style="margin-top:20px;width:100%;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">警告擷圖</span>
			</header>
			
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" >
                <div class="w3-half" style="">
                    <img style="width:100%;object-fit:fill;margin-top:14px;" src="alarm/3-1.jpg<?php echo '?t=' . rand() ?>" loading="lazy"></img>
                </div>
                <div class="w3-half" style="">
                    <img style="width:100%;object-fit:fill;margin-top:14px;margin-bottom:14px;" src="alarm/3-2.jpg<?php echo '?t=' . rand() ?>" loading="lazy"></img>
                </div>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:20px;width:100%;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">傾斜儀 詳細資料</span>
			</header>
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
				<table class="w3-table-all" style="margin-top:14px;margin-bottom:14px;">
					<tr>
						<th style="text-align:center;vertical-align:middle;">監測位置</th>
						<th style="text-align:center;vertical-align:middle;">燈號狀態</th>
						<th style="text-align:center;vertical-align:middle;">數值(度)</th>
						<th style="text-align:center;vertical-align:middle;">IOT設備電壓(V)</th>
						<th style="text-align:center;vertical-align:middle;">接收時間</th>
						<th style="text-align:center;vertical-align:middle;">傳輸頻率</th>
					</tr>

<?php
    home_get_inclinometer_all();
?>

				</table>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">雨量 詳細資料</span>
			</header>
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
				<table class="w3-table-all" style="margin-top:14px;margin-bottom:14px;">
					<tr>
						<th style="text-align:center;vertical-align:middle;">監測位置</th>
						<th style="text-align:center;vertical-align:middle;">燈號狀態</th>
						<th style="text-align:center;vertical-align:middle;">數值(mm)</th>
						<th style="text-align:center;vertical-align:middle;">IOT設備電壓(V)</th>
						<th style="text-align:center;vertical-align:middle;">接收時間</th>
						<th style="text-align:center;vertical-align:middle;">傳輸頻率</th>
					</tr>

<?php
    home_get_raingauge_all();
?>

				</table>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:20px;width:100%;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">系統電源 狀態</span>
			</header>
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
				<table class="w3-table-all" style="margin-top:14px;margin-bottom:14px;">
					<tr>
						<th style="text-align:center;vertical-align:middle;width:33%;">監測項目</th>
						<th style="text-align:center;vertical-align:middle;width:33%;">狀態</th>
						<th style="text-align:center;vertical-align:middle;width:33%;">接收時間</th>
					</tr>

<tr>
<td style="text-align:center;vertical-align:middle;">市電監測</td>
<td style="text-align:center;vertical-align:middle;"><span id="lp_led_status_ui">⚫</span></td>
<td style="text-align:center;vertical-align:middle;">-</td>
</tr>

<?php
	// home_get_line_power(301);
	home_get_battery_voltage(302)
?>

				</table>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:20px;width:100%;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
                    <svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
                        <path fill="currentColor" d="M487.4 315.7l-42.6-24.6c4.3-23.2 4.3-47 0-70.2l42.6-24.6c4.9-2.8 7.1-8.6 5.5-14-11.1-35.6-30-67.8-54.7-94.6-3.8-4.1-10-5.1-14.8-2.3L380.8 110c-17.9-15.4-38.5-27.3-60.8-35.1V25.8c0-5.6-3.9-10.5-9.4-11.7-36.7-8.2-74.3-7.8-109.2 0-5.5 1.2-9.4 6.1-9.4 11.7V75c-22.2 7.9-42.8 19.8-60.8 35.1L88.7 85.5c-4.9-2.8-11-1.9-14.8 2.3-24.7 26.7-43.6 58.9-54.7 94.6-1.7 5.4.6 11.2 5.5 14L67.3 221c-4.3 23.2-4.3 47 0 70.2l-42.6 24.6c-4.9 2.8-7.1 8.6-5.5 14 11.1 35.6 30 67.8 54.7 94.6 3.8 4.1 10 5.1 14.8 2.3l42.6-24.6c17.9 15.4 38.5 27.3 60.8 35.1v49.2c0 5.6 3.9 10.5 9.4 11.7 36.7 8.2 74.3 7.8 109.2 0 5.5-1.2 9.4-6.1 9.4-11.7v-49.2c22.2-7.9 42.8-19.8 60.8-35.1l42.6 24.6c4.9 2.8 11 1.9 14.8-2.3 24.7-26.7 43.6-58.9 54.7-94.6 1.5-5.5-.7-11.3-5.6-14.1zM256 336c-44.1 0-80-35.9-80-80s35.9-80 80-80 80 35.9 80 80-35.9 80-80 80z"></path>
                    </svg>
				<span style="font-size:16px;margin-left:4px;">牌面控制</span>
			</header>
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
				<table class="w3-table-all" style="margin-top:14px;margin-bottom:14px;">
					<tr>
						<th style="text-align:center;vertical-align:middle;width:33%;">項目</th>
						<th style="text-align:center;vertical-align:middle;width:33%;">操作狀態</th>
						<th style="text-align:center;vertical-align:middle;width:33%;">系統狀態</th>
					</tr>

<tr>
<td style="text-align:center;vertical-align:middle;">告警牌面</td>
<td style="text-align:center;vertical-align:middle;">
	<a onclick="document.getElementById('cms_control').style.display='block'" style="color:blue;cursor:pointer;">
		<img id="cms_status_ui" src="img/off1.png" width="120px"></img>
	</a>
</td>
<td style="text-align:center;vertical-align:middle;">
<span id="cms_status_ui2">⚫</span>
</td>
</tr>

				</table>
			</div>
		</div>

		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
				Copyright © 基能科技股份有限公司
		</footer>
		
	</div>
	
	<div id="locpic_01" class="w3-modal">
		<div class="w3-modal-content" style="max-width:480px;">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:20px; vertical-align:middle;">苗縣道126 23.5K邊坡</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('locpic_01').style.display='none'">&times;</a>
			</div>
			<div class="w3-content w3-center">
				<img src="img/locpic_03.jpg" class="w3-light-gray" style="object-fit:contain;width:100%;max-height:360px;">
			</div>
		</div>
	</div>
	
	<div id="cms_control" class="w3-modal">
		<div class="w3-modal-content" style="max-width:480px;">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:20px; vertical-align:middle;">告警牌面</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="cms_control_ui_close()">&times;</a>
			</div>
			
			<div class="w3-center w3-padding w3-light-gray" style="width:100%;">
			<button class="w3-btn w3-round w3-orange" style="margin:10px;" onclick="cms_on();">開啟牌面</button>
			<button class="w3-btn w3-round w3-pink" style="margin:10px;" onclick="cms_off();">關閉牌面</button>
			<br>
			</div>
			
		</div>
	</div>
	
	<div id="cms_response" class="w3-modal">
		<div class="w3-modal-content" style="max-width:480px;">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:20px; vertical-align:middle;">系統訊息</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('cms_response').style.display='none';">&times;</a>
			</div>
			
			<div class="w3-center w3-padding w3-light-gray" style="width:100%;">
			<h5 id="cms_set_response"></h5>
			<button class="w3-btn w3-round w3-pink" style="margin:10px;" onclick="document.getElementById('cms_response').style.display='none';">關閉</button>
			</div>
			
		</div>
	</div>
	
	<div id="cms_connecting" class="w3-modal">
		<div class="w3-modal-content" style="max-width:480px;">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:20px; vertical-align:middle;">系統訊息</a>
				<!--
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('cms_response').style.display='none';">&times;</a>
				-->
			</div>
			
			<div class="w3-center w3-padding w3-light-gray" style="width:100%;">
			<h5 id="cms_connecting_str"></h5>
			
		</div>
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
		$.get("api/cms-status3.php", function(data, status)
		{
			if (status == "success")
			{
				document.getElementById("cms_status_ui").src = data;
				document.getElementById("cms_status_ui2").innerHTML = '🟢';
			}
			else
			{
				// document.getElementById("cms_status_ui").src = "img/unknown.png";
				document.getElementById("cms_status_ui").src = "img/off1.png";
				document.getElementById("cms_status_ui2").innerHTML = '⚫';
			}
		});
		$.get("api/lp-status3.php", function(data, status)
		{
			if (status == "success")
			{
				if (data == "1")
				{
					document.getElementById("lp_led_status_ui").innerHTML = "🟢";
				}
				else if (data == "0")
				{
					document.getElementById("lp_led_status_ui").innerHTML = "🔴";
				}
				else
				{
					document.getElementById("lp_led_status_ui").innerHTML = "⚫";
				}
			}
			else
			{
				document.getElementById("lp_led_status_ui").innerHTML = "⚫";
			}
		});
		setInterval
		(
			function()
			{
				$(".video_obj").each
				(
					function()
					{
						var video_obj_path = $(this).attr("data-src");
						if(video_obj_path.indexOf("?") >= 0)
						{
							$(this).attr("src", video_obj_path + "&t=" + Math.random());
						}
						else
						{
							$(this).attr("src", video_obj_path + "?t=" + Math.random());
						}
					}
				);
			}
		, 5000);
	</script>
	</body>
</html>
