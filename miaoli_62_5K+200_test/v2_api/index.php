<?php
// slope_5k200.php
require_once 'db.php';
require("./resources.php");

// 抓取市電資料
$stmt = $pdo->query("SELECT * FROM sensor_record WHERE device = 'utilityPower' ORDER BY trigger_time DESC LIMIT 1");
$latest_power = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="zh-tw">

<head>
    <title>苗栗62線 5K+200 邊坡即時監測數據</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php get_css_js_link() ?>
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> -->
    <!-- <link rel="stylesheet" href="/css/w3-4.15.css" /> -->
    <!-- <script src="/js/jquery-3.6.0.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
    <!-- <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

    <style>
        /* 套用 2.1K 頁面的核心深色樣式 */
        body {
            margin: 0;
            padding: 0;
            font-family: Verdana, sans-serif;
            background-color: #292424;
            color: #cccccc;
        }

        .main-content {
            padding: 12px 24px;
            background-color: #292424;
        }

        #map {
            height: 60vh;
            border-radius: 12px;
            margin-top: 10px;
            border: 1px solid rgba(119, 124, 124, 0.2);
        }

        /* 表格樣式統一 */
        table.w3-table-all,
        table {
            background-color: rgba(38, 40, 40, 1) !important;
            color: #cccccc !important;
            margin-top: 14px;
            margin-bottom: 14px;
        }

        table th {
            background-color: #333333 !important;
            color: #ffffff !important;
            border: 1px solid #444 !important;
            text-align: center;
            vertical-align: middle;
        }

        table td {
            border: 1px solid #444 !important;
            text-align: center;
            vertical-align: middle;
            background-color: rgba(38, 40, 40, 1) !important;
        }

        .picture-container {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }

        .photo-item {
            flex: 1;
            min-width: 45%;
            padding: 8px;
        }

        .photo-item img {
            width: 100%;
            border-radius: 8px;
            border: 1px solid rgba(119, 124, 124, 0.3);
        }

        /* 狀態圓點 */
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .dot-green {
            background-color: #2ecc71;
        }

        .dot-red {
            background-color: #e74c3c;
        }

        /* 開關樣式 (維持功能但調整顏色) */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            display: none;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #555;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .status-table {
            width: 100%;
        }

        /* 外層容器：深色背景與圓角 */
        .main-card {
            background-color: rgba(38, 40, 40, 1);
            border: 1px solid rgba(119, 124, 124, 0.2);
            border-radius: 12px;
            padding: 10px 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            /* margin-top: 20px; */
        }

        /* 標題欄位：帶有下橫線的樣式 */
        .main-map-header {
            color: #ffffff;
            border-bottom: 1px solid rgba(119, 124, 124, 0.3);
            padding: 12px 24px;
            /* display: flex;
            align-items: center;
            font-weight: bold; */
        }

        /* 表格本體：移除白色背景，改為深色 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            margin-bottom: 14px;
            background-color: transparent !important;
        }

        /* 表格標頭：深灰色背景 */
        table th {
            background-color: #262828 !important;
            color: #ffffff !important;
            border: 1px solid #cccccc !important;
            text-align: center;
            padding: 8px;
        }

        /* 表格內容：深色背景、灰色文字與框線 */
        table td {
            background-color: rgba(38, 40, 40, 1) !important;
            color: #cccccc !important;
            border: 1px solid #cccccc !important;
            text-align: center;
            padding: 12px;
        }

        /* 狀態圓點 */
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-green {
            background-color: #2ecc71;
        }

        .footer-content {
            padding-top: 32px;
            padding-bottom: 32px;
            margin-top: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.2), 0 4px 20px 0 rgba(0, 0, 0, 0.19);
        }
    </style>
</head>

