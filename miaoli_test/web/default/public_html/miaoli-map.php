<?php
/* =========================================
   miaoli-map.php
   功能：
   1. 用 PHP 管理初始設定
   2. 前端用 Leaflet 顯示地圖
   3. 讀取 board-controller.js 控制牌面邏輯
   ========================================= */

/* =========================================
   1. 告示牌設定
   之後如果你要改成從資料庫讀，
   就改這裡
   ========================================= */
$customSigns = [
    [
        'cms_id' => 1,
        'name' => '新竹系統-出口 A',
        'pos'  => [24.753218, 120.984637],
        'sid'  => '0062'
    ],
    [
        'cms_id' => 2,
        'name' => '新竹系統-分流 B',
        'pos'  => [24.754192, 120.985163],
        'sid'  => '0061'
    ],
    [
        'cms_id' => 3,
        'name' => '國一銜接處 C',
        'pos'  => [24.756886, 120.989380],
        'sid'  => '0227'
    ],
    [
        'cms_id' => 4,
        'name' => '寶山交流道 D',
        'pos'  => [24.756467, 120.990147],
        'sid'  => '0228'
    ],
    [
        'cms_id' => 5,
        'name' => '國一北上下交流處',
        'pos'  => [24.758645, 120.988827],
        'sid'  => '0060'
    ],
    [
        'cms_id' => 6,
        'name' => '國一南上',
        'pos'  => [24.758162, 120.988156],
        'sid'  => '0059'
    ],
    [
        'cms_id' => 7,
        'name' => '國三往西匯流處',
        'pos'  => [24.758465, 120.983097],
        'sid'  => '0229'
    ],
    [
        'cms_id' => 8,
        'name' => '國三往東下交流處',
        'pos'  => [24.757700, 120.978102],
        'sid'  => '0230'
    ]
];

/* =========================================
   2. 初始模式
   之後可以改成從資料庫撈 current_mode
   ========================================= */
