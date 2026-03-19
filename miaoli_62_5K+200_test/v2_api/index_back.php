<?php
// index.php 
require_once 'db.php';           // 你原本的地基


// 抓取市電資料 (你原本的邏輯)
$stmt = $pdo->query("SELECT * FROM sensor_record WHERE device = 'utilityPower' ORDER BY trigger_time DESC LIMIT 1");
$latest_power = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>苗栗62線5K+200落石告警系統</title>
    <link rel="icon" type="image/svg+xml" href="https://fvfpvx68.geonerve-iot.com/miaoli_62_5K_200/public/img/hill-rockslide-solid.svg">
    <!-- <link rel="stylesheet" href="style.css"> -->

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Microsoft JhengHei", sans-serif;
            display: flex;
            flex-direction: column;
            /* 改為垂直排列，先放 Top Nav 再放內容 */
            height: 100vh;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

        }

        /* 頂部導航條 (Header) */
        .top-nav {
            height: 60px;
            flex-shrink: 0;
            background-color: #343a40;
            /* 接近原本系統的深灰色 */
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }

        .nav-title {
            font-size: 24px;
            font-weight: 500;
        }

        /* 下方容器：側邊欄 + 內容 */
        .container {
            display: flex;
            flex: 1;
            /* 自動填滿剩餘高度 */
        }

        /* 左側側邊欄 */
        .sidebar {
            width: 200px;
            background-color: #212529;
            /* 較深色背景 */
            color: white;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }


        .sidebar-menu a {
            color: #c2c7d0;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            font-size: 14px;
        }

        .sidebar-menu a .active {
            background-color: #f8f9fa;
            /* 選中狀態改為淺色背景 */
        }

        .sidebar-menu a .active {
            color: #333;
            /* 選中時文字變黑 */
        }

        /* 右側主內容區 */
        .main-content {
            flex: 1;
            background-color: #f4f6f9;
            padding: 20px;
        }

        .content-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        a {
            text-decoration: none;
            /* 移除底線 */
            color: inherit;
            /* 繼承父層的文字顏色 */
            cursor: pointer;
            /* 保持滑鼠移上去時顯示「手型」圖示 */
        }

        /* 讓四張圖變成 2x2 排列 */
        .video-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* 分成兩欄 */
            gap: 15px;
            /* 圖片間距 */
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .video-card-body img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 4px;
        }

        /* 卡片外框與陰影 */
        .monitor-card {
            background: #fff;
            border: 1px solid #d2d6de;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #f7f7f7;
            padding: 10px 15px;
            font-weight: bold;
            border-bottom: 1px solid #f4f4f4;
            font-size: 1.1em;
        }

        .card-content {
            padding: 15px;
        }

        /* 表格樣式 */
        .status-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            border: 1px solid #eee;
        }

        .status-table th {
            background-color: #f9f9f9;
            padding: 10px;
            border-bottom: 2px solid #ddd;
            color: #333;
        }

        .status-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            background-color: #f4f4f4;
            /* 模擬圖中的淺灰背景 */
        }

        /* 狀態圓點 */
        .dot {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        }

        .dot-green {
            background-color: #5cb85c;
        }

        .dot-red {
            background-color: #d9534f;
        }

        /* 模擬截圖中的開關按鈕 (Toggle Switch) */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
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
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(30px);
        }
    </style>
</head>