<body>

    <div class="main-content">
        <div class="main-card">
            <h1 style="color: #ffffff; ">苗栗62線 5K+200 邊坡即時監測數據</h1>
        </div>

        <div class="main-card" style="margin-top:10px;">
            <header class="main-map-header">
                <svg style="height:16px;" fill="currentColor" viewBox="0 0 576 512">
                    <path d="M288 0c-69.59 0-126 56.41-126 126 0 56.26 82.35 158.8 113.9 196.02 6.39 7.54 17.82 7.54 24.2 0C331.65 284.8 414 182.26 414 126 414 56.41 357.59 0 288 0zm0 168c-23.2 0-42-18.8-42-42s18.8-42 42-42 42 18.8 42 42-18.8 42-42 42zM20.12 215.95A32.006 32.006 0 0 0 0 245.66v250.32c0 11.32 11.43 19.06 21.94 14.86L160 448V214.92c-8.84-15.98-16.07-31.54-21.25-46.42L20.12 215.95zM288 359.67c-14.07 0-27.38-6.18-36.51-16.96-19.66-23.2-40.57-49.62-59.49-76.72v182l192 64V266c-18.92 27.09-39.82 53.52-59.49 76.72-9.13 10.77-22.44 16.95-36.51 16.95zm266.06-198.51L416 224v288l139.88-55.95A31.996 31.996 0 0 0 576 426.34V176.02c0-11.32-11.43-19.06-21.94-14.86z"></path>
                </svg>
                <span style="font-size:16px;margin-left:4px;">地圖</span>
            </header>
            <div id="map"></div>
        </div>

        <div class="main-card" style="margin-top:20px;">
            <header class="main-map-header">
                <svg style="height:16px; margin-right:8px;" fill="currentColor" viewBox="0 0 512 512">
                    <path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
                </svg>
                <span>即時擷圖</span>
            </header>
            <div class="picture-container">
                <div class="photo-item"><img id="img_cam1" src="loading.gif" alt="Cam 1"></div>
                <div class="photo-item"><img id="img_cam2" src="loading.gif" alt="Cam 2"></div>
                <!-- <div class="photo-item"><img id="img_alert1" src="loading.gif" alt="Alert 1"></div>
                <div class="photo-item"><img id="img_alert2" src="loading.gif" alt="Alert 2"></div> -->
            </div>
        </div>
        <div class="main-card" style="margin-top:20px;">
            <header class="main-map-header">
                <svg style="height:16px; margin-right:8px;" fill="currentColor" viewBox="0 0 512 512">
                    <path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
                </svg>
                <span>警告擷圖</span>
            </header>
            <div class="picture-container">
                <!-- <div class="photo-item"><img id="img_cam1" src="loading.gif" alt="Cam 1"></div>
                <div class="photo-item"><img id="img_cam2" src="loading.gif" alt="Cam 2"></div> -->
                <div class="photo-item"><img id="img_alert1" src="loading.gif" alt="Alert 1"></div>
                <div class="photo-item"><img id="img_alert2" src="loading.gif" alt="Alert 2"></div>
            </div>
        </div>
        <div class="main-card" style="margin-top:20px;">
            <header class="main-map-header">
                <svg style="height:16px; margin-right:8px;" fill="currentColor" viewBox="0 0 512 512">
                    <path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
                </svg>
                <span style="font-size:16px;">市電監測</span>
            </header>

            <div style="overflow-x:auto;">
                <table class="w3-table-all">
                    <thead>
                        <tr>
                            <th style="width: 50%;">狀態</th>
                            <th style="width: 50%;">時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div id="power-light" class="dot dot-green"></div>
                                <span id="power-status">正常</span>
                            </td>
                            <td id="power-time">2026-02-04 11:03:35</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="main-card" style="margin-top:20px;">
            <div class="status-container" style="margin-top: 20px;">

                <div class="monitor-card">
                    <header class="main-map-header">
                        <svg style="height:16px; margin-right:8px;" fill="currentColor" viewBox="0 0 512 512">
                            <path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
                        </svg>
                        <span style="font-size:16px;font-weight: bold;">電池 詳細資料</span>
                    </header>
                    <div class="card-content">
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">設備</th>
                                    <th style="width: 25%;">燈號狀態</th>
                                    <th style="width: 25%;">電池電壓(V)</th>
                                    <th style="width: 25%;">時間</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>電池電壓</td>
                                    <td>
                                        <div id="battery-light" class="dot dot-green"></div>
                                    </td>
                                    <td id="battery-voltage" style="font-weight: bold;">14.115</td>
                                    <td id="battery-time">2026-01-29 17:20:03</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-card" style="margin-top:20px;">
            <div class="status-container" style="margin-top: 20px;">

                <header class="main-map-header">
                    <span class="icon">⚙️</span>
                    <span style="font-size:16px;font-weight: bold;">牌面控制</span>
                </header>

                <div class="card-content">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">項目</th>
                                <th style="width: 50%;">狀態</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>告警牌面</td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <img id="cms-status-img"
                                        src="img/off1.png"
                                        alt="開關狀態"
                                        style="cursor: pointer; width: 80px; transition: 0.3s;"
                                        onclick="openCmsModal()">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <footer class="footer-content w3-center w3-opacity" style="color: #888;">
            Copyright © 基能科技股份有限公司
        </footer>
    </div>
    <div id="cms-control-modal" class="w3-modal" style="display:none; z-index: 1000;">
        <div class="w3-modal-content w3-animate-zoom" style="max-width: 450px; background-color: transparent;">

            <div class="w3-bar w3-blue">
                <a class="w3-bar-item w3-padding-8" style="font-size:20px; vertical-align:middle; text-decoration: none;">告警牌面</a>
                <a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px; text-decoration: none;" onclick="document.getElementById('cms-control-modal').style.display='none'">×</a>
            </div>

            <div class="w3-center w3-padding w3-light-gray" style="width:100%;">

                <button class="w3-btn w3-round w3-orange" style="margin:10px; color: white !important;" onclick="sendCmsCommand(1);">開啟牌面</button>
                <button class="w3-btn w3-round w3-pink" style="margin:10px; color: white !important;" onclick="sendCmsCommand(0);">關閉牌面</button>

            </div>

        </div>
    </div>
    <script>
        // 1. 初始化座標與地圖
        var lat = <?php echo $latest_power['lat'] ?? 24.457632; ?>;
        var lng = <?php echo $latest_power['lng'] ?? 120.913251; ?>;
        var map = L.map('map').setView([lat, lng], 16);

        // 2. 載入底圖 (NLSC)
        L.tileLayer('https://wmts.nlsc.gov.tw/wmts/EMAP/default/GoogleMapsCompatible/{z}/{y}/{x}', {
            attribution: '© NLSC'
        }).addTo(map);

        // 3. 定義自定義標記圖示 (比照 2.1K)
        const marker_icon = L.icon({
            iconUrl: 'img/map-marker.png', // 確保圖檔路徑正確
            iconSize: [48, 48],
            iconAnchor: [24, 48] // 錨點設定在圖片底部中心
        });

        // 4. 建立標記並綁定點擊事件
        L.marker([lat, lng], {
                icon: marker_icon,
                zIndexOffset: 5
            })
            .addTo(map)
            .on('click', function(e) {
                // 觸發點擊後的彈窗函式
                Marker_onClick('locpic_01');
            });

        // 5. 定義點擊觸發彈窗的函式
        function Marker_onClick(obj_id) {
            var modal = document.getElementById(obj_id);
            if (modal) {
                modal.style.display = 'block';
            } else {
                console.error("找不到元件：" + obj_id);
            }
        }
        // 影像與狀態更新邏輯 (維持 10-60秒 更新頻率)
        // 影像與狀態更新邏輯
        function refreshImages() {
            // 增加 nocache 確保每次都跟伺服器抓最新的圖片清單 JSON
            fetch('fetch_images.php?nocache=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    const t = new Date().getTime();

                    // 1. 處理即時擷圖 (Cam1 & Cam2) -> 走 Proxy，維持 ?t= 機制避免快取
                    if (data.cam1) {
                        const connector = data.cam1.includes('?') ? '&' : '?';
                        document.getElementById('img_cam1').src = data.cam1 + connector + 't=' + t;
                    }
                    if (data.cam2) {
                        const connector = data.cam2.includes('?') ? '&' : '?';
                        document.getElementById('img_cam2').src = data.cam2 + connector + 't=' + t;
                    }

                    // 2. 處理警告擷圖 (Alert1 & Alert2) -> 走舊伺服器絕對網址
                    // 重要：移除 + '?t=' + t，避免 Cloudflare 報錯導致破圖
                    if (data.alert1) {
                        document.getElementById('img_alert1').src = data.alert1;
                    }
                    if (data.alert2) {
                        document.getElementById('img_alert2').src = data.alert2;
                    }
                })
                .catch(err => console.error("影像更新失敗:", err));
        }

        function refreshStatus() {
            fetch('fetch_status.php?nocache=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    // 市電處理
                    const pLight = document.getElementById('power-light');
                    if (pLight) {
                        pLight.className = 'dot ' + (data.power.status === '正常' ? 'dot-green' : 'dot-red');
                    }
                    if (document.getElementById('power-status'))
                        document.getElementById('power-status').innerText = data.power.status;

                    if (document.getElementById('power-time'))
                        document.getElementById('power-time').innerText = data.power.time;

                    // 電池處理
                    if (document.getElementById('battery-voltage'))
                        document.getElementById('battery-voltage').innerText = data.battery.voltage;

                    if (document.getElementById('battery-time'))
                        document.getElementById('battery-time').innerText = data.battery.time;


                    if (data.cms) {
                        const imgEl = document.getElementById('cms-status-img');
                        if (imgEl) {
                            // 如果抓到的狀態是 ON，顯示 ON 的圖片；否則顯示 OFF 的圖片
                            imgEl.src = (data.cms.status === 'ON') ? IMG_ON_SRC : IMG_OFF_SRC;
                        }
                    }
                })
                .catch(err => console.error("狀態更新失敗:", err));
        }
        // 1. 定義你的圖片路徑 (請填入正確的檔名與路徑)
        const IMG_ON_SRC = 'img/on1.png'; // 綠色 ON 圖片
        const IMG_OFF_SRC = 'img/off1.png'; // 灰色 OFF 圖片

        // 2. 開啟彈窗函式
        function openCmsModal() {
            document.getElementById('cms-control-modal').style.display = 'block';
        }

        // 3. 發送控制指令與切換圖片函式
        function sendCmsCommand(statusVal) {
            // 點擊後先關閉彈窗，讓使用者感覺反應很快
            document.getElementById('cms-control-modal').style.display = 'none';

            // 呼叫後端 PHP 發送 Modbus
            fetch('update_cms.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'status=' + statusVal
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "ok") {
                        // 如果後端回傳 ok，立刻切換圖片
                        const imgEl = document.getElementById('cms-status-img');
                        if (imgEl) {
                            imgEl.src = (statusVal === 1) ? IMG_ON_SRC : IMG_OFF_SRC;
                        }
                        console.log("硬體更新成功，狀態: " + (statusVal === 1 ? "ON" : "OFF"));
                    } else {
                        alert("控制失敗，請檢查設備連線狀態");
                    }
                })
                .catch(err => {
                    console.error("網路錯誤:", err);
                    alert("網路連線異常");
                });
        }


        setInterval(refreshImages, 5000);
        setInterval(refreshStatus, 10000);
        refreshImages();
        refreshStatus();
    </script>
</body>

</html>