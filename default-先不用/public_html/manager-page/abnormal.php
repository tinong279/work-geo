<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    require("C:\\xampp\\htdocs\\default\\home-func-1.php");
    require("C:\\xampp\\htdocs\\default\\public-func.php");
    
?>
<!DOCTYPE html>
<html lang="zh-tw">
	<head>
		<title>異常數據說明 - <?php echo $system_name; ?></title>
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

<?php get_sidebar_html(5); ?>

			<div style="margin-bottom:100px;"></div>
		</div>
	</div>
	
	<div class="w3-overlay w3-hide-large" onclick="w3_close()" id="myOverlay"></div>
	
	<div class="w3-main w3-container w3-padding-large" style="margin-left:240px;margin-top:59px;">
	
		<h1>異常數據說明</h1>
		
		<div class="w3-card-4" style="margin-top:10px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
                    <path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">數據說明一</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom w3-center">
			    <img src="img/error_1.jpg" width="100%" style="max-width:800px;" loading="lazy">
			    <h4 style="margin-top:18px;">此數據因儀器擾動或突波，而產生變化，屬於正常現象，無須採取應變措施。</h4>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:10px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
                    <path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">數據說明二</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom w3-center">
			    <img src="img/error_2.jpg" width="100%" style="max-width:800px;" loading="lazy">
			    <h4 style="margin-top:18px;">此數據為設備斷訊現象造成之變化，需進行斷訊了解，請通知原廠商進行查修。</h4>
			</div>
		</div>

		<div class="w3-card-4" style="margin-top:10px;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
                    <path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">數據說明三</span>
			</header>
			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom w3-center">
			    <img src="img/error_3.jpg" width="100%" style="max-width:800px;" loading="lazy">
			    <h4 style="margin-top:18px;">此數據因溫差造成之週期變化，屬於正常現象，無須採取應變措施。</h4>
			</div>
		</div>

		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
				Copyright © 基能科技股份有限公司
		</footer>
		
	</div>
	
	<div id="locpic_01" class="w3-modal">
		<div class="w3-modal-content" style="max-width:480px;">
			<div class="w3-bar w3-blue">
				<a class="w3-bar-item w3-padding-8" style="font-size:20px; vertical-align:middle;">苗21縣道12K+500</a>
				<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('locpic_01').style.display='none'">&times;</a>
			</div>
			<div class="w3-content w3-center">
				<img src="/img/locpic_01.jpg" class="w3-light-gray" style="object-fit:contain;width:100%;max-height:360px;">
			</div>
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
	</script>
	</body>
</html>
