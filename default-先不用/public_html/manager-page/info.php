<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    require("C:\\xampp\\htdocs\\default\\public-func.php");
    
?>
<!DOCTYPE html>
<html lang="zh-tw">
	<head>
		<title>管理值說明 - <?php echo $system_name; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<link rel="stylesheet" href="css/w3-4.10.css">
		<link rel="stylesheet" href="css/font-awesome-4.7.0.css">
		<script src="js/jquery-3.6.0.js"></script>
		<script src="js/chart-2.9.4.js"></script>
        
		<!--
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/w3-css/4.1.0/w3.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
		-->
		<script language="javascript">
			window.onload = function()
			{
				
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
	
		<div id="menuTut" class="myMenu">

<?php get_sidebar_html(4); ?>

			<div style="margin-bottom:100px;"></div>
		</div>
		
	</div>
	
	<div class="w3-overlay w3-hide-large" onclick="w3_close()" id="myOverlay"></div>
	
	<div class="w3-main w3-container w3-padding-large" style="margin-left:240px;margin-top:59px;">
	
		<h1>管理值說明</h1>

		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
					<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">傾斜儀</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
				<table class="w3-table">
					<tr>
						<th style="width:20%;text-align:center;vertical-align:middle;">燈號</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128994;</span>綠</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128993;</span>黃</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128992;</span>橙</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128308;</span>紅</th>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">狀態</td>
						<td style="text-align:center;vertical-align:middle;">正常</td>
						<td style="text-align:center;vertical-align:middle;">預警</td>
						<td style="text-align:center;vertical-align:middle;">警戒</td>
						<td style="text-align:center;vertical-align:middle;">行動</td>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">傾斜角度</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
						<td style="text-align:center;vertical-align:middle;"><?php echo $inclinometer_step1; ?> 度</td>
						<td style="text-align:center;vertical-align:middle;"><?php echo $inclinometer_step2; ?> 度</td>
						<td style="text-align:center;vertical-align:middle;"><?php echo $inclinometer_step3; ?> 度</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
					<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">雨量</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
				<table class="w3-table">
					<tr>
						<th style="width:20%;text-align:center;vertical-align:middle;">燈號</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128994;</span>綠</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128993;</span>黃</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128992;</span>橙</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128308;</span>紅</th>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">狀態</td>
						<td style="text-align:center;vertical-align:middle;">正常</td>
						<td style="text-align:center;vertical-align:middle;">預警</td>
						<td style="text-align:center;vertical-align:middle;">警戒</td>
						<td style="text-align:center;vertical-align:middle;">行動</td>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">10分鐘雨量</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step1_10m; ?> mm</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">時雨量</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step1; ?> mm</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step2; ?> mm</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step3; ?> mm</td>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">連續24小時累積雨量</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step1_1440m; ?> mm</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step2_1440m; ?> mm</td>
						<td style="text-align:center;vertical-align:middle;">≧ <?php echo $raingauge_step3_1440m; ?> mm</td>
					</tr>
				</table>
			</div>
		</div>
		
		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
					<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">牌面說明</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
				<table class="w3-table">
					<tr>
						<th style="width:20%;text-align:center;vertical-align:middle;">運作說明</th>
						<th style="width:20%;text-align:center;vertical-align:middle;">自動模式</th>
						<th style="width:20%;text-align:center;vertical-align:middle;">手動模式</th>
						<th style="width:20%;text-align:center;vertical-align:middle;">備註</th>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">自動關閉時間(預設值)</td>
						<td style="text-align:center;vertical-align:middle;">自啟動後3小時</td>
						<td style="text-align:center;vertical-align:middle;">自啟動後3小時</td>
						<td style="text-align:center;vertical-align:middle;">事件觸發自動啟動與人工手動啟動，牌面皆會在3小時內自動關閉。</td>
					</tr>
				</table>
			</div>
		</div>
		
		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
					<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">牌面說明2</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
				<table class="w3-table">
					<tr>
						<th style="width:20%;text-align:center;vertical-align:middle;">燈號</th>
						<th style="width:20%;text-align:center;vertical-align:middle;">綠燈</th>
						<th style="width:20%;text-align:center;vertical-align:middle;">灰燈</th>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">牌面連線狀態</td>
						<td style="text-align:center;vertical-align:middle;">正常</td>
						<td style="text-align:center;vertical-align:middle;">斷線</td>
					</tr>
				</table>
			</div>
		</div>
		
		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
					<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">(俯視圖)面向感測器方向</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom w3-center">
				<img src="/manager-page/img/inclinometer-info.png" alt="傾斜儀方位表示圖" title="傾斜儀方位表示圖" style="max-width:300px;" />
			</div>
		</div>
		
        <!--
		<div class="w3-card-4" style="margin-top:20px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
					<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">水位計</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">
				<table class="w3-table">
					<tr>
						<th style="width:20%;text-align:center;vertical-align:middle;">燈號</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128994;</span>綠</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128993;</span>黃</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128992;</span>橙</th>
						<th style="width:20%;text-align:center;vertical-align:middle;"><span>&#128308;</span>紅</th>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">狀態</td>
						<td style="text-align:center;vertical-align:middle;">正常</td>
						<td style="text-align:center;vertical-align:middle;">預警</td>
						<td style="text-align:center;vertical-align:middle;">警戒</td>
						<td style="text-align:center;vertical-align:middle;">行動</td>
					</tr>
					<tr>
						<td style="text-align:center;vertical-align:middle;">水位值</td>
						<td style="text-align:center;vertical-align:middle;">-</td>
						<td style="text-align:center;vertical-align:middle;"><?php echo $waterlevelgauge_step1; ?> 公分</td>
						<td style="text-align:center;vertical-align:middle;"><?php echo $waterlevelgauge_step2; ?> 公分</td>
						<td style="text-align:center;vertical-align:middle;"><?php echo $waterlevelgauge_step3; ?> 公分</td>
					</tr>
				</table>
			</div>
		</div>
		-->
		
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
