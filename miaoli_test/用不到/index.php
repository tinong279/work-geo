<!DOCTYPE html>
<html>

<head>
    <title>即時路況監控系統</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 800px;
        }
    </style>
</head>

<body>

    <div id="map"></div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([24.2, 120.7], 10); // 定位在台灣中部
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        function updateTraffic() {
            // 1. 同時抓取「即時數據」和「座標對照表」
            Promise.all([
                    fetch('miaoli-01-live-traffic.json').then(res => res.json()),
                    fetch('section_coords.json').then(res => res.json()) // 這支你要準備好
                ])
                .then(([trafficData, coordsData]) => {
                    // 先清除舊的線，避免地圖越來越重
                    map.eachLayer(layer => {
                        if (layer instanceof L.Polyline) map.removeLayer(layer);
                    });

                    trafficData.forEach(item => {
                        let sid = item[0]; // 取得 SectionID
                        let info = item[1]; // 取得時速與等級資訊

                        // 根據原本 PHP 算的 CongestionLevel 決定顏色
                        let color = "#00ff00";
                        if (info.CongestionLevel == "2") color = "#ffff00";
                        if (info.CongestionLevel == "3") color = "#ff0000";

                        // 如果對照表裡有座標，就畫出來
                        if (coordsData[sid]) {
                            L.polyline(coordsData[sid], {
                                color: color,
                                weight: 6,
                                opacity: 1
                            }).addTo(map).bindPopup(`路段: ${sid}<br>時速: ${info.TravelSpeed}`);
                        }
                    });
                });
        }

        updateTraffic();
        setInterval(updateTraffic, 60000); // 每分鐘自動刷新一次
    </script>
</body>

</html>