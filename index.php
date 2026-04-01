<!DOCTYPE html>
<html lang="zh-tw">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="/css/w3-4.15.css" />
	<script src="/js/jquery-3.6.0.js"></script>
	<script>
	</script>
	<style>
	</style>
	<title>苗栗縣旅遊地區交通監控及管理平台</title>

	<meta name="description" content="苗栗縣旅遊地區交通監控及管理平台">
	<meta property="og:site_name" content="苗栗縣旅遊地區交通監控及管理平台">
	<meta property="og:title" content="苗栗縣旅遊地區交通監控及管理平台">
	<meta property="og:type" content="website">
	<meta property="og:description" content="苗栗縣旅遊地區交通監控及管理平台">

	<style>
		.sidebar-item-instant {
			background: #32b8c61a !important;
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 14px 16px;
			margin-bottom: 16px;
			border-radius: 14px;
			cursor: pointer;

			transition: all 0.25s ease;
			font-size: 15px;
		}

		/* 當 ID 為 SlopeMonitorBtn 且同時具有 is-open 類別時生效 */
		.SlopeMonitorBtn.is-open {
			margin-bottom: 2px !important;
		}

		/* 設定箭頭的基本樣式 */
		.nav-icon {
			width: 24px !important;
			/* 變大 */
			height: 24px !important;
			/* 變大 */
			fill: white !important;
			/* 變成白色 */
			transition: transform 0.3s ease;
			/* 旋轉動畫 */
			vertical-align: middle;
			margin-right: 8px;
		}

		/* 當選單打開時，箭頭旋轉 180 度（朝上） */
		.icon-rotate {
			transform: rotate(180deg);
		}

		/* --- 側邊欄子項目選中樣式 --- */
		.active-slope-link {
			background-color: rgba(79, 209, 197, 0.2) !important;
			/* 半透明青色背景 */
			color: #4fd1c5 !important;
			/* 文字變青色 */
			border-left: 4px solid #4fd1c5 !important;
			/* 左側發光邊條 */
			font-weight: bold;
			transition: all 0.2s ease;
		}

		c

		/* 滑鼠移入時的微亮效果 */
		#SlopeMonitor a:hover,
		#TrafficMonitor a:hover,
		.SlopeMonitorBtn:hover {
			background-color: #ccc !important;
			/* 改為純白色 */
			color: #000000 !important;
			/* 文字改為黑色，否則白底白字會看不見 */
			transition: 0.2s;
		}

		.SlopeMonitorBtn:hover span {
			color: #000000 !important;
			transition: 0.2s;
		}
	</style>

</head>

