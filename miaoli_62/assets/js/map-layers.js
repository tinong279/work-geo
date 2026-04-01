/**
 * 地圖圖層控制
 */

let radarLayer = null;
let rainfallLayer = null;
let weatherMarkers = [];
let trafficLayerGroup = null;
let cameraMarkers = null;
let updateIntervals = {};

// 地理邊界
const RADAR_BOUNDS = L.latLngBounds([20.473, 118.000], [26.473, 124.000]);
const RAINFALL_BOUNDS = L.latLngBounds([21.505, 119.188], [25.920, 123.588]);

// 路況顏色
const TRAFFIC_COLORS = { 1: '#00ff00', 2: '#ffff00', 3: '#ffa500', 4: '#ff0000', 5: '#ff00ff' };
const TRAFFIC_LEVEL_TEXT = { 1: '順暢', 2: '緩慢', 3: '壅塞', 4: '嚴重壅塞', 5: '封閉' };

// ========== 通用圖層切換函數 ==========
async function toggleImageLayer(layerName, apiUrl, bounds, legendId) {
    const btn = document.getElementById(`${layerName}Toggle`);
    const legend = document.getElementById(legendId);
    const layerVar = layerName === 'radar' ? 'radarLayer' : 'rainfallLayer';

    // 關閉圖層
    if (window[layerVar]) {
        map.removeLayer(window[layerVar]);
        window[layerVar] = null;
        btn.classList.remove('active');
        if (legend) {
            legend.style.display = 'none';
            adjustLegendPositions();
        }
        return;
    }

    // 開啟圖層
    btn.classList.add('loading');

    try {
        const response = await fetch(apiUrl);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || '無法取得資料');
        }

        window[layerVar] = L.imageOverlay(result.imageUrl, bounds, {
            opacity: 0.6,
            interactive: true
        }).addTo(map);

        btn.classList.remove('loading');
        btn.classList.add('active');

        if (legend) {
            legend.style.display = 'block';
            adjustLegendPositions();
        }
    } catch (error) {
        console.error(`載入${layerName}失敗:`, error);
        alert(`載入失敗：${error.message}`);
        btn.classList.remove('loading');
    }
}

// ========== 雷達迴波圖層 ==========
async function toggleRadarLayer() {
    await toggleImageLayer('radar', 'api/get_radar_image.php', RADAR_BOUNDS, 'radarLegend');
}

// ========== 累積雨量圖層 ==========
async function toggleRainfallLayer() {
    await toggleImageLayer('rainfall', 'api/get_rainfall_image.php', RAINFALL_BOUNDS, 'rainfallLegend');
}

// ========== 天氣資訊圖層 ==========
async function toggleWeatherLayer() {
    const btn = document.getElementById('weatherToggle');

    if (weatherMarkers.length > 0) {
        weatherMarkers.forEach(marker => map.removeLayer(marker));
        weatherMarkers = [];
        btn.classList.remove('active');
        return;
    }

    btn.classList.add('loading');

    try {
        const response = await fetch('api/get_weather_data.php');
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || '無法取得天氣資料');
        }

        result.data.forEach(station => {
            const marker = L.marker([station.lat, station.lon], {
                icon: L.icon({
                    iconUrl: station.icon,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -16]
                })
            }).bindPopup(`
                <div style="min-width: 150px;">
                    <h4 style="margin: 0 0 8px 0;">${station.name}</h4>
                    <p style="margin: 4px 0;"><strong>天氣：</strong>${station.weather}</p>
                </div>
            `).addTo(map);

            weatherMarkers.push(marker);
        });

        btn.classList.remove('loading');
        btn.classList.add('active');
    } catch (error) {
        console.error('載入天氣資訊失敗:', error);
        alert('載入天氣資訊失敗：' + error.message);
        btn.classList.remove('loading');
    }
}

// ========== 圖例位置調整 ==========
function adjustLegendPositions() {
    const radarLegend = document.getElementById('radarLegend');
    const rainfallLegend = document.getElementById('rainfallLegend');

    const radarVisible = radarLegend && radarLegend.style.display !== 'none';
    const rainfallVisible = rainfallLegend && rainfallLegend.style.display !== 'none';

    if (radarVisible && rainfallVisible) {
        radarLegend.style.setProperty('top', '15px', 'important');
        radarLegend.style.setProperty('right', '15px', 'important');
        radarLegend.style.setProperty('left', 'auto', 'important');
        rainfallLegend.style.setProperty('top', '15px', 'important');
        rainfallLegend.style.setProperty('right', '95px', 'important');
        rainfallLegend.style.setProperty('left', 'auto', 'important');
    } else if (radarVisible) {
        radarLegend.style.setProperty('top', '15px', 'important');
        radarLegend.style.setProperty('right', '15px', 'important');
        radarLegend.style.setProperty('left', 'auto', 'important');
    } else if (rainfallVisible) {
        rainfallLegend.style.setProperty('top', '15px', 'important');
        rainfallLegend.style.setProperty('right', '15px', 'important');
        rainfallLegend.style.setProperty('left', 'auto', 'important');
    }
}

