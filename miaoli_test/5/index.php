<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>監測點位儀表板</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <!-- 核心樣式 -->
    <link rel="stylesheet" href="assets/css/core/variables.css">
    <link rel="stylesheet" href="assets/css/core/reset.css">
    <link rel="stylesheet" href="assets/css/core/layout.css">

    <!-- 元件樣式 -->
    <link rel="stylesheet" href="assets/css/components/buttons.css">
    <link rel="stylesheet" href="assets/css/components/forms.css">
    <link rel="stylesheet" href="assets/css/components/modals.css">
    <link rel="stylesheet" href="assets/css/components/tables.css">
    <link rel="stylesheet" href="assets/css/components/sidebar.css">

    <!-- 小工具樣式 -->
    <link rel="stylesheet" href="assets/css/widgets/sensor-widgets.css">
    <link rel="stylesheet" href="assets/css/widgets/status-indicators.css">

    <!-- 工具樣式 -->
    <link rel="stylesheet" href="assets/css/utils/animations.css">

    <!-- 頁面專用樣式 -->
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
</head>

<body>
    <?/*php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    */ ?>

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="content-area">
                <div id="map" class="map-fullscreen">
                    <!-- 地圖圖例 -->
                    <div id="radarLegend" class="map-legend" style="display: none;">
                        <div class="legend-title">雷達回波 (dBZ)</div>
                        <svg width="60" height="300" viewBox="0 0 60 300">
                            <!-- 漸層定義 -->
                            <defs>
                                <linearGradient id="radarGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ff00ff;stop-opacity:1" />
                                    <stop offset="15%" style="stop-color:#ff00ff;stop-opacity:1" />
                                    <stop offset="23%" style="stop-color:#8b0000;stop-opacity:1" />
                                    <stop offset="31%" style="stop-color:#ff0000;stop-opacity:1" />
                                    <stop offset="38%" style="stop-color:#ff4500;stop-opacity:1" />
                                    <stop offset="46%" style="stop-color:#ffa500;stop-opacity:1" />
                                    <stop offset="54%" style="stop-color:#ffff00;stop-opacity:1" />
                                    <stop offset="62%" style="stop-color:#90ee90;stop-opacity:1" />
                                    <stop offset="69%" style="stop-color:#00ff00;stop-opacity:1" />
                                    <stop offset="77%" style="stop-color:#008000;stop-opacity:1" />
                                    <stop offset="85%" style="stop-color:#0000ff;stop-opacity:1" />
                                    <stop offset="92%" style="stop-color:#00bfff;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#00ffff;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <!-- 色條 -->
                            <rect x="10" y="10" width="20" height="280" fill="url(#radarGradient)" stroke="#333" stroke-width="1" />
                            <!-- 刻度標籤 -->
                            <text x="35" y="15" font-size="11" fill="#333" font-weight="bold">65</text>
                            <text x="35" y="55" font-size="11" fill="#333">60</text>
                            <text x="35" y="95" font-size="11" fill="#333">55</text>
                            <text x="35" y="135" font-size="11" fill="#333">50</text>
                            <text x="35" y="175" font-size="11" fill="#333">45</text>
                            <text x="35" y="215" font-size="11" fill="#333">40</text>
                            <text x="35" y="255" font-size="11" fill="#333">35</text>
                            <text x="35" y="293" font-size="11" fill="#333" font-weight="bold">30</text>
                        </svg>
                    </div>

                    <div id="rainfallLegend" class="map-legend" style="display: none;">
                        <div class="legend-title">累積雨量 (mm)</div>
                        <svg width="65" height="300" viewBox="0 0 65 300">
                            <!-- 漸層定義 -->
                            <defs>
                                <linearGradient id="rainfallGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ff00ff;stop-opacity:1" />
                                    <stop offset="7%" style="stop-color:#8b008b;stop-opacity:1" />
                                    <stop offset="14%" style="stop-color:#8b0000;stop-opacity:1" />
                                    <stop offset="21%" style="stop-color:#ff0000;stop-opacity:1" />
                                    <stop offset="28%" style="stop-color:#ff4500;stop-opacity:1" />
                                    <stop offset="35%" style="stop-color:#ffa500;stop-opacity:1" />
                                    <stop offset="42%" style="stop-color:#ffff00;stop-opacity:1" />
                                    <stop offset="50%" style="stop-color:#90ee90;stop-opacity:1" />
                                    <stop offset="57%" style="stop-color:#00ff00;stop-opacity:1" />
                                    <stop offset="64%" style="stop-color:#008000;stop-opacity:1" />
                                    <stop offset="71%" style="stop-color:#0000ff;stop-opacity:1" />
                                    <stop offset="78%" style="stop-color:#00bfff;stop-opacity:1" />
                                    <stop offset="85%" style="stop-color:#87ceeb;stop-opacity:1" />
                                    <stop offset="92%" style="stop-color:#add8e6;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#f0f8ff;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <!-- 色條 -->
                            <rect x="10" y="10" width="20" height="280" fill="url(#rainfallGradient)" stroke="#333" stroke-width="1" />
                            <!-- 刻度標籤 -->
                            <text x="35" y="15" font-size="10" fill="#333" font-weight="bold">300</text>
                            <text x="35" y="35" font-size="10" fill="#333">200</text>
                            <text x="35" y="55" font-size="10" fill="#333">150</text>
                            <text x="35" y="75" font-size="10" fill="#333">130</text>
                            <text x="35" y="95" font-size="10" fill="#333">110</text>
                            <text x="35" y="115" font-size="10" fill="#333">90</text>
                            <text x="35" y="135" font-size="10" fill="#333">70</text>
                            <text x="35" y="155" font-size="10" fill="#333">50</text>
                            <text x="35" y="175" font-size="10" fill="#333">40</text>
                            <text x="35" y="195" font-size="10" fill="#333">30</text>
                            <text x="35" y="215" font-size="10" fill="#333">20</text>
                            <text x="35" y="235" font-size="10" fill="#333">15</text>
                            <text x="35" y="255" font-size="10" fill="#333">10</text>
                            <text x="35" y="275" font-size="10" fill="#333">6</text>
                            <text x="35" y="293" font-size="10" fill="#333" font-weight="bold">2</text>
                        </svg>
                    </div>
                </div>

                <div id="sensorPanel" class="sensor-panel">
                    <div class="sensor-card sensor-detail-card">
                        <button class="close-button" onclick="closeSensorPanel()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                        <h2 id="sensorTitle">感測點資訊</h2>
                        <div class="status-grid">
                            <div class="status-item success">
                                <svg class="status-icon" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z" />
                                </svg>
                                <div class="status-label">市電狀態</div>
                                <div class="status-value" id="powerStatus">ON</div>
                            </div>
                            <div class="status-item success">
                                <svg class="status-icon" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M11,6V14H13V6H11M12,2C6.48,2 2,6.48 2,12C2,17.52 6.48,22 12,22C17.52,22 22,17.52 22,12C22,6.48 17.52,2 12,2M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20Z" />
                                </svg>
                                <div class="status-label">電池電壓</div>
                                <div class="status-value" id="batteryVoltage">12V</div>
                            </div>
                        </div>
                    </div>

                    <div class="sensor-card">
                        <div class="data-section">
                            <h3>傾斜儀有行動值狀態</h3>
                            <div class="data-value" id="tiltStatus">正常運作</div>
                        </div>
                    </div>

                    <div class="sensor-card">
                        <div class="data-section">
                            <h3>牌面</h3>
                            <div class="data-value" id="signStatus">狀態良好</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div>

    <?php include 'includes/change_password_modal.php'; ?>

    <!-- 公路即時影像 Modal -->
    <div id="cameraModal" class="camera-modal" style="display: none;">
        <div class="camera-modal-content">
            <div class="camera-modal-header">
                <h3 id="cameraModalTitle">公路影像</h3>
                <button class="camera-refresh-btn" onclick="refreshCameraStream()" title="重新載入">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                </button>
                <button class="camera-modal-close" onclick="closeCameraModal()">&times;</button>
            </div>
            <div class="camera-modal-body">
                <img id="cameraModalImage" src="" alt="公路影像">
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet MarkerCluster JS -->
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <!-- OverlappingMarkerSpiderfier JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OverlappingMarkerSpiderfier-Leaflet/0.2.6/oms.min.js"></script>

    <!-- Sidebar Scripts -->
    <script src="assets/js/sidebar-scripts.js?v=<?php echo time(); ?>"></script>
    <!-- Sensor Widget System -->
    <script src="assets/js/sensor-thresholds.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/sensor-widgets.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/sensor-config.js?v=<?php echo time(); ?>"></script>
    <!-- Map Layers Control -->
    <script src="assets/js/map-layers.js?v=<?php echo time(); ?>"></script>
    <!-- 自訂腳本 -->
    <script src="assets/js/dashboard-scripts.js?v=<?php echo time(); ?>"></script>

    <?php include 'includes/change_password_modal.php'; ?>

</html>