<body>

	<div class="w3-top" style="z-index:15;">
		<div class="w3-bar w3-card" id="Navbar" class="" style="background-color:#606060;height:51px;">
			<div href="/" class="w3-wide" style="position:absolute;top:8px;left:10px;text-decoration:none;"><b style="font-size:24px;color:white;">苗栗縣旅遊地區交通監控及管理平台</b></div>
			<div class="w3-right" style="font-size:16px;">
				<!--
				<a href="/" class="w3-bar-item w3-button w3-hide-small w3-hide-medium"><span style="color:white;"><b>AAA</b></span></a>
				-->
			</div>
		</div>
	</div>

	<script src="/js/leaflet-1.9.3.js"></script>
	<link rel="stylesheet" href="/css/leaflet-1.9.5.css" />
	<script src="/js/leaflet-markerclusterplugin-1.4.1.js"></script>
	<link rel="stylesheet" href="/css/leaflet-markerclusterplugin-1.4.1.css" />

	<div style="padding-top:51px;"></div>


	<div class="app-layout">
		<div class="w3-sidebar sidebar-dark w3-bar-block w3-card w3-animate-left" style="font-size:14px;border:1px solid #777c7c4d;" id="Sidebar">
			<div style="height:10px;"></div>

			<!-- ====================================================================== -->

			<div class="w3-padding-small"><img src="/img/stacks_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:4px;color:white;">資訊篩選</span></div>

			<div class="w3-bar-item w3-button sidebar-item" onclick="toggleAccordion('InstantData')" style="cursor:pointer;">
				<img src="/img/stacks_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img> <span style="margin-left:8px;color:white;">即時資料</span>

			</div>

			<div id="InstantData" class="w3-hide">

				<div class="SlopeMonitorBtn w3-bar-item  sidebar-item-instant"
					onclick="toggleAccordion('TrafficMonitor', this)" style="padding-left: 24px;">

					<svg id="TrafficIcon" class="nav-icon" viewBox="0 0 24 24">
						<path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"></path>
					</svg>

					<span style="color:white;">交通路況監測</span>
				</div>

				<div id="TrafficMonitor" class="w3-hide SlopeMonitorBtn">
					<a href="javascript:void(0)" class="w3-bar-item w3-button" style="padding-left: 30px; color:white;"
						onclick="ShowDefaultMap(this)">
						南庄平面道路
					</a>
				</div>


				<div class="SlopeMonitorBtn w3-bar-item w3-button sidebar-item-instant"
					onclick="toggleAccordion('SlopeMonitor', this)" style="padding-left: 24px;">

					<svg id="SlopeIcon" class="nav-icon" viewBox="0 0 24 24">
						<path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"></path>
					</svg>

					<span style="color:white;">邊坡監測</span>
				</div>

				<div id="SlopeMonitor" class="w3-hide">
					<a href="javascript:void(0)" class="w3-bar-item w3-button" style="padding-left: 30px; color:white;"
						onclick="ShowSlopePage('iframe_21k', this)">
						苗62鄉道 2.1K邊坡
					</a>

					<a href="javascript:void(0)" class="w3-bar-item w3-button" style="padding-left: 30px; color:white;"
						onclick="ShowSlopePage('iframe_62k', this)">
						苗62鄉道 6.2K邊坡
					</a>

					<a href="javascript:void(0)" class="w3-bar-item w3-button" style="padding-left: 30px; color:white;"
						onclick="ShowSlopePage('iframe_5k200', this)">
						苗62鄉道 5K+200邊坡
					</a>
				</div>


			</div>





			<div onclick="SettingMapLayer(0);" id="Layer_00" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/videocam_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">即時影像</span></div>
			<a href="javascript:void(0)" class="w3-bar-item w3-button sidebar-item" id="FullImageBtn"
				style="padding-left: 16px; color:white;"
				onclick="ShowFullImagePage(this)">
				<img src="/img/videocam_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img>
				<span style="margin-left:8px;color:white;">即時影像-全圖</span>
			</a>
			<!--
	<div onclick="SettingMapLayer(7)" id="Layer_07" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">對向來車系統管理</span></div>
	-->
			<a href="https://iot-sunmade.com.tw/sunmadelook/default.aspx" target="_blank" id="Layer_07" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">對向來車系統資訊</span></a>

			<div onclick="SettingMapLayer(5);" id="Layer_05" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/local_parking_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">停車場資訊</span></div>

			<div onclick="SettingMapLayer(1);" id="Layer_01" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/radar_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">雷達迴波</span></div>

			<div onclick="SettingMapLayer(2);" id="Layer_02" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/rainy_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">累積雨量</span></div>

			<div onclick="SettingMapLayer(3);" id="Layer_03" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/partly_cloudy_day_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">天氣資訊</span></div>

			<!-- ====================================================================== -->
			<div style="border-top: 1px solid #777c7c4d;margin-top: 24px;padding-top: 8px; ">
				<div class="w3-padding-small" style="padding: 8px 8px !important;"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:4px;color:white;">車流導引發佈系統</span></div>

				<div onclick="window.open('/cms-system/realtime/');" id="Layer_04" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/bolt_24dp_5F6368_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">即時訊息發布設定</span></div>

				<div onclick="window.open('/cms-system/schedule/');" id="Layer_04" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/pending_actions_24dp_5F6368_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">排程訊息發布設定</span></div>

				<div onclick="SettingMapLayer(8);" id="Layer_08" class="w3-bar-item w3-button sidebar-item" style=""><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">車流動態導引面板</span></div>

				<div href="javascript:void(0)" class="w3-bar-item w3-button sidebar-item" style="color:white;"
					onclick="ShowSlopePage('iframe_62k_cms', this)"><img src="/img/bolt_24dp_5F6368_FILL0_wght400_GRAD0_opsz24.svg"></img>
					苗62訊息發布設定
				</div>
			</div>
			<!-- ====================================================================== -->

			<div onclick="window.open('/cms-system/device-info.php');" class="w3-bar-item w3-button sidebar-item"><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;color:white;">設備管理</span></div>

			<!-- ====================================================================== -->

			<div class="w3-padding" style="width:100%; margin-top: 24px;padding-top: 16px !important;">
				<div class="" style="color:white;"><b>南庄平面道路車速</b></div>
				<table class="w3-border" style="font-size:12px;background-color:#ffffff;">
					<tbody>
						<tr>
							<th>顏色</th>
							<th>說明</th>
						</tr>
						<tr>
							<td style="background-color:#ff0000;" title="紅色代表壅塞"></td>
							<td>　　　壅 塞　　　　</td>
						</tr>
						<tr>
							<td style="background-color:#ff9900;" title="橘色代表車多"></td>
							<td>　　　車 多　　　　</td>
						</tr>
						<tr>
							<td style="background-color:#009900;" title="綠色代表順暢"></td>
							<td>　　　順 暢　　　　</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="w3-padding" style="width:100%;">
				<div class="" style="color:white;"><b>高快速道路車速</b></div>
				<table class="w3-border" style="font-size:12px;background-color:#ffffff;">
					<tbody>
						<tr>
							<th>顏色</th>
							<th>說明</th>
						</tr>
						<tr>
							<td style="background-color:#ff00ff;" title="時速 20 公里以下"></td>
							<td>時速 20 公里以下</td>
						</tr>
						<tr>
							<td style="background-color:#ff0000;" title="時速 20 ~ 39 公里"></td>
							<td>時速 20 ~ 39 公里</td>
						</tr>
						<tr>
							<td style="background-color:#ff9900;" title="時速 40 ~ 59 公里"></td>
							<td>時速 40 ~ 59 公里</td>
						</tr>
						<tr>
							<td style="background-color:#f7d600;" title="時速 60 ~ 79 公里"></td>
							<td>時速 60 ~ 79 公里</td>
						</tr>
						<tr>
							<td style="background-color:#009900;" title="時速 80 公里以上"></td>
							<td>時速 80 公里以上</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="w3-padding-32"></div>

		</div>


		<!-- <div class="app-main">
			<div class="map-card">
				<div id="map">
					<div id="ruler_ui_1" class="w3-light-gray w3-round" style="z-index:800;padding:0px;border:2px solid gray;display:none;">
						<img id="ruler_ui_2" src="/img/dbz.png" width="40px" height="290px" title="" alt="" style="position:absolute;top:3px;left:3px;" />
					</div>
				</div>
			</div>
		</div> -->

		<!------------------------------------- 新增地圖區域 ---------------------------------------------->
		<div class="app-main">
			<div id="default_map_container" class="map-card" style="display:block;">
				<div id="map">
					<div id="ruler_ui_1" class="w3-light-gray w3-round" style="z-index:800;padding:0px;border:2px solid gray;display:none;">
						<img id="ruler_ui_2" src="/img/dbz.png" width="40px" height="290px" title="" alt="" style="position:absolute;top:3px;left:3px;" />
					</div>
				</div>
			</div>

			<div id="slope_data_container" style="display:none; height:calc(100vh - 51px); background-color: white;">
				<iframe id="iframe_21k" src="./default-1/public_html/manager-page/home-1.php" style="width:100%; height:100%; border:none; display:none;"></iframe>
				<iframe id="iframe_62k" src="./default-1/public_html/manager-page/home-2.php" style="width:100%; height:100%; border:none; display:none;"></iframe>
				<iframe id="iframe_5k200" src="./miaoli_62_5K+200_test/v2_api/index.php" style="width:100%; height:100%; border:none; display:none;"></iframe>
				<iframe id="iframe_62k_cms" src="./miaoli_62/realtime_message.php" style="width:100%; height:100%; border:none; display:none;"></iframe>
			</div>
			<div id="full_image_container" style="display:none; height:calc(100vh - 51px); background-color: white;">
				<iframe id="iframe_full_cctv" src="full_cctv.php" style="width:100%; height:100%; border:none;"></iframe>
			</div>
		</div>
		<!------------------------------------- 新增地圖區域 ---------------------------------------------->

		<!-- <div id="car_flow_sensor_msg" class="w3-modal" style="z-index:400;"> -->
		<div id="car_flow_sensor_panel" class="right-panel">
			<div class="car_flow_card" style="font-size:16px;">
				<div class="sensor-card sensor-detail-card">
					<div style="color:white;"><b>對向來車系統管理</b></div>
					<div class="close-button" style="color:white;" onclick="PanelManager.closeAll(); resetLayerUI();"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg></div>
				</div>
				<div class="" style="">
					<iframe src="03.php" style="width:100%;height:calc(100vh - 119px);" frameborder="0"></iframe>
					<!-- <button class="" style="min-width:100px;margin-top:10px;" onclick="car_flow_sensor_msg_Close();">關閉</button> -->
				</div>
			</div>
		</div>





		<div id="parking_panel" class="right-panel">
			<div class="car_flow_card" style="font-size:16px;">
				<div class="sensor-card sensor-detail-card">
					<div style="color:white;"><b>停車場資訊</b></div>

					<div class="close-button" onclick="PanelManager.closeAll(); resetLayerUI();">
						✕
					</div>
				</div>

				<div class="w3-padding w3-center">
					<div class="w3-text-gray" style="font-size:24px;">
						<b>南庄遊客中心停車場</b>
					</div>
					<div class="w3-text-gray">苗栗縣南庄鄉</div>
					<div class="w3-text-gray">大同路43號</div>

					<div class="w3-text-teal" style="margin-top:12px;">
						即時剩餘車位
					</div>

					<div style="font-size:48px;font-weight:700;color:#4fd1c5;">
						<span id="parking-01_1"></span>
					</div>
				</div>
			</div>
		</div>

		<div id="LiveCam" class="w3-modal" style="z-index:400;">
			<div class="w3-modal-content w3-light-gray" style="font-size:16px;width:400px;">
				<div class="w3-bar w3-blue" style="">
					<div class="w3-bar-item w3-left"><b>即時影像</b></div>
					<div class="w3-bar-item w3-hover-grey w3-right" style="cursor:pointer;" onclick="LiveCam_Close();"><b>×</b></div>
				</div>
				<div class="w3-padding w3-center" style="">
					<div id="LiveCam_name" class="w3-left"></div>
					<button class="w3-left w3-button w3-padding-small w3-lime w3-round" style="margin-left:6px;" onclick="LiveCam_reload();"><b>更新</b></button>
					<script>
						function LiveCam_reload() {
							str_buf1 = document.getElementById('LiveCam_source').src;
							document.getElementById('LiveCam_source').src = '';
							document.getElementById('LiveCam_source').src = str_buf1;
						}
					</script>
					<br>
					<img id="LiveCam_source" src="" data-src="" class="video_obj" style="object-fit:fill;width:100%;margin-top:10px;"></img>
					<br>
					<button class="w3-btn w3-blue w3-round w3-center" style="min-width:100px;margin-top:10px;" onclick="LiveCam_Close();">關閉</button>
				</div>
			</div>
		</div>


		<div id="cms_msg" class="w3-modal" style="z-index:32;">
			<div class="w3-modal-content w3-light-gray" style="font-size:16px;">
				<div class="w3-bar w3-blue" style="">
					<div class="w3-bar-item w3-left"><b>車流動態CMS管理</b></div>
					<div class="w3-bar-item w3-hover-grey w3-right" style="cursor:pointer;" onclick="cms_msg_Close();"><b>×</b></div>
				</div>
				<div class="w3-padding w3-center" style="">
					<!--
		    <img width="100%" src="/img/messageImage_1722581085293.jpg"></img>
		    -->

					<iframe src="/01.php" style="width:100%;height:60vh;" frameborder="0"></iframe>

					<button class="w3-btn w3-blue w3-round w3-center" style="min-width:100px;margin-top:10px;" onclick="cms_msg_Close();">關閉</button>
				</div>
			</div>
		</div>



		<div id="car_flow_msg" class="w3-modal" style="z-index:32;">
			<div class="w3-modal-content w3-light-gray" style="font-size:16px;">
				<div class="w3-bar w3-blue" style="">
					<div class="w3-bar-item w3-left"><b>道路車速分析系統</b></div>
					<div class="w3-bar-item w3-hover-grey w3-right" style="cursor:pointer;" onclick="car_flow_msg_Close();"><b>×</b></div>
				</div>
				<div class="w3-padding w3-center" style="">
					<iframe src="/02.php" style="width:100%;height:60vh;" frameborder="0"></iframe>
					<button class="w3-btn w3-blue w3-round w3-center" style="min-width:100px;margin-top:10px;" onclick="car_flow_msg_Close();">關閉</button>
				</div>
			</div>
		</div>

		<script>
			//======================================
			var LayerSettingArray = [false, false, false, false, false, false, false, false, false];
			var Layer_zoom = 8;
			var o_lat = 24.644837142840247;
			var o_lon = 120.96724487027633;
			var getLocation_flag = false;
			//======================================
			<?php

			$cms_dataList = [];
			$CMS_MarkersList = '';

			require("cms-system/ConnMySQL.php");

			if ($db_link == TRUE) {
				$db_link->query("SET NAMES utf8");

				$sql_query = "
        SELECT 
            a.`id`,
            a.`status`,
            a.`info`,
            a.`last-ping-echo-time`,
            a.`lat`,
            a.`lon`,
            b.`current_mode`,
            b.`display_type`,
            b.`text_content`,
            b.`text_color`,
            b.`text_size`,
            b.`image_path`
        FROM `01-cms-status` a
        LEFT JOIN `cms_status` b ON a.`id` = b.`id`
        ORDER BY a.`id` ASC
    ";

				$stmt = $db_link->prepare($sql_query);

				if ($stmt == true) {
					$stmt->execute();
					$stmt->store_result();

					$stmt->bind_result(
						$id,
						$status,
						$info,
						$last_ping_echo_time,
						$lat,
						$lon,
						$current_mode,
						$display_type,
						$text_content,
						$text_color,
						$text_size,
						$image_path
					);

					$count_buf1 = 0;

					while ($stmt->fetch()) {
						$cms_dataList[] = [
							"id" => $id,
							"status" => $status,
							"info" => $info,
							"last-ping-echo-time" => $last_ping_echo_time,
							"lat" => $lat,
							"lon" => $lon,
							"current_mode" => $current_mode,
							"display_type" => $display_type,
							"text_content" => $text_content,
							"text_color" => $text_color,
							"text_size" => $text_size,
							"image_path" => $image_path
						];

						if ($id >= 1 && $id <= 10) {
							$popupHtml = "<div class='w3-center' style='min-width:128px;'>"
								. "CMS-" . $id . "<br>"
								. $info . "<br>"
								. "<img src='/cms-system/cms-content-img/" . str_pad($id, 2, '0', STR_PAD_LEFT) . ".bmp' />"
								. "</div>";
						} elseif ($id >= 11 && $id <= 17) {
							if ($display_type == 'image' && !empty($image_path)) {
								$popupHtml = "<div class='w3-center' style='min-width:128px;'>"
									. "CMS-" . $id . "<br>"
									. $info . "<br>"
									. "<img src='/miaoli_62/" . ltrim($image_path, "/") . "' />"
									. "</div>";
							} elseif ($display_type == 'text' && !empty($text_content)) {
								$popupHtml = "<div class='w3-center' style='min-width:128px;'>"
									. "CMS-" . $id . "<br>"
									. $info . "<br>"
									. $text_content
									. "</div>";
							} else {
								$popupHtml = "<div class='w3-center' style='min-width:128px;'>"
									. "CMS-" . $id . "<br>"
									. $info . "<br>"
									. "無資料"
									. "</div>";
							}
						} else {
							$popupHtml = "<div class='w3-center' style='min-width:128px;'>"
								. "CMS-" . $id . "<br>"
								. $info . "<br>"
								. "無資料"
								. "</div>";
						}

						if ($count_buf1 > 0) {
							$CMS_MarkersList .= ',';
						}

						$CMS_MarkersList .= '['
							. $lat . ','
							. $lon . ','
							. '"/img/cms.png"' . ','
							. json_encode($popupHtml, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
							. ','
							. '""' . ','
							. '""' . ']';
						$CMS_MarkersList .= "\n";

						$count_buf1 += 1;
					}

					$stmt->close();
				}

				$db_link->close();
			}

			?>

			var CMS_MarkersList = [<?php echo $CMS_MarkersList; ?>];
			// // 手動補上的 CMS 點位
			// CMS_MarkersList.push(
			// 	[24.472447, 120.804505, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""],
			// 	[24.455985, 120.877737, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""],
			// 	[24.414874, 120.862746, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""],
			// 	[24.48005066429962, 120.79652619480197, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""],
			// 	[24.452234691100653, 120.87451555193206, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""],
			// 	[24.4434694377443, 120.89073661698605, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""],
			// 	[24.46622216956653, 120.93849438624606, "/img/cms.png", "<div class='w3-center' style='min-width:128px;'>CMS</div>", "", ""]
			// );
			/*
				var CMS_MarkersList = 
				[
				 [24.54151, 120.9481,"/img/cms.png","<b>42k+700處</b><br>雷達(往南) 🟢正常<br>雷達(往北) 🟢正常<br>電池1 🟢正常12.6 V<br>電池1 🟢正常12.2 V","",""]
				,[24.54267, 120.9472,"/img/cms.png","<b>43k+100處</b><br>雷達(往南) 🟢正常<br>雷達(往北) 🟢正常<br>電池1 🟢正常12.6 V<br>電池1 🟢正常12.2 V","",""]
				,[24.53793, 120.9285,"/img/cms.png","<b>48k+000處</b><br>雷達(往南) 🟢正常<br>雷達(往北) 🟢正常<br>電池1 🟢正常12.6 V<br>電池1 🟢正常12.2 V","",""]
				];
			*/
			//======================================
			function SettingMapLayer(obj_index) {
				if (obj_index == 0) {
					LayerSettingArray[0] = !LayerSettingArray[0];
					if (LayerSettingArray[0] == true) {
						add_cam_marker();
						document.getElementById("Layer_00").setAttribute("style", "background-color:orange;");
					} else {
						remove_cam_marker();
						document.getElementById("Layer_00").setAttribute("style", "");
					}
				} else if (obj_index == 1) {
					LayerSettingArray[1] = !LayerSettingArray[1];
					if (LayerSettingArray[1] == true) {
						add_obs_radar();
						document.getElementById("Layer_01").setAttribute("style", "background-color:orange;");
					} else {
						remove_obs_radar();
						document.getElementById("Layer_01").setAttribute("style", "");
					}
				} else if (obj_index == 2) {
					LayerSettingArray[2] = !LayerSettingArray[2];
					if (LayerSettingArray[2] == true) {
						add_obs_rainfall();
						document.getElementById("Layer_02").setAttribute("style", "background-color:orange;");
					} else {
						remove_obs_rainfall();
						document.getElementById("Layer_02").setAttribute("style", "");
					}
				} else if (obj_index == 3) {
					LayerSettingArray[3] = !LayerSettingArray[3];
					if (LayerSettingArray[3] == true) {
						add_weather_marker();
						document.getElementById("Layer_03").setAttribute("style", "background-color:orange;");
					} else {
						remove_weather_marker();
						document.getElementById("Layer_03").setAttribute("style", "");
					}
				} else if (obj_index == 5) {
					LayerSettingArray[5] = !LayerSettingArray[5];
					if (LayerSettingArray[5] == true) {
						// 開自己
						PanelManager.open('parking_panel');
						document.getElementById("Layer_05").setAttribute("style", "background-color:orange;");
					} else {
						PanelManager.closeAll();
						document.getElementById("Layer_05").setAttribute("style", "");
					}
				} else if (obj_index == 4) {
					LayerSettingArray[4] = !LayerSettingArray[4];
					if (LayerSettingArray[4] == true) {
						document.getElementById('cms_msg').style.display = 'block';
						document.getElementById("Layer_04").setAttribute("style", "background-color:orange;");
					} else {
						document.getElementById('cms_msg').style.display = 'none';
						document.getElementById("Layer_04").setAttribute("style", "");
					}
				} else if (obj_index == 6) {
					LayerSettingArray[6] = !LayerSettingArray[6];
					if (LayerSettingArray[6] == true) {
						document.getElementById('car_flow_msg').style.display = 'block';
						document.getElementById("Layer_06").setAttribute("style", "background-color:orange;");
					} else {
						document.getElementById('car_flow_msg').style.display = 'none';
						document.getElementById("Layer_06").setAttribute("style", "");
					}
				} else if (obj_index == 7) {
					LayerSettingArray[7] = !LayerSettingArray[7];
					if (LayerSettingArray[7] == true) {
						document.getElementById('car_flow_sensor_msg').style.display = 'block';
						document.getElementById("Layer_07").setAttribute("style", "background-color:orange;");
					} else {
						document.getElementById('car_flow_sensor_msg').style.display = 'none';
						document.getElementById("Layer_07").setAttribute("style", "");
					}
				} else if (obj_index == 8) {
					LayerSettingArray[8] = !LayerSettingArray[8];
					if (LayerSettingArray[8] == true) {
						document.getElementById("Layer_08").setAttribute("style", "background-color:orange;");

						CMS_MarkersList.map(item => L.marker(new L.LatLng(item[0], item[1]), {
								icon: L.icon({
									iconUrl: item[2],
									iconSize: [32, 32],
								})
							}).addTo(map).bindPopup(item[3]))
							.forEach(item => CMS_Markers.push(item));

					} else {
						document.getElementById("Layer_08").setAttribute("style", "");

						for (i = 0; i < CMS_Markers.length; i++) {
							map.removeLayer(CMS_Markers[i]);
						}
						CMS_Markers = [];

					}
				}
			}

			function LiveCam_Close() {
				document.getElementById('LiveCam_source').src = "/map-data/none.txt";
				document.getElementById('LiveCam').style.display = 'none';
			}

			function parking_msg_Close() {
				document.getElementById('parking_msg').style.display = 'none';
				document.getElementById("Layer_05").setAttribute("style", "");
				LayerSettingArray[5] = false;
			}

			function cms_msg_Close() {
				document.getElementById('cms_msg').style.display = 'none';
				document.getElementById("Layer_04").setAttribute("style", "");
				LayerSettingArray[4] = false;
			}

			function car_flow_msg_Close() {
				document.getElementById('car_flow_msg').style.display = 'none';
				document.getElementById("Layer_06").setAttribute("style", "");
				LayerSettingArray[6] = false;
			}

			function car_flow_sensor_msg_Close() {
				document.getElementById('car_flow_sensor_msg').style.display = 'none';
				document.getElementById("Layer_07").setAttribute("style", "");
				LayerSettingArray[7] = false;
			}
			//======================================
			var MapMarginTop = 51;
			$(window).on("resize", ResizeMap);

			function ResizeMap() {
				$('#map').css("height", ($(window).height() - MapMarginTop - 1));
				$('#map').css("width", ($(window).width()));
			}
			$(document).ready(function() {
				ResizeMap();
				init_map();
			});
			//=======================================================================
			var map = '';

			function init_map() {
				map = L.map('map', {
					attributionControl: false,
					zoom: 15,
					zoomControl: false,
				}).setView([o_lat, o_lon], Layer_zoom);

				L.control.zoom({
					position: 'bottomleft',
				}).addTo(map);

				var osm = L.tileLayer('https://wmts.nlsc.gov.tw/wmts/EMAP/default/GoogleMapsCompatible/{z}/{y}/{x}', {
					minZoom: 7,
					maxZoom: 17,
				}).addTo(map);

				var latLngBounds2 = L.latLngBounds([
					[21.505, 119.188],
					[25.920, 123.588]
				]);
				SouthWest = L.latLng(18.473, 117.000),
					NorthEast = L.latLng(27.473, 125.000);
				const bounds = L.latLngBounds(SouthWest, NorthEast);
				map.setMaxBounds(bounds);
				map.on('drag', function() {
					map.panInsideBounds(bounds, {
						animate: false
					});
				});

				map.on('zoomend', function(e) {
					Layer_zoom = e.target._zoom;
				});

				// 創建一個帶有文本的自定義標記
				var customIcon = L.divIcon({
					className: 'custom-label', // 使用自定義的 CSS 類來顯示文本
					html: '<div class="w3-white" style="font-size:16px;"><span>🅿️️</span ><span class="w3-text-blue" id="parking-01_2" style="font-weight:700;"></span></div>', // 這裡是你想顯示的文本
					iconSize: [48, 48],
					iconAnchor: [24, 0],
				});

				// 添加自定義標記到地圖
				L.marker([24.59796857085059, 120.99994264376956], {
					icon: customIcon
				}).addTo(map);

			}
			//=======================================================================
			const map_cam_icon = L.icon({
				iconUrl: '/map-data/videocam.png',
				iconSize: [32, 32],
			});
			//------------------------------0317新增------------------------------
			const company_cam_icon = L.icon({
				iconUrl: '/map-data/videocam_1.png',
				iconSize: [32, 32],
			});
			//------------------------------0317新增------------------------------
			//=======================================================================
			const SectionShape_url = '/map-data/section-shape.json';
			var SectionShape = [];
			var SectionShape_flag = false;

			const SectionShape_miaoli_01_url = '/map-data/miaoli-01.json';
			var SectionShape_miaoli_01 = [];
			var SectionShape_miaoli_01_flag = false;

			const CamList_url = '/map-data/cam-list.json';
			var CamList = [];
			var CamList_flag = false;
			var CamMarkers = L.markerClusterGroup();

			const LiveTraffic_url = '/map-data/live-traffic.json';
			var LiveTrafficStatus = [];
			var LiveTrafficStatus_flag = false;

			const LiveTraffic_miaoli_01_url = '/map-data/miaoli-01-live-traffic.json';
			var LiveTrafficStatus_miaoli_01 = [];
			var LiveTrafficStatus_flag_miaoli_01 = false;

			// ----------------------------------------------0317新增----------------------------------------------
			// --- 大湖路網相關變數 ---
			//dahu-coords.php
			//dahu-live-traffic.php
			const SectionShape_dahu_url = '/map-data/dahu-coords.json';
			var SectionShape_dahu = [];
			var SectionShape_dahu_flag = false;

			const LiveTraffic_dahu_url = '/map-data/dahu-live-traffic.json';
			var LiveTrafficStatus_dahu = [];
			var LiveTrafficStatus_flag_dahu = false;

			// 大湖專用的圖層組
			var SectionLiveTraffic_polyline_dahu = L.layerGroup();

			//大湖攝影機
			const CompanyCamList_url = '/map-data/company-cam-list.json'; // 新的檔案路徑
			var CompanyCamList = [];
			var CompanyCamList_flag = false;
			// ----------------------------------------------0317新增----------------------------------------------

			const parking_01_url = '/map-data/parking-01.json';
			var parking_01 = [];
			var parking_01_flag = false;
			var parking_01_num = 99;

			var SectionLiveTraffic_polyline_freeway = L.layerGroup();

			var SectionLiveTraffic_polyline_miaoli_01 = L.layerGroup();

			var SectionLiveTraffic_polyline_Lv6 = '';

			const OBS_Radar_Path = '/map-data/O-A0058-003.png';
			var OBS_Radar_imageOverlay = null;

			// const OBS_Rainfall_Path = '/map-data/O-A0040-002.jpg';
			const OBS_Rainfall_Path = '/map-data/O-A0040-003/0/0/0.png';
			var OBS_Rainfall_imageOverlay = null;

			const WeatherList_url = '/map-data/O-A0003-001.json';
			var WeatherList = [];
			var WeatherList_flag = false;
			var WeatherMarkers = [];

			var CMS_Markers = [];
			//=======================================================================
			$.get(SectionShape_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					SectionShape = data;
					SectionShape_flag = true;
				}
			});
			$.get(SectionShape_miaoli_01_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					SectionShape_miaoli_01 = data;
					SectionShape_miaoli_01_flag = true;
				}
			});
			$.get(CamList_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					CamList = data;
					CamList_flag = true;
				}
			});
			$.get(LiveTraffic_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					LiveTrafficStatus = data;
					LiveTrafficStatus_flag = true;
				}
			});
			$.get(LiveTraffic_miaoli_01_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					LiveTrafficStatus_miaoli_01 = data;
					LiveTrafficStatus_flag_miaoli_01 = true;
				}
			});
			// ----------------------------------------------0317新增----------------------------------------------
			// 抓取大湖地理座標
			$.get(SectionShape_dahu_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					SectionShape_dahu = data;
					SectionShape_dahu_flag = true;
				}
			});

			// 抓取大湖即時路況
			$.get(LiveTraffic_dahu_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					LiveTrafficStatus_dahu = data;
					LiveTrafficStatus_flag_dahu = true;
				}
			});
			//大湖攝影機
			$.get(CompanyCamList_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					CompanyCamList = data;
					CompanyCamList_flag = true;
				}
			});
			// ----------------------------------------------0317新增----------------------------------------------
			$.get(WeatherList_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					WeatherList = data;
					WeatherList_flag = true;
				}
			});
			$.get(parking_01_url + "?t=" + Math.random(), function(data, status) {
				if (status == "success") {
					parking_01 = data;
					parking_01_flag = true;
				}
			});
			//=======================================================================
			function add_obs_radar() {
				const OBS_Radar_latLngBounds = L.latLngBounds([
					[20.473, 118.000],
					[26.473, 124.000]
				]);
				OBS_Radar_imageOverlay = L.imageOverlay(OBS_Radar_Path + "?t=" + Math.random(), OBS_Radar_latLngBounds, {
					opacity: 0.6,
					OBS_Radar_Path,
					alt: '氣象局 雷達合成回波圖',
					interactive: true,
				}).addTo(map);
				const ruler_ui_1 = document.getElementById("ruler_ui_1");
				const ruler_ui_2 = document.getElementById("ruler_ui_2");
				ruler_ui_2.src = "/img/dbz.png";
				ruler_ui_1.style.display = "block";
			}

			function remove_obs_radar() {
				map.removeLayer(OBS_Radar_imageOverlay);
				const ruler_ui_1 = document.getElementById("ruler_ui_1");
				const ruler_ui_2 = document.getElementById("ruler_ui_2");
				ruler_ui_2.src = "";
				ruler_ui_1.style.display = "none";
			}

			function add_obs_rainfall() {
				const OBS_Rainfall_latLngBounds = L.latLngBounds([
					[21.505, 119.188],
					[25.920, 123.588]
				]);
				OBS_Rainfall_imageOverlay = L.imageOverlay(OBS_Rainfall_Path + "?t=" + Math.random(), OBS_Rainfall_latLngBounds, {
					opacity: 0.6,
					OBS_Rainfall_Path,
					alt: '氣象局 日累積雨量圖',
					interactive: true,
				}).addTo(map);
				const ruler_ui_1 = document.getElementById("ruler_ui_1");
				const ruler_ui_2 = document.getElementById("ruler_ui_2");
				ruler_ui_2.src = "/img/rain_mm.jpg";
				ruler_ui_1.style.display = "block";
			}

			function remove_obs_rainfall() {
				map.removeLayer(OBS_Rainfall_imageOverlay);
				const ruler_ui_1 = document.getElementById("ruler_ui_1");
				const ruler_ui_2 = document.getElementById("ruler_ui_2");
				ruler_ui_2.src = "";
				ruler_ui_1.style.display = "none";
			}

			function Add_SectionLiveTrafficPolyLine_freeway() {

				var check_flag = false;
				for (i = 0; i < SectionShape.length; i++) {
					try {
						var polyline = '';

						if (LiveTrafficStatus[i][1]['CongestionLevel'] == '1') {
							polyline = L.polyline(SectionShape[i][1], {
									color: "#00ff00",
									weight: 6
								})
								.bindPopup(`速度: ${LiveTrafficStatus[i][1]['TravelSpeed']} 公里/小時`);
							check_flag = true;
							SectionLiveTraffic_polyline_freeway.addLayer(polyline);
						} else if (LiveTrafficStatus[i][1]['CongestionLevel'] == '2') {
							polyline = L.polyline(SectionShape[i][1], {
									color: "#ffff00",
									weight: 6
								})
								.bindPopup(`速度: ${LiveTrafficStatus[i][1]['TravelSpeed']} 公里/小時`);
							check_flag = true;
							SectionLiveTraffic_polyline_freeway.addLayer(polyline);
						} else if (LiveTrafficStatus[i][1]['CongestionLevel'] == '3') {
							polyline = L.polyline(SectionShape[i][1], {
									color: "#ffa500",
									weight: 6
								})
								.bindPopup(`速度: ${LiveTrafficStatus[i][1]['TravelSpeed']} 公里/小時`);
							check_flag = true;
							SectionLiveTraffic_polyline_freeway.addLayer(polyline);
						} else if (LiveTrafficStatus[i][1]['CongestionLevel'] == '4') {
							polyline = L.polyline(SectionShape[i][1], {
									color: "#ff0000",
									weight: 6
								})
								.bindPopup(`速度: ${LiveTrafficStatus[i][1]['TravelSpeed']} 公里/小時`);
							check_flag = true;
							SectionLiveTraffic_polyline_freeway.addLayer(polyline);
						} else if (LiveTrafficStatus[i][1]['CongestionLevel'] == '5') {
							polyline = L.polyline(SectionShape[i][1], {
									color: "#ff00ff",
									weight: 6
								})
								.bindPopup(`速度: ${LiveTrafficStatus[i][1]['TravelSpeed']} 公里/小時`);
							check_flag = true;
							SectionLiveTraffic_polyline_freeway.addLayer(polyline);
						}
					} catch (error) {

					} finally {

					}
				}
				if (check_flag == true) {
					SectionLiveTraffic_polyline_freeway.addTo(map);
				}
			}

			function Remove_SectionLiveTrafficPolyLine_freeway() {
				SectionLiveTraffic_polyline_freeway.remove(map);
				SectionLiveTraffic_polyline_freeway = null;
				SectionLiveTraffic_polyline_freeway = L.layerGroup();
			}

			function Add_SectionLiveTrafficPolyLine_miaoli_01() {

				var check_flag = false;
				for (i = 0; i < SectionShape_miaoli_01.length; i++) {
					try {
						var polyline = '';

						for (j = 0; j < LiveTrafficStatus_miaoli_01.length; j++) {
							if (LiveTrafficStatus_miaoli_01[j][0] == SectionShape_miaoli_01[i][0]) {

								if (LiveTrafficStatus_miaoli_01[j][1]['CongestionLevel'] == '1') {
									polyline = L.polyline(SectionShape_miaoli_01[i][1], {
											color: "#00ff00",
											weight: 6
										})
										.bindPopup(`速度: ${LiveTrafficStatus_miaoli_01[j][1]['TravelSpeed']} 公里/小時, SectionID: ${LiveTrafficStatus_miaoli_01[j][1]['SectionID']}`);
									check_flag = true;
									SectionLiveTraffic_polyline_miaoli_01.addLayer(polyline);
								} else if (LiveTrafficStatus_miaoli_01[j][1]['CongestionLevel'] == '2') {
									polyline = L.polyline(SectionShape_miaoli_01[i][1], {
											color: "#ffa500",
											weight: 6
										})
										.bindPopup(`速度: ${LiveTrafficStatus_miaoli_01[j][1]['TravelSpeed']} 公里/小時, SectionID: ${LiveTrafficStatus_miaoli_01[j][1]['SectionID']}`);
									check_flag = true;
									SectionLiveTraffic_polyline_miaoli_01.addLayer(polyline);
								} else if (LiveTrafficStatus_miaoli_01[j][1]['CongestionLevel'] == '3') {
									polyline = L.polyline(SectionShape_miaoli_01[i][1], {
											color: "#ff0000",
											weight: 6
										})
										.bindPopup(`速度: ${LiveTrafficStatus_miaoli_01[j][1]['TravelSpeed']} 公里/小時, SectionID: ${LiveTrafficStatus_miaoli_01[j][1]['SectionID']}`);
									check_flag = true;
									SectionLiveTraffic_polyline_miaoli_01.addLayer(polyline);
								}

								break;

							}
						}




					} catch (error) {

					} finally {

					}
				}
				if (check_flag == true) {
					SectionLiveTraffic_polyline_miaoli_01.addTo(map);
				}
			}

			// --------------------------------0317新增----------------------------------------------
			function Add_SectionLiveTrafficPolyLine_dahu() {
				var check_flag = false;
				for (var i = 0; i < SectionShape_dahu.length; i++) {
					try {
						var polyline = '';
						for (var j = 0; j < LiveTrafficStatus_dahu.length; j++) {
							// 比對 ID (SectionShape_dahu[i][0] 是 ID)
							if (LiveTrafficStatus_dahu[j][0] == SectionShape_dahu[i][0]) {
								var info = LiveTrafficStatus_dahu[j][1];
								var lineColor = "#00ff00"; // 預設順暢

								if (info['CongestionLevel'] == '2') {
									lineColor = "#ffa500"; // 車多
								} else if (info['CongestionLevel'] == '3') {
									lineColor = "#ff0000"; // 壅塞
								}

								polyline = L.polyline(SectionShape_dahu[i][1], {
									color: lineColor,
									weight: 6,
									opacity: 0.8
								}).bindPopup(`大湖路段速度: ${info['TravelSpeed']} 公里/小時`);

								check_flag = true;
								SectionLiveTraffic_polyline_dahu.addLayer(polyline);
								break;
							}
						}
					} catch (error) {}
				}
				if (check_flag == true) {
					SectionLiveTraffic_polyline_dahu.addTo(map);
				}
			}

			function Remove_SectionLiveTrafficPolyLine_dahu() {
				SectionLiveTraffic_polyline_dahu.remove(map);
				SectionLiveTraffic_polyline_dahu = null;
				SectionLiveTraffic_polyline_dahu = L.layerGroup();
			}
			//------------------------------0317新增-------------------------------------------------


			function Remove_SectionLiveTrafficPolyLine_miaoli_01() {
				SectionLiveTraffic_polyline_miaoli_01.remove(map);
				SectionLiveTraffic_polyline_miaoli_01 = null;
				SectionLiveTraffic_polyline_miaoli_01 = L.layerGroup();
			}

			function add_cam_marker() {
				CamMarkers = L.markerClusterGroup();
				CamList.map(item => L.marker(new L.LatLng(item[0], item[1]), {
						icon: map_cam_icon
					}).on('click', function(e) {
						CamMarker_onClick(item)
					}))
					.forEach(item => CamMarkers.addLayer(item));

				// 2. 繪製公司案子這 17 個點
				CompanyCamList.map(item => L.marker(new L.LatLng(item[0], item[1]), {
					icon: company_cam_icon // 使用不同圖示區分
				}).on('click', function(e) {
					CamMarker_onClick(item);
				})).forEach(item => CamMarkers.addLayer(item));
				map.addLayer(CamMarkers);
			}

			function remove_cam_marker() {
				map.removeLayer(CamMarkers);
			}



			function add_weather_marker() {
				WeatherList.map(item => L.marker(new L.LatLng(item[0], item[1]), {
						icon: L.icon({
							iconUrl: item[2],
							iconSize: [32, 32],
						})
					}).addTo(map).bindPopup(item[4] + ' ' + item[5] + '<br>氣候:' + item[3]))
					.forEach(item => WeatherMarkers.push(item));
			}

			function remove_weather_marker() {
				for (i = 0; i < WeatherMarkers.length; i++) {
					map.removeLayer(WeatherMarkers[i]);
				}
				WeatherMarkers = [];
			}
			//=======================================================================
			function CamMarker_onClick(item) {
				document.getElementById('LiveCam_source').src = item[5];
				document.getElementById("LiveCam_source").setAttribute("data-src", item[5]);

				document.getElementById('LiveCam_name').innerHTML = item[2] + item[3] + item[4];
				document.getElementById('LiveCam').style.display = 'block';
			}
			//=======================================================================
			function init_MapLayerSetting() {
				/*
				document.getElementById("Layer_v01").checked = Layer_v01;
				document.getElementById("Layer_v02").checked = Layer_v02;
				document.getElementById("Layer_v03").checked = Layer_v03;
				document.getElementById("Layer_v04").checked = Layer_v04;
				document.getElementById("Layer_v06").checked = Layer_v06;
				document.getElementById("Layer_v07").checked = Layer_v07;
				document.getElementById("Layer_v08").checked = Layer_v08;
				*/
			}
			//=======================================================================
			$(function() {
				$(window).keydown(
					function(event) {
						if (event.keyCode == 27) {
							LiveCam_Close();
							parking_msg_Close();
							cms_msg_Close();
							// car_flow_msg_Close();
							car_flow_sensor_msg_Close();
							PanelManager.closeAll();
							resetLayerUI();
						}
					}
				);
			});
			//=======================================================================
			var defaultIconSize = 24; // 初始圖標大小

			// 創建 markers 並將它們存儲在 WeatherMarkers 中
			var WeatherMarkers3333 = [];



			var wait_ext_data1 = setInterval(
				function() {

					if (SectionShape_flag == true && SectionShape_miaoli_01_flag == true && LiveTrafficStatus_flag == true && LiveTrafficStatus_flag_miaoli_01 == true && parking_01_flag == true &&
						// ----------------------------------------------0317新增----------------------------------------------
						SectionShape_dahu_flag == true && LiveTrafficStatus_flag_dahu == true) {
						// ----------------------------------------------0317新增----------------------------------------------	

						if (getLocation_flag == false) {
							Layer_zoom = 13;
						}



						Add_SectionLiveTrafficPolyLine_freeway();
						Add_SectionLiveTrafficPolyLine_miaoli_01();

						// ----------------------------------------------0317新增----------------------------------------------
						Add_SectionLiveTrafficPolyLine_dahu();
						// ----------------------------------------------0317新增----------------------------------------------


						init_MapLayerSetting();
						//---------------------------------------------
						if (LayerSettingArray[0] == true) {
							add_cam_marker();
							document.getElementById("Layer_00").setAttribute("style", "background-color:orange;");
						} else {
							document.getElementById("Layer_00").setAttribute("style", "");
						}
						//---------------------------------------------
						if (LayerSettingArray[3] == true) {
							add_weather_marker();
							document.getElementById("Layer_03").setAttribute("style", "background-color:orange;");
						} else {
							document.getElementById("Layer_03").setAttribute("style", "");
						}
						//---------------------------------------------



						//---------------------------------------------

						// var WeatherList3333 = 
						// [
						//     [24.688002756720046, 120.92672514483105, "/img/21-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.688758211459856, 120.9254163577269, "/img/14-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.67984409926199, 120.95136133044104, "/img/18-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.67984409926199, 120.95066133044104, "/img/20-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.608347285200818, 121.00315765695609, "/img/23-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.605730576187323, 121.00158139144708, "/img/17-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.672432964228726, 120.95321620905766, "/img/22-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.671660140709204, 120.95352482678013, "/img/15-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.641058242676962, 120.97860495565766, "/img/24-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.638556633199757, 120.98107592935028, "/img/16-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.64349071802091, 120.98042521928762, "/img/19-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""],
						//     [24.645058242676962, 120.97860495565766, "/img/44-arrow_back_24dp_FF0000_FILL0_wght400_GRAD0_opsz24.png", 
						//     "", "", ""]
						// ];



						// WeatherList3333.map(item => {
						//     var marker = L.marker(new L.LatLng(item[0], item[1]), {
						//         icon: L.icon({
						//             iconUrl: item[2],
						//             iconSize: [defaultIconSize, defaultIconSize], // 初始大小
						//         })
						//     }).addTo(map);

						//     WeatherMarkers3333.push(marker);
						// });

						/*
						// 當地圖縮放時調整圖標大小
						map.on('zoomend', function() {
						    var currentZoom = map.getZoom(); // 獲取當前地圖縮放級別

						    var newSize = defaultIconSize * (currentZoom / 13); // 根據縮放級別動態調整大小（13 是基準縮放級別）

						    if (currentZoom >= 16)
						    {
						        
						    }
						    else
						    {
						        // newSize = 0;
						    }

						    // 遍歷每個 marker，更新其 icon 大小
						    WeatherMarkers3333.forEach(marker => {
						        var iconUrl = marker.options.icon.options.iconUrl; // 獲取當前 icon 的 URL
						        var newIcon = L.icon({
						            iconUrl: iconUrl,
						            iconSize: [newSize, newSize] // 設置新的大小
						        });
						        marker.setIcon(newIcon); // 更新 marker 的 icon
						    });
						});
						*/

						//---------------------------------------------
						for (i = 0; i < parking_01['data'].length; i++) {
							if (parking_01['data'][i]['number'] == 'NJP001') {
								parking_01_num = parking_01['data'][i]['empty_car'];
							}
						}
						document.getElementById('parking-01_1').textContent = parking_01_num;
						document.getElementById('parking-01_2').textContent = parking_01_num;
						//---------------------------------------------

						clearInterval(wait_ext_data1);
					}
				}, 250);





			var wait_ext_data2 = setInterval(
				function() {
					//----------------------------0317新增--------------------------------

					LiveTrafficStatus_flag_dahu = false;
					$.get(LiveTraffic_dahu_url + "?t=" + Math.random(), function(data, status) {
						if (status == "success") {
							Remove_SectionLiveTrafficPolyLine_dahu();
							LiveTrafficStatus_dahu = data;
							LiveTrafficStatus_flag_dahu = true;
							Add_SectionLiveTrafficPolyLine_dahu();
						}
					});
					//----------------------------0317新增--------------------------------
					LiveTrafficStatus_flag = false;
					$.get(LiveTraffic_url + "?t=" + Math.random(), function(data, status) {
						if (status == "success") {
							Remove_SectionLiveTrafficPolyLine_freeway();
							LiveTrafficStatus = data;
							LiveTrafficStatus_flag = true;
							Add_SectionLiveTrafficPolyLine_freeway();
						}
					});
					//----------------------------
					LiveTrafficStatus_flag_miaoli_01 = false;
					$.get(LiveTraffic_miaoli_01_url + "?t=" + Math.random(), function(data, status) {
						if (status == "success") {
							Remove_SectionLiveTrafficPolyLine_miaoli_01();
							LiveTrafficStatus_miaoli_01 = data;
							LiveTrafficStatus_flag_miaoli_01 = true;
							Add_SectionLiveTrafficPolyLine_miaoli_01();
						}
					});
					//----------------------------
					parking_01_flag = false;
					$.get(parking_01_url + "?t=" + Math.random(), function(data, status) {
						if (status == "success") {
							parking_01 = data;
							parking_01_flag = true;
							for (i = 0; i < parking_01['data'].length; i++) {
								if (parking_01['data'][i]['number'] == 'NJP001') {
									parking_01_num = parking_01['data'][i]['empty_car'];
								}
							}
							document.getElementById('parking-01_1').textContent = parking_01_num;
							document.getElementById('parking-01_2').textContent = parking_01_num;
						}
					});
					//---------------------------------------------
				}, 60000);
			//=======================================================================
			const PanelManager = {
				panels: ['car_flow_sensor_panel', 'parking_panel'],
				transitionMs: 360,

				open(panelId) {
					this.closeAll(false);
					document.getElementById(panelId)?.classList.add('open');
					this.resizeMap();
				},

				closeAll(doResize = true) {
					this.panels.forEach(id =>
						document.getElementById(id)?.classList.remove('open')
					);
					if (doResize) this.resizeMap();
				},

				resizeMap() {
					setTimeout(() => {
						if (window.map) map.invalidateSize();
					}, this.transitionMs);
				}
			};


			function resetLayerUI() {
				['Layer_05', 'Layer_07'].forEach(id => {
					const el = document.getElementById(id);
					if (el) el.setAttribute('style', '');
				});

				LayerSettingArray[5] = false;
				LayerSettingArray[7] = false;
			}
			// -------------------------------------------下拉選單測試-------------------------------------------------
			function toggleAccordion(id, btnElement) {
				// 1. 使用現代化 toggle 控制選單容器
				var x = document.getElementById(id);
				if (x) {
					x.classList.toggle("w3-show");
				}

				// 2. 直接操作傳入的按鈕元素
				if (btnElement) {
					btnElement.classList.toggle('is-open'); // 切換間距類別

					// 3. 處理按鈕內部的箭頭旋轉
					var icon = btnElement.querySelector('.nav-icon');
					if (icon) {
						icon.classList.toggle('icon-rotate');
					}
				}
			}

			// -------------------------------------------下拉選單測試-------------------------------------------------	
			// -------------------------------------------0318新增-------------------------------------------------
			// 1. 新增全圖影像切換函式
			function ShowFullImagePage(el) {
				// A. 清除所有選單的亮起狀態 (包含邊坡與交通監測)
				document.querySelectorAll('#SlopeMonitor a,#TrafficMonitor a, .sidebar-item , .sidebar-item a, #FullImageBtn').forEach(function(link) {
					link.classList.remove('active-slope-link');
				});

				// B. 幫當前點擊的按鈕加上亮起類別
				if (el) {
					el.classList.add('active-slope-link');
				}

				// C. 容器顯示切換：隱藏地圖與邊坡，顯示全圖影像
				document.getElementById('default_map_container').style.display = 'none';
				document.getElementById('slope_data_container').style.display = 'none';
				document.getElementById('full_image_container').style.display = 'block';

				// D. 如果有打開的圖層，可以視需求關閉
				// SettingMapLayer(0); // 視需求決定是否連動原本的圖層開關
			}

			// 2. 修改原本的 ShowSlopePage，確保它會隱藏「全圖影像容器」
			var originalShowSlopePage = ShowSlopePage;
			ShowSlopePage = function(targetId, el) {
				document.getElementById('full_image_container').style.display = 'none';
				originalShowSlopePage(targetId, el);
			};

			// 3. 修改原本的 ShowDefaultMap，確保它會隱藏「全圖影像容器」
			var originalShowDefaultMap = ShowDefaultMap;
			ShowDefaultMap = function(el) {
				document.getElementById('full_image_container').style.display = 'none';
				originalShowDefaultMap(el);
				if (window.map) map.invalidateSize();
			};
			// -------------------------------------------0318新增-------------------------------------------------
			// -------------------------------------------地圖切換測試-------------------------------------------------
			// 核心切換邏輯：修改後可接收 element 參數來處理亮起狀態
			function ShowSlopePage(targetId, el) {
				// 1. 清除所有邊坡監測選單的亮起狀態
				document.querySelectorAll('#SlopeMonitor a, #TrafficMonitor a, #FullImageBtn,.sidebar-item').forEach(function(link) {
					link.classList.remove('active-slope-link');
				});

				// 2. 幫當前點擊的元素加上亮起類別
				if (el) {
					el.classList.add('active-slope-link');
				}

				// 3. 原有的顯示邏輯
				document.getElementById('default_map_container').style.display = 'none';
				document.getElementById('slope_data_container').style.display = 'block';
				document.getElementById('full_image_container').style.display = 'none'; // 確保全圖隱藏

				var allIframes = document.querySelectorAll('#slope_data_container iframe');
				allIframes.forEach(function(ifrm) {
					ifrm.style.display = 'none';

				});

				var target = document.getElementById(targetId);
				if (target) {
					target.style.display = 'block';
				}
			}

			// 修改回首頁函式
			function ShowDefaultMap(el) {
				// 清除選單亮起狀態
				document.querySelectorAll('#SlopeMonitor a, #TrafficMonitor a, #FullImageBtn,.sidebar-item').forEach(function(link) {
					link.classList.remove('active-slope-link');
				});

				// 加上首頁亮起類別
				if (el) el.classList.add('active-slope-link');

				document.getElementById('default_map_container').style.display = 'block';
				document.getElementById('slope_data_container').style.display = 'none';
				document.getElementById('full_image_container').style.display = 'none'; // 確保全圖隱藏

				if (window.map) map.invalidateSize();
			}
			// -------------------------------------------地圖切換測試-------------------------------------------------
		</script>




</body>

</html>