$initialBoardMode = 'normal';
$initialIncidentMessage = '';
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <title>苗栗路況監控系統</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            margin: 0;
            font-family: Arial, "Microsoft JhengHei", sans-serif;
        }

        #map {
            height: 700px;
            width: 100%;
        }

        .leaflet-interactive {
            stroke-linejoin: round;
            stroke-linecap: round;
        }

        .control-panel {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.96);
            padding: 12px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            min-width: 300px;
        }

        .control-panel h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .control-panel .desc {
            font-size: 13px;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .control-panel .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .control-panel button {
            border: none;
            background: #1976d2;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .control-panel button:hover {
            opacity: 0.9;
        }

        .control-panel button.btn-normal {
            background: #2e7d32;
        }

        .control-panel button.btn-danger {
            background: #c62828;
        }

        .control-panel button.btn-warning {
            background: #ef6c00;
        }

        .mode-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            color: #fff;
            background: #2e7d32;
        }

        .mode-badge.incident {
            background: #c62828;
        }

        .custom-sign-label {
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .custom-sign-label .leaflet-tooltip-content {
            margin: 0;
            padding: 0;
        }

        .sign-board {
            min-width: 180px;
            text-align: center;
            background: #111;
            color: #fff;
            border: 3px solid #333;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35);
        }

        .sign-header {
            background: #222;
            padding: 6px 8px;
            font-size: 13px;
            font-weight: bold;
            border-bottom: 1px solid #444;
        }

        .sign-message {
            padding: 10px 8px 6px 8px;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .sign-footer {
            padding: 6px 8px 10px 8px;
            font-size: 12px;
            color: #ddd;
            line-height: 1.5;
        }

        .text-green {
            color: #00e676;
        }

        .text-yellow {
            color: #ffea00;
        }

        .text-red {
            color: #ff5252;
        }

        .text-orange {
            color: #ffb300;
        }
    </style>
</head>

<body>

    <div class="control-panel">
        <h3>現場牌面控制</h3>
        <div class="desc">
            一般模式：依車速自動顯示<br>
            事故模式：人工切換即時訊息
        </div>

        <div class="btn-group">
            <button class="btn-normal" onclick="switchToNormal()">一般模式</button>
            <button class="btn-danger" onclick="switchToIncident('前方有車禍')">事故：前方有車禍</button>
            <button class="btn-warning" onclick="switchToIncident('請小心駕駛')">事故：請小心駕駛</button>
        </div>

        <div id="modeBadge" class="mode-badge">目前模式：一般模式</div>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="board-controller.js"></script>

    <script>
        let lastCmsStatus = {};
        /* =========================================
           3. 把 PHP 資料丟給前端 JS
           ========================================= */
        const customSigns = <?= json_encode($customSigns, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const initialBoardMode = <?= json_encode($initialBoardMode, JSON_UNESCAPED_UNICODE) ?>;
        const initialIncidentMessage = <?= json_encode($initialIncidentMessage, JSON_UNESCAPED_UNICODE) ?>;

        /* =========================================
           4. 初始化 BoardController 狀態
           ========================================= */
        if (initialBoardMode === 'incident') {
            BoardController.setIncidentMode(initialIncidentMessage);
        } else {
            BoardController.setNormalMode();
        }

        /* =========================================
           5. 初始化地圖
           ========================================= */
        var map = L.map('map').setView([24.773, 121.015], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var roadLayerGroup = L.layerGroup().addTo(map);
        var signageLayerGroup = L.layerGroup().addTo(map);

        let lastCoordsLookup = {};
        let lastTrafficLookup = new Map();

        /* =========================================
           6. 路網顏色判斷
           ========================================= */
        function getRoadLineStatus(info) {
            let lineColor = '#00ff00';
            let statusText = '暢通';

            if (info.CongestionLevel == '2') {
                lineColor = '#ffff00';
                statusText = '車多';
            } else if (info.CongestionLevel == '3') {
                lineColor = '#ff0000';
                statusText = '壅塞';
            }

            return {
                lineColor,
                statusText
            };
        }

        /* =========================================
           7. 組裝牌面 HTML
           ========================================= */
        function buildSignboardHtml(sign, trafficInfo) {
            const cms = lastCmsStatus[String(sign.cms_id)] || null;
            const speed = Number(trafficInfo?.TravelSpeed || 0);

            // 手動模式：優先顯示資料庫內容
            if (cms && cms.current_mode === 'manual') {

                if (cms.display_type === 'image' && cms.image_path) {
                    return `
                <div class="sign-board">
                    <div class="sign-header">${cms.name || sign.name}</div>
                    <div class="sign-message" style="padding:8px;">
                        <img src="${cms.image_path}?t=${Date.now()}"
                             style="width:100%; max-height:180px; object-fit:contain; background:#000;">
                    </div>
                    <div class="sign-footer">
                        手動模式<br>
                        最後更新：${cms.last_updated || '-'}
                    </div>
                </div>
            `;
                }

                return `
            <div class="sign-board">
                <div class="sign-header">${cms.name || sign.name}</div>
                <div class="sign-message"
                     style="
                        color:${cms.text_color || '#c6ff00'};
                        font-size:${cms.text_size || 24}px;
                     ">
                    ${cms.text_content || ''}
                </div>
                <div class="sign-footer">
                    手動模式<br>
                    最後更新：${cms.last_updated || '-'}
                </div>
            </div>
        `;
            }

            // 非手動模式：走原本車速判斷
            const boardInfo = BoardController.getBoardDisplayInfo(speed);

            return `
        <div class="sign-board">
            <div class="sign-header">${sign.name}</div>
            <div class="sign-message ${boardInfo.textClass}">
                ${boardInfo.message}
            </div>
            <div class="sign-footer">
                路段 ID：${sign.sid}<br>
                即時時速：<span style="color:#4fc3f7; font-weight:bold;">${speed}</span> km/h
            </div>
        </div>
    `;
        }

        /* =========================================
           8. 畫路線
           ========================================= */
        function drawRoadLines(coordsLookup, trafficData) {
            roadLayerGroup.clearLayers();

            let drawCount = 0;

            trafficData.forEach(item => {
                let sid = item[0].toString().trim();
                let info = item[1];
                let path = coordsLookup[sid];

                if (path && path.length > 0) {
                    const roadStatus = getRoadLineStatus(info);

                    let line = L.polyline(path, {
                        color: roadStatus.lineColor,
                        weight: sid.length > 4 ? 8 : 6,
                        opacity: 0.9
                    }).addTo(roadLayerGroup);

                    line.bindTooltip(
                        `<b>${sid.length > 4 ? '國道路段' : '在地路段'}</b><br>
                         <b>ID: ${sid}</b><br>
                         時速：<span style="font-size:1.2em; color:blue;">${info.TravelSpeed}</span> km/h<br>
                         狀態：${roadStatus.statusText}`, {
                            sticky: true,
                            direction: 'top',
                            opacity: 0.9
                        }
                    );

                    drawCount++;
                }
            });

            return drawCount;
        }

        /* =========================================
           9. 畫牌面
           ========================================= */
        function drawSignboards() {
            signageLayerGroup.clearLayers();

            customSigns.forEach(sign => {
                const trafficInfo = lastTrafficLookup.get(sign.sid.toString().trim());

                if (!trafficInfo) {
                    console.warn(`找不到 SID: ${sign.sid} 的即時路況資料`);
                    return;
                }

                L.marker(sign.pos).addTo(signageLayerGroup)
                    .bindTooltip(
                        buildSignboardHtml(sign, trafficInfo), {
                            permanent: true,
                            direction: 'top',
                            className: 'custom-sign-label',
                            opacity: 1,
                            offset: [0, -10]
                        }
                    )
                    .openTooltip();
            });
        }

        /* =========================================
           10. 更新模式 badge
           ========================================= */
        function refreshModeBadge() {
            const badge = document.getElementById('modeBadge');
            const label = BoardController.getModeLabel();

            badge.textContent = '目前模式：' + label;

            if (BoardController.state.mode === 'incident') {
                badge.classList.add('incident');
            } else {
                badge.classList.remove('incident');
            }
        }

        /* =========================================
           11. 切換模式
           ========================================= */
        function switchToNormal() {
            BoardController.setNormalMode();
            refreshModeBadge();
            drawSignboards();
        }

        function switchToIncident(message) {
            BoardController.setIncidentMode(message);
            refreshModeBadge();
            drawSignboards();
        }

        /* =========================================
           12. 抓路況資料
           ========================================= */
        function updateTraffic() {

            const nocache = '?t=' + new Date().getTime();

            Promise.all([
                fetch('miaoli-01.json' + nocache).then(res => res.json()),
                fetch('miaoli-01-live-traffic.json' + nocache).then(res => res.json()),
                fetch('get_cms_status.php' + nocache).then(res => res.json())
            ]).then(([coordsData, trafficData, cmsStatusData]) => {
                console.log('cmsStatusData', cmsStatusData);
                let coordsLookup = {};
                coordsData.forEach(item => {
                    coordsLookup[item[0].toString()] = item[1];
                });

                let trafficLookup = new Map();
                trafficData.forEach(item => {
                    let sid = item[0].toString().trim();
                    let info = item[1];
                    trafficLookup.set(sid, info);
                });

                lastTrafficLookup = trafficLookup;
                lastCmsStatus = cmsStatusData || {};

                drawRoadLines(coordsLookup, trafficData);
                drawSignboards();

            }).catch(err => {
                console.error('讀取資料失敗：', err);
            });
        }

        /* =========================================
           13. 初始化
           ========================================= */
        refreshModeBadge();
        updateTraffic();
        setInterval(updateTraffic, 60000);
    </script>
</body>

</html>