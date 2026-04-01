// 感測器小工具類別系統
// 所有小工具的基礎類別
class SensorWidget {
    constructor(containerId, data, config) {
        this.containerId = containerId;
        this.data = data;
        this.config = config;
    }

    // 計算狀態顏色 (綠色/黃色/橘色/紅色/灰色)
    calculateStatus(value, thresholds, receivedTime) {
        // 檢查是否斷線 (超過1小時)
        const now = new Date();
        const received = new Date(receivedTime);
        const hoursDiff = (now - received) / (1000 * 60 * 60);

        if (hoursDiff > 1) {
            return { color: 'gray', label: '斷線', class: 'status-offline' };
        }

        if (!thresholds || value === null || value === undefined) {
            return { color: 'gray', label: '無資料', class: 'status-offline' };
        }

        // 根據閾值判斷狀態
        if (thresholds.action !== undefined && value >= thresholds.action) {
            return { color: 'red', label: '行動', class: 'status-danger' };
        }
        if (thresholds.alert !== undefined && value >= thresholds.alert) {
            return { color: 'orange', label: '警戒', class: 'status-alert' };
        }
        if (thresholds.warning !== undefined && value >= thresholds.warning) {
            return { color: 'yellow', label: '預警', class: 'status-warning' };
        }
        return { color: 'green', label: '正常', class: 'status-normal' };
    }

