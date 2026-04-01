// ========== 地圖與感測器面板控制 JavaScript ==========

let globalLocations = [];
let markerSpiderfier = null; // 用於處理重疊標記

document.addEventListener('DOMContentLoaded', function () {
    // 檢查是否在首頁（有地圖容器）
    const mapContainer = document.getElementById('map');

    fetch('api/get_locations.php')
        .then(response => response.json())
        .then(locations => {
            if (locations && locations.length > 0) {
                globalLocations = locations;
                // 只在有地圖容器時初始化地圖
                if (mapContainer) {
                    initializeMap(locations);

                    // 檢查 URL 參數是否指定了要顯示的點位
                    const urlParams = new URLSearchParams(window.location.search);
                    const locationId = urlParams.get('location');
                    if (locationId) {
                        const location = locations.find(loc => loc.id == locationId);
                        if (location) {
                            setTimeout(() => showSensorPanel(location), 500);
                        }
                    }
                }
                populateLocationTabs(locations);
            } else {
                console.error("無法獲取或地點資料為空");
            }
        })
        .catch(error => {
            console.error('獲取地點資料時發生錯誤:', error);
        });
});
// ========== 7. 填充側邊欄的即時資料分頁 ========== 
function populateLocationTabs(locations) {
    // 檢查是否在首頁
    const isIndexPage = document.getElementById('map') !== null;

    // 按分類分組點位
    const categories = {
        '邊坡監測': [],
        'AI落石影像監測': [],
        '邊坡落石監測': [],
        '地下道水位監測': []
    };

    locations.forEach(function (loc) {
        const category = loc.category || '邊坡監測';
        if (categories[category]) {
            categories[category].push(loc);
        }
    });

    // 為每個分類填充點位
    Object.keys(categories).forEach(function (categoryName) {
        const tabsContainer = document.querySelector(`.location-tabs[data-category="${categoryName}"]`);
        if (!tabsContainer) return;

        tabsContainer.innerHTML = '';

        categories[categoryName].forEach(function (loc) {
            const li = document.createElement('li');
            li.className = 'location-tab';
            const btn = document.createElement('button');
            btn.textContent = loc.name;
            btn.style.width = '100%';
            btn.style.textAlign = 'left';
            btn.style.padding = '8px 12px 8px 24px';
            btn.style.border = 'none';
            btn.style.background = 'none';
            btn.style.cursor = 'pointer';
            btn.style.fontSize = '13px';
            btn.onmouseover = () => btn.style.background = '#f0f0f0';
            btn.onmouseout = () => btn.style.background = 'none';
            btn.onclick = function () {
                if (isIndexPage) {
                    // 在首頁直接顯示面板
                    showSensorPanel(loc);
                } else {
                    // 在其他頁面跳轉到首頁並帶上點位 ID
                    window.location.href = 'index.php?location=' + loc.id;
                }
            };
            li.appendChild(btn);
            tabsContainer.appendChild(li);
        });
    });

    // 設置分類按鈕的點擊事件
    document.querySelectorAll('.category-button').forEach(function (btn) {
        btn.onclick = function () {
            const category = this.getAttribute('data-category');
            const tabsContainer = document.querySelector(`.location-tabs[data-category="${category}"]`);
            const icon = this.querySelector('svg path');

            if (tabsContainer.style.display === 'none' || tabsContainer.style.display === '') {
                tabsContainer.style.display = 'block';
                // 旋轉箭頭
                icon.setAttribute('d', 'M7.41,15.41L12,10.83L16.59,15.41L18,14L12,8L6,14L7.41,15.41Z');
            } else {
                tabsContainer.style.display = 'none';
                // 恢復箭頭
                icon.setAttribute('d', 'M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z');
            }
        };
    });
}

let map; // 將 map 宣告在全域
let locationMarkers = {}; // 儲存點位標記以便更新
let locationMarkersList = []; // 儲存所有標記以便進行重疊檢測
let oms; // OverlappingMarkerSpiderfier 實例
let currentLocationId = null; // 儲存當前顯示的點位ID
let autoRefreshInterval = null; // 自動更新計時器