// ========== 即時路況圖層 ==========
async function loadLiveTraffic() {
    try {
        const response = await fetch('api/get_live_traffic.php');

        // 檢查 HTTP 狀態
        if (!response.ok) {
            console.warn(`即時路況 API 返回錯誤: ${response.status}`);
            return;
        }

        const text = await response.text();

        // 檢查是否為有效 JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.warn('即時路況 API 返回非 JSON 格式:', text.substring(0, 200));
            return;
        }

        if (!result.success || !result.data || result.data.length === 0) {
            console.warn('即時路況無資料');
            return;
        }

        if (trafficLayerGroup) map.removeLayer(trafficLayerGroup);
        trafficLayerGroup = L.layerGroup();

        result.data.forEach(section => {
            const color = TRAFFIC_COLORS[section.congestionLevel] || '#808080';
            const levelText = TRAFFIC_LEVEL_TEXT[section.congestionLevel] || '未知';

            L.polyline(section.coordinates, {
                color: color,
                weight: 6,
                opacity: 0.7
            }).bindPopup(`
                <div style="min-width: 150px;">
                    <h4 style="margin: 0 0 8px 0;">${section.roadName}</h4>
                    <p style="margin: 4px 0;"><strong>路況：</strong>${levelText}</p>
                    <p style="margin: 4px 0;"><strong>速度：</strong>${section.speed} 公里/小時</p>
                </div>
            `).addTo(trafficLayerGroup);
        });

        trafficLayerGroup.addTo(map);
        // console.log(`✓ 即時路況: ${result.count} 個路段`);
    } catch (error) {
        console.warn('載入即時路況失敗:', error.message);
    }
}

// ========== 公路即時影像圖層 ==========
async function toggleCameraLayer() {
    const btn = document.getElementById('cameraToggle');

    if (cameraMarkers) {
        map.removeLayer(cameraMarkers);
        cameraMarkers = null;
        btn.classList.remove('active');
        return;
    }

    btn.classList.add('loading');

    try {
        const response = await fetch('map-data/cam-list.json');
        const cameras = await response.json();

        if (!cameras || cameras.length === 0) {
            throw new Error('無攝影機資料');
        }

        // 使用 MarkerCluster
        cameraMarkers = L.markerClusterGroup({
            maxClusterRadius: 60,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });

        cameras.forEach(camera => {
            const [lat, lng, roadName, location, direction, streamUrl] = camera;

            const marker = L.marker([lat, lng], {
                icon: L.icon({
                    iconUrl: 'assets/images/videocam.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -16]
                })
            });

            marker.on('click', () => {
                openCameraModal(roadName, location, streamUrl);
            });

            cameraMarkers.addLayer(marker);
        });

        map.addLayer(cameraMarkers);
        btn.classList.remove('loading');
        btn.classList.add('active');

        // console.log(`✓ 公路攝影機: ${cameras.length} 個`);
    } catch (error) {
        console.error('載入公路攝影機失敗:', error);
        alert('載入公路攝影機失敗：' + error.message);
        btn.classList.remove('loading');
    }
}

let cameraRefreshInterval = null;
let currentCameraUrl = '';

function openCameraModal(roadName, location, streamUrl) {
    const modal = document.getElementById('cameraModal');
    const title = document.getElementById('cameraModalTitle');
    const image = document.getElementById('cameraModalImage');

    title.textContent = `${roadName} - ${location}`;
    currentCameraUrl = streamUrl;

    // 清除舊的計時器
    if (cameraRefreshInterval) {
        clearInterval(cameraRefreshInterval);
    }

    // 初始載入
    image.src = streamUrl;
    modal.style.display = 'flex';

    // 每10秒重新載入
    cameraRefreshInterval = setInterval(() => {
        if (modal.style.display === 'flex') {
            refreshCameraStream();
        }
    }, 10000);
}

function refreshCameraStream() {
    const image = document.getElementById('cameraModalImage');
    if (currentCameraUrl) {
        // 先清空再重新載入,不加任何參數
        image.src = '';
        setTimeout(() => {
            image.src = currentCameraUrl;
        }, 100);
    }
}

function closeCameraModal() {
    const modal = document.getElementById('cameraModal');
    const image = document.getElementById('cameraModalImage');

    // 清除計時器
    if (cameraRefreshInterval) {
        clearInterval(cameraRefreshInterval);
        cameraRefreshInterval = null;
    }

    modal.style.display = 'none';
    image.src = '';
}// 點擊 modal 背景關閉
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('cameraModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeCameraModal();
            }
        });
    }
});

// ========== 自動更新機制 ==========
function startAutoUpdate(name, func, interval) {
    if (updateIntervals[name]) clearInterval(updateIntervals[name]);
    updateIntervals[name] = setInterval(func, interval);
}

function initAutoUpdates() {
    loadLiveTraffic();
    startAutoUpdate('traffic', loadLiveTraffic, 5 * 60 * 1000);

    // 氣象圖層每 10 分鐘自動更新（僅在開啟時）
    startAutoUpdate('radar', async () => {
        if (radarLayer) {
            await toggleRadarLayer();
            await toggleRadarLayer();
        }
    }, 10 * 60 * 1000);

    startAutoUpdate('rainfall', async () => {
        if (rainfallLayer) {
            await toggleRainfallLayer();
            await toggleRainfallLayer();
        }
    }, 10 * 60 * 1000);

    // console.log('✓ 自動更新: 路況 5 分鐘、氣象 10 分鐘');
}

document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', initAutoUpdates)
    : initAutoUpdates();