<body>
    <nav class="top-nav">
        <div class="nav-title">苗栗62線5K+200落石告警系統</div>
        <div class="nav-logout">
            <a href="#">
                <svg style="height:24px; width:24px; filter:invert(0%);" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
                    <path d="M512 128v256c0 53.02-42.98 96-96 96h-72C330.7 480 320 469.3 320 456c0-13.26 10.75-24 24-24H416c26.4 0 48-21.6 48-48V128c0-26.4-21.6-48-48-48h-72C330.7 80 320 69.25 320 56C320 42.74 330.7 32 344 32H416C469 32 512 74.98 512 128zM367.9 273.9L215.5 407.6C209.3 413.1 201.3 416 193.3 416c-4.688 0-9.406-.9687-13.84-2.969C167.6 407.7 160 396.1 160 383.3V328H40C17.94 328 0 310.1 0 288V224c0-22.06 17.94-40 40-40H160V128.7c0-12.75 7.625-24.41 19.41-29.72C191.5 93.56 205.7 95.69 215.5 104.4l152.4 133.6C373.1 242.6 376 249.1 376 256S373.1 269.4 367.9 273.9zM315.8 256L208 161.1V232h-160v48h160v70.03L315.8 256z">
                    </path>
                </svg>
            </a>
        </div>
    </nav>
    <div class="container">


        <div class="main-content">
            <h2 class="content-title">即時監測資料</h2>

            <div class="video-card" style="margin-top: 30px; border: 1px solid #d2d6de; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: #fff;">
                <div class="video-card-header" style="background-color: #f7f7f7; padding: 10px 10px; font-weight: bold; border-bottom: 1px solid #f4f4f4; font-size: 1.1em;">
                    <span class="icon">📍</span> 地圖
                </div>
                <div class="video-card-body" style="padding: 10px;">
                    <div id="map" style=" border: 1px solid #eee; border-radius: 4px;"></div>
                </div>
            </div>

            <div class="video-grid">

                <div class="video-card" style="border: 1px solid #d2d6de; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: #fff;">
                    <div class="video-card-header" style="background-color: #f7f7f7; padding: 10px 10px; font-weight: bold; border-bottom: 1px solid #f4f4f4; font-size: 1.1em;">
                        📷 攝影機 1 - 即時擷圖
                    </div>
                    <div class="video-card-body" style="padding: 10px;">
                        <div style=" border: 1px solid #eee; border-radius: 4px;"> <img id="img_cam1" src="loading.gif" alt="攝影機1即時影像" style="width:100%;"></div>
                    </div>
                </div>

                <div class="video-card" style="border: 1px solid #d2d6de; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: #fff;">
                    <div class="video-card-header" style="background-color: #f7f7f7; padding: 10px 10px; font-weight: bold; border-bottom: 1px solid #f4f4f4; font-size: 1.1em;">
                        📷 攝影機 2 - 即時擷圖
                    </div>
                    <div class="video-card-body" style="padding: 10px;">
                        <div style=" border: 1px solid #eee; border-radius: 4px;"> <img id="img_cam2" src="loading.gif" alt="攝影機2即時影像" style="width:100%;"></div>
                    </div>
                </div>
                <div class="video-card" style="border: 1px solid #d2d6de; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: #fff;">
                    <div class="video-card-header" style="background-color: #f7f7f7; padding: 10px 10px; font-weight: bold; border-bottom: 1px solid #f4f4f4; font-size: 1.1em;">
                        🚨 攝影機 1 - 警告擷圖
                    </div>
                    <div class="video-card-body" style="padding: 10px;">
                        <div style=" border: 1px solid #eee; border-radius: 4px;"> <img id="img_alert1" src="loading.gif" alt="攝影機1即時影像" style="width:100%;"></div>
                    </div>
                </div>
                <div class="video-card" style="border: 1px solid #d2d6de; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: #fff;">
                    <div class="video-card-header" style="background-color: #f7f7f7; padding: 10px 10px; font-weight: bold; border-bottom: 1px solid #f4f4f4; font-size: 1.1em;">
                        🚨 攝影機 2 - 警告擷圖
                    </div>
                    <div class="video-card-body" style="padding: 10px;">
                        <div style=" border: 1px solid #eee; border-radius: 4px;"> <img id="img_alert2" src="loading.gif" alt="攝影機2即時影像" style="width:100%;"></div>
                    </div>
                </div>




                <!-- <div class="video-card">
                    <div class="video-card-header">📷 攝影機 2 - 即時擷圖</div>
                    <div class="video-card-body">
                        <img id="img_cam2" src="loading.gif" alt="攝影機2即時影像" style="width:100%;">
                    </div>
                </div>

                <div class="video-card">
                    <div class="video-card-header">🚨 攝影機 1 - 警告擷圖</div>
                    <div class="video-card-body">
                        <img id="img_alert1" src="loading.gif" alt="攝影機1警告影像" style="width:100%;">
                    </div>
                </div>

                <div class="video-card">
                    <div class="video-card-header">🚨 攝影機 2 - 警告擷圖</div>
                    <div class="video-card-body">
                        <img id="img_alert2" src="loading.gif" alt="攝影機2警告影像" style="width:100%;">
                    </div>
                </div> -->
            </div>

            <div class="status-container" style="margin-top: 20px;">

                <div class="monitor-card">
                    <div class="card-header"><span class="icon">⚡</span> 市電監測</div>
                    <div class="card-content">
                        <table class="status-table">
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
                                    <td id="power-time">2026-01-29 17:20:03</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="monitor-card">
                    <div class="card-header"><span class="icon">📟</span> 電池 詳細資料</div>
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

                <div class="monitor-card">
                    <div class="card-header"><span class="icon">⚙️</span> 牌面控制</div>
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
                                    <td>
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                            <span>關閉</span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="cms-switch">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>


    </div>

    <script>
        // --- 1. 地圖初始化邏輯 ---
        <?php
        $lat = $latest_power['lat'] ?? 24.457632;
        $lng = $latest_power['lng'] ?? 120.913251;
        ?>
        var lat = <?php echo $lat; ?>;
        var lng = <?php echo $lng; ?>;
        var map = L.map('map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker = L.marker([lat, lng]).addTo(map)
            .bindPopup('苗栗62線5K+200')
            .openPopup();

        // --- 2. 影像更新邏輯 (維持你原本的) ---
        function refreshImages() {
            fetch('fetch_images.php?nocache=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    const t = new Date().getTime();
                    const updateImg = (id, newSrc) => {
                        if (newSrc) {
                            const img = document.getElementById(id);
                            const separator = newSrc.includes('?') ? '&' : '?';
                            img.src = newSrc + separator + 't=' + t;
                        }
                    };
                    if (data.cam1) updateImg('img_cam1', data.cam1);
                    if (data.cam2) updateImg('img_cam2', data.cam2);
                    if (data.alert1) updateImg('img_alert1', data.alert1);
                    if (data.alert2) updateImg('img_alert2', data.alert2);
                    console.log("影像資料已同步: " + new Date().toLocaleTimeString());
                })
                .catch(err => console.error('影像更新失敗:', err));
        }

        // --- 3. 硬體狀態更新邏輯 (市電、電池、牌面) ---
        function refreshStatus() {
            // 呼叫我們之前討論的 fetch_status.php
            fetch('fetch_status.php?nocache=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    // 更新市電燈號與文字
                    const pLight = document.getElementById('power-light');
                    const pStatus = document.getElementById('power-status');
                    pStatus.innerText = data.power.status;
                    pStatus.style.color = (data.power.status === '正常') ? 'green' : 'red';
                    pLight.style.backgroundColor = (data.power.status === '正常') ? '#2ecc71' : '#e74c3c';
                    document.getElementById('power-time').innerText = data.power.time;

                    // 更新電池電壓
                    document.getElementById('battery-voltage').innerText = data.battery.voltage;
                    document.getElementById('battery-time').innerText = data.battery.time;

                    // 更新牌面 Switch 狀態 (反映 Python 讀取到的 Modbus 狀態)
                    const cmsSwitch = document.getElementById('cms-switch');
                    const cmsText = document.getElementById('cms-text');
                    cmsSwitch.checked = (data.cms.status === 'ON');
                    cmsText.innerText = (data.cms.status === 'ON') ? '開啟' : '關閉';
                })
                .catch(err => console.error('硬體狀態更新失敗:', err));
        }

        // --- 4. 牌面控制邏輯 (點擊開關時寫入資料庫) ---
        document.getElementById('cms-switch').addEventListener('change', function() {
            const isChecked = this.checked;
            const status = isChecked ? 1 : 0;

            // 發送指令到 update_cms.php，讓 PHP 更改資料庫中的牌面狀態
            fetch('update_cms.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'status=' + status
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        console.log("牌面指令已送出: " + (isChecked ? "開啟" : "關閉"));
                    }
                });
        });

        // --- 5. 定時執行排程 ---
        refreshImages(); // 初始化執行影像
        refreshStatus(); // 初始化執行狀態

        setInterval(refreshImages, 60000); // 影像 60 秒更新一次
        setInterval(refreshStatus, 10000); // 電力/牌面 10 秒更新一次 (對應 Python 循環頻率)

        // 切換至查詢分頁
        function loadQueryPage() {
            fetch('query_page.php')
                .then(response => response.text())
                .then(html => {
                    document.querySelector('.main-content').innerHTML = html;

                    // 重要：載入內容後，立即執行初始化
                    initDateTimePicker();
                });
        }

        // 封裝初始化邏輯
        function initDateTimePicker() {
            flatpickr(".datetimepicker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                // 設定點擊 input 時開啟
                allowInput: true
            });
        }

        let myChart = null; // 用於儲存圖表實例，避免重複渲染

        function executeSearch() {
            const device = document.getElementById('q-device').value;
            const start = document.getElementById('q-start').value;
            const end = document.getElementById('q-end').value;
            const statusLabel = document.getElementById('chart-status');
            const canvas = document.getElementById('historyChart');

            statusLabel.innerText = "資料讀取中...";

            fetch(`fetch_history.php?type=${device}&start=${start}&end=${end}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        statusLabel.innerText = "此區間查無資料";
                        canvas.style.display = 'none';
                        return;
                    }

                    statusLabel.style.display = 'none';
                    canvas.style.display = 'block';

                    const labels = data.map(item => item.trigger_time);
                    // 處理數值：如果是市電(utilityPower)，將 'ON' 轉為 1, 'OFF' 轉為 0
                    const values = data.map(item => {
                        if (device === 'utilityPower') {
                            // 只要 value 是 "1" 或 1，就視為正常 (1)
                            return (item.value == "1" || item.value == 1) ? 1 : 0;
                        }
                        return parseFloat(item.value);
                    });

                    if (myChart) myChart.destroy(); // 銷毀舊圖表

                    const ctx = canvas.getContext('2d');
                    myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: (device === 'utilityPower' ? '市電狀態' : '電池電壓(V)'),
                                data: values,
                                borderColor: '#4bc0c0', // 綠色線條
                                backgroundColor: 'rgba(255, 255, 255, 0)',
                                stepped: true, // 關鍵：設定為階梯狀圖
                                borderWidth: 2,
                                pointRadius: 2,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    // 如果是市電，設定刻度為 0 和 1 (對應 異常/正常)
                                    ticks: {
                                        callback: function(value) {
                                            if (device === 'utilityPower') {
                                                return value === 1 ? '正常' : (value === 0 ? '異常' : '');
                                            }
                                            return value + ' V';
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
        }

        // 初始化開始時間與結束時間的選擇器
        flatpickr(".datetimepicker", {
            enableTime: true, // 開啟時間選擇
            dateFormat: "Y-m-d H:i", // 設定格式為 2026-01-30 00:00
            time_24hr: true, // 使用 24 小時制
            locale: {
                firstDayOfWeek: 0,
                weekdays: {
                    shorthand: ["週日", "週一", "週二", "週三", "週四", "週五", "週六"],
                    longhand: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"]
                },
                months: {
                    shorthand: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                    longhand: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"]
                }
            }
        });
    </script>
</body>

</html>