function initializeMap(locations) {
    // ========== 2. 初始化地圖 ==========
    const centerLat = locations.reduce((sum, loc) => sum + loc.lat, 0) / locations.length;
    const centerLon = locations.reduce((sum, loc) => sum + loc.lon, 0) / locations.length;
    map = L.map('map').setView([centerLat, centerLon], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // 初始化 OverlappingMarkerSpiderfier
    oms = new OverlappingMarkerSpiderfier(map, {
        keepSpiderfied: true,
        nearbyDistance: 20,
        circleSpiralSwitchover: 9,
        spiderfyDistanceMultiplier: 1.5,
        legWeight: 2.5,
        legColors: {
            usual: '#32B8C6',
            highlighted: '#FF5459'
        }
    });

    // ========== 6. 在地圖上加入所有標記 ==========
    locations.forEach(function (loc) {
        createLocationMarker(loc);
    });

    // 定期檢查所有點位狀態並更新標記
    setInterval(() => updateAllMarkerStatus(), 60000); // 每分鐘更新一次
    updateAllMarkerStatus(); // 立即執行一次

    // 啟動自動更新（每3分鐘）
    startAutoRefresh();
}

// 不再需要 adjustOverlappingMarkers 函數，已被 Spiderfy 取代
/* 原本的 adjustOverlappingMarkers 函數已移除

*/

// 創建點位標記
function createLocationMarker(loc) {
    const isAlert = loc.status === 'alert';
    const customIcon = createCustomIcon(isAlert);

    const marker = L.marker([loc.lat, loc.lon], { icon: customIcon }).addTo(map);

    // 設定 z-index：紅色異常標記顯示在最上層
    marker.setZIndexOffset(isAlert ? 1000 : 0);

    // 將標記加入 OverlappingMarkerSpiderfier
    oms.addMarker(marker);

    // 綁定點擊事件 - 同時綁定click和spider_click
    const clickHandler = function () {
        showSensorPanel(loc);
    };

    marker.on('click', clickHandler);
    marker.on('spider_click', clickHandler);

    marker.bindPopup(`<b>${loc.name}</b><br>${loc.category || ''}<br>緯度: ${loc.lat}<br>經度: ${loc.lon}`);

    // 儲存標記以便後續更新
    locationMarkers[loc.id] = { marker: marker, location: loc };
    locationMarkersList.push(marker);
}

// 更新所有標記的狀態
async function updateAllMarkerStatus() {
    for (const [locationId, markerData] of Object.entries(locationMarkers)) {
        try {
            // 獲取配置
            const configResponse = await fetch(`api/get_sensor_config.php?location_id=${locationId}`);
            const configResult = await configResponse.json();

            if (!configResult.success) continue;

            const sensorConfig = configResult.config;

            // 獲取資料
            const response = await fetch(`api/get_sensor_data.php?location_id=${locationId}`);
            const result = await response.json();

            if (result.success && sensorConfig.sensors && sensorConfig.sensors.length > 0) {
                const widgetDataList = transformSensorData(result, sensorConfig);
                const hasAbnormal = hasAbnormalStatus(widgetDataList);

                // 更新標記圖標（不需要重新加入 Spiderfy，因為標記實例沒變）
                const newIcon = createCustomIcon(hasAbnormal);
                markerData.marker.setIcon(newIcon);

                // 更新 z-index：異常標記顯示在最上層
                markerData.marker.setZIndexOffset(hasAbnormal ? 1000 : 0);
            }
        } catch (error) {
            console.error(`更新點位 ${locationId} 狀態時發生錯誤:`, error);
        }
    }
}


// ========== 3. 建立自訂標記圖標 ==========
function createCustomIcon(isAlert) {
    const mainColor = isAlert ? '#D32F2F' : '#388E3C';
    const iconPath = isAlert
        ? '<path fill="white" d="M11 15h2v2h-2zm0-8h2v6h-2z"></path>'
        : '<path fill="white" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path>';

    const svgIconHtml = `
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
        <path fill="${mainColor}" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"></path>
        ${iconPath}
    </svg>`;

    return L.divIcon({
        html: svgIconHtml,
        className: '',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
}

// ========== 4. 顯示感測器面板 ==========
async function showSensorPanel(locationData) {
    const mapDiv = document.getElementById('map');
    const panel = document.getElementById('sensorPanel');

    mapDiv.classList.remove('map-fullscreen');
    mapDiv.classList.add('map-compressed');
    panel.classList.add('active');

    document.getElementById('sensorTitle').textContent = locationData.name;

    // 儲存當前點位ID用於自動更新
    currentLocationId = locationData.id;

    try {
        // 從 API 獲取感測器配置
        const configResponse = await fetch(`api/get_sensor_config.php?location_id=${locationData.id}`);
        const configResult = await configResponse.json();

        if (!configResult.success) {
            throw new Error(configResult.error || '無法取得感測器配置');
        }

        const sensorConfig = configResult.config;

        // 從 API 獲取感測器資料
        const response = await fetch(`api/get_sensor_data.php?location_id=${locationData.id}`);
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || '無法取得感測器資料');
        }

        if (!sensorConfig.sensors || sensorConfig.sensors.length === 0) {
            // 如果沒有配置，顯示預設訊息
            panel.innerHTML = `
                <div class="sensor-card sensor-detail-card">
                    <button class="close-button" onclick="closeSensorPanel()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    <h2 id="sensorTitle">${locationData.name}</h2>
                    <div class="widget-body">
                        <p style="text-align: center; color: #666; padding: 20px;">此點位尚未配置感測器</p>
                    </div>
                </div>
            `;
        } else {
            // 轉換資料格式
            const widgetDataList = transformSensorData(result, sensorConfig);

            // 渲染小工具
            renderWidgets(panel, locationData.name, widgetDataList);
        }

    } catch (error) {
        console.error('載入感測器資料時發生錯誤:', error);
        panel.innerHTML = `
            <div class="sensor-card sensor-detail-card">
                <button class="close-button" onclick="closeSensorPanel()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <h2 id="sensorTitle">${locationData.name}</h2>
                <div class="widget-body">
                    <p style="text-align: center; color: #d32f2f; padding: 20px;">載入資料失敗：${error.message}</p>
                </div>
            </div>
        `;
    }

    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 450);
}