    formatTime(datetime) {
        if (!datetime) return '---';
        const date = new Date(datetime);
        return date.toLocaleString('zh-TW', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    }

    render() {
        throw new Error('render() 必須在子類別中實作');
    }
}

// 電池電壓小工具
class BatteryWidget extends SensorWidget {
    render() {
        // 電池電壓在 value 中（後端已處理格式轉換）
        let voltage = parseFloat(this.data.value);

        // 防止 NaN
        if (isNaN(voltage) || voltage === null || voltage === undefined) {
            voltage = 0;
        }

        const receivedTime = this.data.received_at;
        // 取得感測器名稱（中文名稱或英文名稱）
        const sensorName = this.data.chinese_name || this.data.sensor_name || '電池電壓';

        // 電池電壓狀態：小於3.0V為紅色
        let status;
        const now = new Date();
        const received = new Date(receivedTime);
        const hoursDiff = (now - received) / (1000 * 60 * 60);

        if (hoursDiff > 1) {
            status = { color: 'gray', label: '斷線', class: 'status-offline' };
        } else if (voltage < 3.0) {
            status = { color: 'red', label: '電量不足', class: 'status-danger' };
        } else {
            status = { color: 'green', label: '正常', class: 'status-normal' };
        }

        return `
            <div class="sensor-card sensor-widget battery-widget ${status.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M16.67,4H15V2H9V4H7.33A1.33,1.33 0 0,0 6,5.33V20.67C6,21.4 6.6,22 7.33,22H16.67A1.33,1.33 0 0,0 18,20.67V5.33C18,4.6 17.4,4 16.67,4Z" />
                    </svg>
                    <h3>${sensorName}</h3>
                </div>
                <div class="widget-body battery-widget-body">
                    <div class="battery-status-display">
                        <div class="widget-status-badge ${status.class}">
                            <div class="status-indicator"></div>
                            <span class="status-label">${status.label}</span>
                        </div>
                    </div>
                    <div class="battery-voltage-display">
                        <div class="battery-voltage-value">${voltage.toFixed(2)}</div>
                        <div class="battery-voltage-unit">V</div>
                    </div>
                    <div class="battery-time-display">
                        <div class="battery-time-label">接收時間</div>
                        <div class="battery-time-value ${status.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(receivedTime)}</div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 傾斜儀小工具
class TiltWidget extends SensorWidget {
    render() {
        // 新版：支援多組傾斜儀
        const pairs = this.data.pairs;
        if (pairs && pairs.length > 0) {
            // 計算整體狀態
            let overallStatus = { color: 'green', label: '正常', class: 'status-normal' };
            const severity = { 'gray': 5, 'red': 4, 'orange': 3, 'yellow': 2, 'green': 1 };

            pairs.forEach(pair => {
                const xValue = parseFloat(pair.x_data.value);
                const yValue = parseFloat(pair.y_data.value);
                const xStatus = this.calculateStatus(xValue, pair.x_thresholds, pair.x_data.received_at);
                const yStatus = this.calculateStatus(yValue, pair.y_thresholds, pair.y_data.received_at);

                if (severity[xStatus.color] > severity[overallStatus.color]) overallStatus = xStatus;
                if (severity[yStatus.color] > severity[overallStatus.color]) overallStatus = yStatus;
            });

            // 取得標題（從第一組的中文名稱，移除 _X 或 _Y 後綴）
            let titleName = pairs[0].x_name || '傾斜儀';
            // 移除 _X, _Y 後綴（包括前面可能的數字）
            titleName = titleName.replace(/[-–—_＿\s]*\d*[-–—_＿\s]*[XYＸＹxy]$/g, '').trim();
            // 確保以「傾斜儀」結尾
            if (!titleName.endsWith('傾斜儀')) {
                titleName = titleName + ' - 傾斜儀';
            }

            // 產生每組傾斜儀的 HTML
            const pairsHtml = pairs.map((pair, index) => {
                const xValue = parseFloat(pair.x_data.value);
                const yValue = parseFloat(pair.y_data.value);
                const xStatus = this.calculateStatus(xValue, pair.x_thresholds, pair.x_data.received_at);
                const yStatus = this.calculateStatus(yValue, pair.y_thresholds, pair.y_data.received_at);

                // 取得電池電壓（從 comment_value，可能是 JSON 格式）
                let batteryVoltage = 0;
                const commentValue = pair.x_data.comment_value || pair.y_data.comment_value;

                if (commentValue) {
                    // 嘗試解析 JSON（舊格式：{"RSSI":-65,"BAT":330}）
                    try {
                        const jsonData = JSON.parse(commentValue);
                        if (jsonData && jsonData.BAT !== undefined) {
                            batteryVoltage = parseFloat(jsonData.BAT) / 100.0;
                        } else {
                            batteryVoltage = parseFloat(commentValue);
                        }
                    } catch (e) {
                        // 如果不是 JSON，直接轉換為數字（新格式）
                        batteryVoltage = parseFloat(commentValue);
                    }
                }

                // 防止 NaN
                if (isNaN(batteryVoltage)) {
                    batteryVoltage = 0;
                }

                // 電池狀態判斷
                const now = new Date();
                const received = new Date(pair.x_data.received_at);
                const hoursDiff = (now - received) / (1000 * 60 * 60);
                let batteryStatus;
                if (hoursDiff > 1) {
                    batteryStatus = { color: 'gray', label: '斷線', class: 'status-offline' };
                } else if (batteryVoltage < (window.SENSOR_THRESHOLDS?.BATTERY_LOW_VOLTAGE || 3.0)) {
                    batteryStatus = { color: 'red', label: '電量不足', class: 'status-danger' };
                } else {
                    batteryStatus = { color: 'green', label: '正常', class: 'status-normal' };
                }

                // 只有多組時才顯示組別標題
                const groupTitle = pairs.length > 1 ? `<div class="tilt-pair-title">傾斜儀 ${index + 1}</div>` : '';

                return `
                    <div class="tilt-pair-group">
                        ${groupTitle}
                        <div class="tilt-data-section">
                            <div class="tilt-axes-grid">
                                <div class="tilt-axis-card ${xStatus.class}">
                                    <div class="axis-name">${pair.x_name || 'X軸'}</div>
                                    <div class="axis-value-large">${xValue.toFixed(2)}<span class="axis-unit">度</span></div>
                                    <div class="widget-status-badge ${xStatus.class}">
                                        <div class="status-indicator"></div>
                                        <span class="status-label">${xStatus.label}</span>
                                    </div>
                                    <div class="axis-time-label">接收時間</div>
                                    <div class="axis-time ${xStatus.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(pair.x_data.received_at)}</div>
                                </div>
                                <div class="tilt-axis-card ${yStatus.class}">
                                    <div class="axis-name">${pair.y_name || 'Y軸'}</div>
                                    <div class="axis-value-large">${yValue.toFixed(2)}<span class="axis-unit">度</span></div>
                                    <div class="widget-status-badge ${yStatus.class}">
                                        <div class="status-indicator"></div>
                                        <span class="status-label">${yStatus.label}</span>
                                    </div>
                                    <div class="axis-time-label">接收時間</div>
                                    <div class="axis-time ${yStatus.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(pair.y_data.received_at)}</div>
                                </div>
                            </div>
                            <div class="tilt-battery-card ${batteryStatus.class}">
                                <div class="battery-icon-wrapper">
                                    <svg class="battery-icon-large" viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="currentColor" d="M16.67,4H15V2H9V4H7.33A1.33,1.33 0 0,0 6,5.33V20.67C6,21.4 6.6,22 7.33,22H16.67A1.33,1.33 0 0,0 18,20.67V5.33C18,4.6 17.4,4 16.67,4Z" />
                                    </svg>
                                </div>
                                <div class="battery-info-content">
                                    <div class="battery-label">電池電壓</div>
                                    <div class="battery-value-display">
                                        <span class="battery-value-large">${batteryVoltage.toFixed(2)}</span>
                                        <span class="battery-unit">V</span>
                                    </div>
                                    <div class="widget-status-badge ${batteryStatus.class}">
                                        <div class="status-indicator"></div>
                                        <span class="status-label">${batteryStatus.label}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // 取得預警值、警戒值、行動值（從第一組的 X 軸閾值）
            const firstPairThresholds = pairs[0].x_thresholds;
            let thresholdInfoHtml = '';
            if (firstPairThresholds && (firstPairThresholds.warning !== undefined || firstPairThresholds.alert !== undefined || firstPairThresholds.action !== undefined)) {
                const warningValue = parseFloat(firstPairThresholds.warning);
                const alertValue = parseFloat(firstPairThresholds.alert);
                const actionValue = parseFloat(firstPairThresholds.action);

                thresholdInfoHtml = `
                    <div class="threshold-info">
                        ${!isNaN(warningValue) ? `
                        <div class="threshold-row">
                            <span class="threshold-label">
                                <span class="status-indicator status-warning"></span>
                                預警值
                            </span>
                            <span class="threshold-value">${warningValue.toFixed(2)} 度</span>
                        </div>
                        ` : ''}
                        ${!isNaN(alertValue) ? `
                        <div class="threshold-row">
                            <span class="threshold-label">
                                <span class="status-indicator status-alert"></span>
                                警戒值
                            </span>
                            <span class="threshold-value">${alertValue.toFixed(2)} 度</span>
                        </div>
                        ` : ''}
                        ${!isNaN(actionValue) ? `
                        <div class="threshold-row">
                            <span class="threshold-label">
                                <span class="status-indicator status-danger"></span>
                                行動值
                            </span>
                            <span class="threshold-value">${actionValue.toFixed(2)} 度</span>
                        </div>
                        ` : ''}
                    </div>
                `;
            }

            return `
                <div class="sensor-card sensor-widget tilt-widget ${overallStatus.class}">
                    <div class="widget-header">
                        <svg class="widget-icon" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,17V16H9V14H13V17H14V13H10V11H14V7H11V9H9V7H8V11H12V13H8V17H11Z" />
                        </svg>
                        <h3>${titleName}</h3>
                    </div>
                    <div class="widget-body">
                        ${pairsHtml}
                        ${thresholdInfoHtml}
                    </div>
                </div>
            `;
        }

