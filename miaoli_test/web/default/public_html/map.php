<!DOCTYPE html>
<html>

<head>
    <title>苗栗路況監控系統</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
            width: 100%;
        }

        .leaflet-interactive {
            stroke-linejoin: round;
            stroke-linecap: round;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([24.773, 121.015], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // 新增：專門管理路況的圖層組
        var signageLayerGroup = L.layerGroup().addTo(map);

        const customSigns = [{
                name: "新竹系統-出口 A",
                pos: [24.753218, 120.984637],
                sid: "0062"
            },
            {
                name: "新竹系統-分流 B",
                pos: [24.754192, 120.985163],
                sid: "0061"
            },
            {
                name: "國一銜接處 C",
                pos: [24.756886, 120.989380],
                sid: "0227"
            },
            {
                name: "寶山交流道 D",
                pos: [24.756467, 120.990147],
                sid: "0228"
            },
            {
                name: "國一北上下交流處",
                pos: [24.758645, 120.988827],
                sid: "0060"
            },
            {
                name: "國一南上",
                pos: [24.758162, 120.988156],
                sid: "0059"
            },
            {
                name: "國三往西匯流處",
                pos: [24.758465, 120.983097],
                sid: "0229"
            },
            {
                name: "國三往東下交流處",
                pos: [24.757700, 120.978102],
                sid: "0230"
            }
        ];

        function updateTraffic() {
            const nocache = "?t=" + new Date().getTime();

            Promise.all([
                fetch('miaoli-01.json' + nocache).then(res => res.json()),
                fetch('miaoli-01-live-traffic.json' + nocache).then(res => res.json())
            ]).then(([coordsData, trafficData]) => {

                console.log("成功讀取座標總數:", coordsData.length);

                // 1. 建立座標對照表
                let coordsLookup = {};
                coordsData.forEach(item => {
                    coordsLookup[item[0].toString()] = item[1];
                });

                // 2. 清除舊線條與舊告示牌
                map.eachLayer(layer => {
                    if (layer instanceof L.Polyline) map.removeLayer(layer);
                });
                signageLayerGroup.clearLayers();

                // 建立一個臨時 Map 方便告示牌功能快速抓取路況
                let trafficLookup = new Map();

                // 3. 開始繪製原本的路網
                let drawCount = 0;
                trafficData.forEach(item => {
                    let sid = item[0].toString().trim();
                    let info = item[1];
                    trafficLookup.set(sid, info); // 存入 Lookup 供告示牌使用

                    let path = coordsLookup[sid];

                    if (path && path.length > 0) {
                        let lineColor = "#00ff00"; // 暢通
                        let statusText = "暢通";

                        if (info.CongestionLevel == "2") {
                            lineColor = "#ffff00"; // 車多
                            statusText = "車多";
                        } else if (info.CongestionLevel == "3") {
                            lineColor = "#ff0000"; // 壅塞
                            statusText = "壅塞";
                        }

                        // 建立線條
                        let line = L.polyline(path, {
                            color: lineColor,
                            weight: sid.length > 4 ? 8 : 6,
                            opacity: 0.9
                        }).addTo(map);

                        // 保留：滑鼠移入顯示時速 (Tooltip)
                        line.bindTooltip(
                            `<b>${sid.length > 4 ? '國道路段' : '在地路段'}</b><br>
                            <b>ID: ${sid}</b><br>
                            時速：<span style="font-size:1.2em; color:blue;">${info.TravelSpeed}</span> km/h<br>
                            狀態：${statusText}`, {
                                sticky: true,
                                direction: 'top',
                                opacity: 0.9
                            }

                            // `<b>ID: ${sid}</b><br>
                            //  時速：${info.TravelSpeed} km/h`



                        );
                        drawCount++;
                    }
                });

                // --- 告示牌處理邏輯 ---
                customSigns.forEach(sign => {
                    // 使用 .toString().trim() 確保與 JSON 裡的 ID 格式完全一致
                    let info = trafficLookup.get(sign.sid.toString().trim());

                    if (info) {
                        let speed = info.TravelSpeed;
                        let level = info.CongestionLevel;

                        // 判定顏色與文字
                        let statusColor = "#00e676"; // 預設綠色
                        let statusText = "順暢";

                        if (level == "3") {
                            statusColor = "#ff1744"; // 紅色
                            statusText = "壅塞";
                        } else if (level == "2") {
                            statusColor = "#ffea00"; // 黃色
                            statusText = "車多";
                        }

                        // 建立告示牌 Marker
                        L.marker(sign.pos).addTo(signageLayerGroup)
                            .bindTooltip(
                                `<div style="text-align:center; padding:5px;">
                    <b style="font-size:1.1em;">${sign.name}</b><br>
                    路況：<span style="color:${statusColor}; font-weight:bold;">${statusText}</span><br>
                    時速：<span style="font-size:1.2em; color:blue;">${speed}</span> km/h
                </div>`, {
                                    permanent: true, // 始終顯示文字
                                    direction: 'top',
                                    className: 'custom-sign-label', // 之後可以加 CSS 樣式
                                    opacity: 1,
                                    offset: [0, -10]
                                }
                            ).openTooltip();
                    } else {
                        console.warn(`找不到 ID: ${sign.sid} 的即時路況資料`);
                    }
                });

                console.log(`繪製完成：${drawCount} 條路段，${signageLayerGroup.getLayers().length} 個告示牌`);
            });
        }
        map.on('click', function(e) {
            let lat = e.latlng.lat;
            let lng = e.latlng.lng;

            // 找出距離點擊位置最近的 SID
            // 這裡需要遍歷 trafficData 查找對應 info
            console.log(`點擊座標: [${lat.toFixed(6)}, ${lng.toFixed(6)}]`);

            // 如果你已經有 trafficLookup (Map)，可以手動輸入 ID 測試
            // console.log("該區間資料:", trafficLookup.get("你的SID"));

        });

        updateTraffic();
        setInterval(updateTraffic, 60000); // 每分鐘自動刷新
    </script>
</body>

</html>