// 渲染小工具到面板
function renderWidgets(panel, locationName, widgetDataList) {
    let html = `
        <div class="sensor-card sensor-detail-card">
            <button class="close-button" onclick="closeSensorPanel()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <h2 id="sensorTitle">${locationName}</h2>
        </div>
        <div class="widgets-container">
    `;

    // 為每個小工具創建實例並渲染
    widgetDataList.forEach(widgetData => {
        const widget = createWidget(widgetData);
        if (widget) {
            html += widget.render();
        } else {
            console.error('Widget creation failed for:', widgetData);
        }
    });

    html += '</div>';
    panel.innerHTML = html;
}

// ========== 5. 關閉感測器面板 ==========
function closeSensorPanel() {
    const mapDiv = document.getElementById('map');
    const panel = document.getElementById('sensorPanel');

    mapDiv.classList.remove('map-compressed');
    mapDiv.classList.add('map-fullscreen');
    panel.classList.remove('active');

    // 清除當前點位ID
    currentLocationId = null;

    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 450);
}

// ========== 自動更新功能 ==========
function startAutoRefresh() {
    // 清除舊的計時器
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }

    // 設定每3分鐘更新一次（180000毫秒）
    autoRefreshInterval = setInterval(() => {
        // 更新所有標記狀態
        updateAllMarkerStatus();

        // 如果有打開的感測器面板，更新面板內容
        if (currentLocationId) {
            const location = globalLocations.find(loc => loc.id == currentLocationId);
            if (location) {
                refreshSensorPanel(location);
            }
        }
    }, 180000); // 3分鐘 = 180000毫秒
}

// 重新整理感測器面板資料（不改變UI狀態）
async function refreshSensorPanel(locationData) {
    const panel = document.getElementById('sensorPanel');
    if (!panel.classList.contains('active')) return;

    try {
        // 從 API 獲取感測器配置
        const configResponse = await fetch(`api/get_sensor_config.php?location_id=${locationData.id}`);
        const configResult = await configResponse.json();

        if (!configResult.success) {
            console.error('無法取得感測器配置');
            return;
        }

        const sensorConfig = configResult.config;

        // 從 API 獲取感測器資料
        const response = await fetch(`api/get_sensor_data.php?location_id=${locationData.id}`);
        const result = await response.json();

        if (!result.success) {
            console.error('無法取得感測器資料');
            return;
        }

        if (!sensorConfig.sensors || sensorConfig.sensors.length === 0) {
            return;
        }

        // 轉換資料格式
        const widgetDataList = transformSensorData(result, sensorConfig);

        // 渲染小工具
        renderWidgets(panel, locationData.name, widgetDataList);

        console.log(`已更新 ${locationData.name} 的感測器資料`);

    } catch (error) {
        console.error('更新感測器資料時發生錯誤:', error);
    }
}