        // 無資料
        return `
            <div class="sensor-card sensor-widget tilt-widget status-offline">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,17V16H9V14H13V17H14V13H10V11H14V7H11V9H9V7H8V11H12V13H8V17H11Z" />
                    </svg>
                    <h3>傾斜儀</h3>
                </div>
                <div class="widget-body">
                    <div class="widget-status-badge">
                        <div class="status-indicator"></div>
                        <span class="status-label">無資料</span>
                    </div>
                </div>
            </div>
        `;
    }
}

// 市電狀態小工具
class PowerWidget extends SensorWidget {
    render() {
        const receivedTime = this.data.received_at;
        const chineseName = this.config.chinese_name || '市電狀態';

        // 從 value 或 comment_value 讀取狀態（注意：0 是有效值）
        let statusValue = this.data.value !== undefined && this.data.value !== null
            ? this.data.value
            : this.data.status;

        // 計算是否斷線（超過1小時）
        let statusInfo;
        const now = new Date();
        const received = new Date(receivedTime);
        const hoursDiff = (now - received) / (1000 * 60 * 60);

        if (hoursDiff > 1) {
            statusInfo = { color: 'gray', label: '斷線', class: 'status-offline' };
        } else if (statusValue === 'on' || statusValue === 'normal' || statusValue === 1 || statusValue === '1') {
            statusInfo = { color: 'green', label: '正常供電', class: 'status-normal' };
        } else if (statusValue === 'off' || statusValue === 0 || statusValue === '0') {
            statusInfo = { color: 'red', label: '斷電', class: 'status-danger' };
        } else {
            statusInfo = { color: 'gray', label: '未知', class: 'status-offline' };
        }

        return `
            <div class="sensor-card sensor-widget power-widget ${statusInfo.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z" />
                    </svg>
                    <h3>${chineseName}</h3>
                </div>
                <div class="widget-body power-widget-body">
                    <div class="power-status-display">
                        <div class="power-status-indicator ${statusInfo.class}"></div>
                        <div class="power-status-text">${statusInfo.label}</div>
                    </div>
                    <div class="power-time-display">
                        <div class="power-time-label">接收時間</div>
                        <div class="power-time-value ${statusInfo.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(receivedTime)}</div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 雨量筒小工具
class RainfallWidget extends SensorWidget {
    render() {
        const rainfall = parseFloat(this.data.value || 0);
        const receivedTime = this.data.received_at;
        const chineseName = this.config.chinese_name || '雨量筒';

        const status = this.calculateStatus(rainfall, this.config.thresholds, receivedTime);

        return `
            <div class="sensor-card sensor-widget rainfall-widget ${status.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12,3.77L11.25,4.61C11.25,4.61 9.97,6.06 8.68,7.94C7.39,9.82 6,12.07 6,14.23A6,6 0 0,0 12,20.23A6,6 0 0,0 18,14.23C18,12.07 16.61,9.82 15.32,7.94C14.03,6.06 12.75,4.61 12.75,4.61L12,3.77M12,6.9C12.44,7.42 12.84,7.85 13.68,9.07C14.89,10.83 16,13.07 16,14.23C16,16.45 14.22,18.23 12,18.23C9.78,18.23 8,16.45 8,14.23C8,13.07 9.11,10.83 10.32,9.07C11.16,7.85 11.56,7.42 12,6.9Z" />
                    </svg>
                    <h3>${chineseName}</h3>
                </div>
                <div class="widget-body rainfall-widget-body">
                    <div class="rainfall-status-display">
                        <div class="widget-status-badge ${status.class}">
                            <div class="status-indicator"></div>
                            <span class="status-label">${status.label}</span>
                        </div>
                    </div>
                    <div class="rainfall-value-display">
                        <div class="rainfall-value">${rainfall.toFixed(1)}</div>
                        <div class="rainfall-unit">mm</div>
                    </div>
                    <div class="rainfall-time-display">
                        <div class="rainfall-time-label">接收時間</div>
                        <div class="rainfall-time-value ${status.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(receivedTime)}</div>
                    </div>
                    <div class="threshold-info">
                        <div class="threshold-row">
                            <span class="threshold-label">
                                <span class="status-indicator status-warning"></span>
                                預警值
                            </span>
                            <span class="threshold-value">每小時超過 40 mm</span>
                        </div>
                        <div class="threshold-row">
                            <span class="threshold-label">
                                <span class="status-indicator status-danger"></span>
                                行動值
                            </span>
                            <span class="threshold-value">24小時雨量超過 200 mm</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 落石告警小工具
class RockfallWidget extends SensorWidget {
    render() {
        const receivedTime = this.data ? this.data.received_at : null;
        const chineseName = this.config.chinese_name || '落石偵測';
        const hasRockfall = this.data && this.data.value == 1;

        // 落石偵測：10分鐘內為紅色，超過10分鐘為綠色
        let statusInfo;
        if (hasRockfall && receivedTime) {
            const now = new Date();
            const detected = new Date(receivedTime);
            const minutesDiff = (now - detected) / (1000 * 60);

            if (minutesDiff <= 10) {
                // 10 分鐘內：紅色警報
                statusInfo = { color: 'red', label: '偵測到落石', class: 'status-danger' };
            } else {
                // 超過 10 分鐘：綠色正常
                statusInfo = { color: 'green', label: '正常', class: 'status-normal' };
            }
        } else {
            // 沒有偵測記錄：綠色正常
            statusInfo = { color: 'green', label: '正常', class: 'status-normal' };
        }

        return `
            <div class="sensor-card sensor-widget rockfall-widget ${statusInfo.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12,2L1,21H23M12,6L19.53,19H4.47M11,10V14H13V10M11,16V18H13V16" />
                    </svg>
                    <h3>${chineseName}</h3>
                </div>
                <div class="widget-body power-widget-body">
                    <div class="power-status-display">
                        <div class="power-status-indicator ${statusInfo.class}"></div>
                        <div class="power-status-text">${statusInfo.label}</div>
                    </div>
                    ${receivedTime ? `
                        <div class="power-time-display">
                            <div class="power-time-label">最後偵測時間</div>
                            <div class="power-time-value">${this.formatTime(receivedTime)}</div>
                        </div>
                    ` : `
                        <div class="power-time-display">
                            <div class="power-time-label">狀態</div>
                            <div class="power-time-value">無偵測記錄</div>
                        </div>
                    `}
                </div>
            </div>
        `;
    }
}

// 滯留物辨識小工具
class DetectionWidget extends SensorWidget {
    render() {
        // data 包含最新一筆 sensor_record 資料（可能為 null）
        // value = 1 代表有偵測到滯留物
        // file_name 存放圖片檔名
        const hasDetection = this.data && this.data.value == 1;
        const imageFilename = this.data ? this.data.file_name : null;
        const detectedTime = this.data ? this.data.received_at : null;
        const chineseName = this.config.chinese_name || '滯留物辨識';
        const streamUrl = this.config.stream_url;

        // 根據 trigger_time 產生圖片路徑: /年-月-日/檔名
        let imageUrl = null;
        if (imageFilename && detectedTime) {
            const date = new Date(detectedTime);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            imageUrl = `/detection_images/${year}-${month}-${day}/${imageFilename}`;
        }

        // 檢查是否在 10 分鐘內偵測到
        let status;
        if (hasDetection && detectedTime) {
            const now = new Date();
            const detected = new Date(detectedTime);
            const minutesDiff = (now - detected) / (1000 * 60);

            if (minutesDiff <= 10) {
                // 10 分鐘內：紅色警報
                status = { color: 'red', label: '偵測中', class: 'status-danger' };
            } else {
                // 超過 10 分鐘：綠色正常
                status = { color: 'green', label: '正常', class: 'status-normal' };
            }
        } else {
            // 沒有偵測記錄：綠色正常
            status = { color: 'green', label: '正常', class: 'status-normal' };
        }

        return `
            <div class="sensor-card sensor-widget detection-widget ${status.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M9,2V8H11V11H5C3.89,11 3,11.89 3,13V22H5V13H11V22H13V13H19V22H21V13C21,11.89 20.11,11 19,11H13V8H15V2M11,4H13V6H11V4Z" />
                    </svg>
                    <h3>${chineseName}</h3>
                </div>
                <div class="widget-body detection-widget-body">
                    <!-- 左側：最新偵測圖片 -->
                    <div class="detection-left-panel">
                        <div class="detection-image-container ${status.class}" ${imageUrl ? `onclick="openImageModal('${imageUrl}')"` : ''}>
                            ${imageUrl ? `
                                <img src="${imageUrl}" alt="滯留物偵測" class="detection-snapshot" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="detection-placeholder" style="display:none;">
                                    <svg viewBox="0 0 24 24" width="48" height="48" style="opacity: 0.3;">
                                        <path fill="currentColor" d="M19,19H5V5H19M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M13.96,12.29L11.21,15.83L9.25,13.47L6.5,17H17.5L13.96,12.29Z" />
                                    </svg>
                                    <div style="color: var(--color-text-secondary); font-size: var(--font-size-sm); margin-top: 8px;">圖片載入失敗</div>
                                </div>
                                <div class="detection-overlay">
                                    <div class="detection-label">最新偵測</div>
                                </div>
                            ` : `
                                <div class="detection-placeholder">
                                    <svg viewBox="0 0 24 24" width="48" height="48" style="opacity: 0.3;">
                                        <path fill="currentColor" d="M19,19H5V5H19M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M13.96,12.29L11.21,15.83L9.25,13.47L6.5,17H17.5L13.96,12.29Z" />
                                    </svg>
                                    <div style="color: var(--color-text-secondary); font-size: var(--font-size-base); margin-top: 8px; font-weight: var(--font-weight-medium);">無滯留物發生</div>
                                </div>
                            `}
                        </div>
                    </div>
                    
                    <!-- 右側：串流影像 -->
                    <div class="detection-right-panel">
                        <div class="stream-container" ${streamUrl ? `onclick="openStreamModal('${streamUrl}', '${chineseName} - 即時串流')"` : ''}>
                            ${streamUrl ? `
                                <div class="stream-preview-overlay">
                                    <div class="stream-play-button">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8,5.14V19.14L19,12.14L8,5.14Z" />
                                        </svg>
                                    </div>
                                    <div class="stream-preview-text">點擊觀看串流</div>
                                </div>
                            ` : `
                                <div class="stream-placeholder">
                                    <svg viewBox="0 0 24 24" width="48" height="48" style="opacity: 0.3;">
                                        <path fill="currentColor" d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z" />
                                    </svg>
                                    <div style="color: var(--color-text-secondary); font-size: var(--font-size-sm); margin-top: 8px;">無串流影像</div>
                                </div>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 工具函數：打開影像Modal
function openImageModal(imageUrl) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-backdrop" onclick="this.parentElement.remove()">
            <div class="image-modal-content" onclick="event.stopPropagation()">
                <button class="modal-close" onclick="this.closest('.image-modal').remove()">&times;</button>
                <img src="${imageUrl}" alt="偵測影像">
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// 工具函數：打開串流Modal
function openStreamModal(streamUrl, cameraName) {
    const modal = document.createElement('div');
    modal.className = 'stream-modal';
    modal.innerHTML = `
        <div class="stream-modal-backdrop" onclick="this.parentElement.remove()">
            <div class="stream-modal-content" onclick="event.stopPropagation()">
                <div class="stream-modal-header">
                    <h3>${cameraName}</h3>
                    <button class="modal-close" onclick="this.closest('.stream-modal').remove()">&times;</button>
                </div>
                <div class="stream-modal-body">
                    <iframe src="${streamUrl}" frameborder="0" allowfullscreen allow="autoplay; fullscreen"></iframe>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// 牌面 CMS 小工具
class CMSWidget extends SensorWidget {
    render() {
        const receivedTime = this.data.received_at;
        const chineseName = this.config.chinese_name || '牌面狀態';

        // 計算是否斷線（超過1小時）
        let status;
        const now = new Date();
        const received = new Date(receivedTime);
        const hoursDiff = (now - received) / (1000 * 60 * 60);

        if (hoursDiff > 1) {
            status = { color: 'gray', label: '斷線', class: 'status-offline' };
        } else {
            status = { color: 'green', label: '正常運作', class: 'status-normal' };
        }

        return `
            <div class="sensor-card sensor-widget cms-widget ${status.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M21,16H3V4H21M21,2H3C1.89,2 1,2.89 1,4V16A2,2 0 0,0 3,18H10V20H8V22H16V20H14V18H21A2,2 0 0,0 23,16V4C23,2.89 22.1,2 21,2Z" />
                    </svg>
                    <h3>${chineseName}</h3>
                </div>
                <div class="widget-body cms-widget-body">
                    <div class="cms-status-section">
                        <div class="widget-status-badge ${status.class}">
                            <div class="status-indicator"></div>
                            <span class="status-label">${status.label}</span>
                        </div>
                    </div>
                    <div class="cms-image-container">
                        <img src="assets/images/cms-display-sample.jpg" alt="CMS牌面示意圖" class="cms-display-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22%236b7280%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22%3ECMS 牌面顯示%3C/text%3E%3C/svg%3E'">
                        <div class="cms-info-overlay">
                            <div class="cms-info-text">牌面狀態監控</div>
                        </div>
                    </div>
                    <div class="cms-time-display">
                        <div class="cms-time-label">更新時間</div>
                        <div class="cms-time-value ${status.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(receivedTime)}</div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 水位計小工具
class WaterWidget extends SensorWidget {
    render() {
        const waterLevel = parseFloat(this.data.value || 0);
        const receivedTime = this.data.received_at;
        const chineseName = this.config.chinese_name || '水位計';

        // 計算實際水位（資料庫值 - 20）
        const actualLevel = waterLevel - 20;

        // 計算狀態
        const status = this.calculateStatus(waterLevel, this.config.thresholds, receivedTime);

        // 固定電池電壓 3.7V
        const batteryVoltage = 3.7;
        const batteryStatus = { color: 'green', label: '正常', class: 'status-normal' };

        return `
            <div class="sensor-card sensor-widget water-widget ${status.class}">
                <div class="widget-header">
                    <svg class="widget-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12,3.77L11.25,4.61C11.25,4.61 9.97,6.06 8.68,7.94C7.39,9.82 6,12.07 6,14.23A6,6 0 0,0 12,20.23A6,6 0 0,0 18,14.23C18,12.07 16.61,9.82 15.32,7.94C14.03,6.06 12.75,4.61 12.75,4.61L12,3.77M12,6.9C12.44,7.42 12.84,7.85 13.68,9.07C14.89,10.83 16,13.07 16,14.23C16,16.45 14.22,18.23 12,18.23C9.78,18.23 8,16.45 8,14.23C8,13.07 9.11,10.83 10.32,9.07C11.16,7.85 11.56,7.42 12,6.9Z" />
                    </svg>
                    <h3>${chineseName}</h3>
                </div>
                <div class="widget-body">
                    <div class="tilt-data-section">
                        <div class="water-level-card ${status.class}">
                            <div class="axis-name">水位高度</div>
                            <div class="axis-value-large">${actualLevel.toFixed(1)}<span class="axis-unit">cm</span></div>
                            <div class="widget-status-badge ${status.class}">
                                <div class="status-indicator"></div>
                                <span class="status-label">${status.label}</span>
                            </div>
                            <div class="axis-time-label">接收時間</div>
                            <div class="axis-time ${status.class === 'status-offline' ? 'time-offline' : ''}">${this.formatTime(receivedTime)}</div>
                        </div>
                        <div class="tilt-battery-card ${batteryStatus.class}">
                            <div class="battery-icon-wrapper">
                                <svg class="battery-icon-large" viewBox="0 0 24 24" width="24" height="24">
                                    <path fill="currentColor" d="M16.67,4H15V2H9V4H7.33A1.33,1.33 0 0,0 6,5.33V20.67C6,21.4 6.6,22 7.33,22H16.67A1.33,1.33 0 0,0 18,20.67V5.33C18,4.6 17.4,4 16.67,4Z" />
                                </svg>
                            </div>
                            <div class="battery-info-content">
                                <div class="battery-label">電池電壓</div>
                                <div class="battery-value-display">
                                    <span class="battery-value-large">${batteryVoltage.toFixed(2)}</span>
                                    <span class="battery-unit">V</span>
                                </div>
                                <div class="widget-status-badge ${batteryStatus.class}">
                                    <div class="status-indicator"></div>
                                    <span class="status-label">${batteryStatus.label}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 將類別掛載到 window 以便全域訪問
window.SensorWidget = SensorWidget;
window.BatteryWidget = BatteryWidget;
window.TiltWidget = TiltWidget;
window.PowerWidget = PowerWidget;
window.RainfallWidget = RainfallWidget;
window.RockfallWidget = RockfallWidget;
window.DetectionWidget = DetectionWidget;
window.CMSWidget = CMSWidget;
window.WaterWidget = WaterWidget;


