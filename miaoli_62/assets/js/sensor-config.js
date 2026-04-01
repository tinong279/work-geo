// 感測器資料轉換與狀態檢查
// 將從資料庫載入的感測器配置與資料轉換為小工具格式

// 將API返回的感測器資料轉換為小工具所需的格式
function transformSensorData(apiData, sensorConfig) {
    const widgetData = [];

    // 建立感測器ID對應的資料映射
    const sensorMap = {};
    apiData.sensors.forEach(sensor => {
        sensorMap[sensor.sensor_id] = sensor;
    });

    // 根據配置處理每個感測器
    sensorConfig.sensors.forEach(config => {
        if (config.type === 'battery') {
            // 電池電壓：支援多個電池感測器
            const sensorIds = Array.isArray(config.sensor_ids) ? config.sensor_ids : [config.sensor_ids];

            // 為每個電池感測器建立獨立的小工具
            sensorIds.forEach(sensorId => {
                const data = sensorMap[sensorId];

                if (data) {
                    // 電池電壓在 comment_value 中
                    widgetData.push({
                        type: 'battery',
                        widget: config.widget,
                        data: {
                            ...data,
                            value: data.comment_value || data.value // 優先使用 comment_value
                        },
                        config: config.config
                    });
                }
            });
        }
        else if (config.type === 'tilt') {
            // 傾斜儀：處理多組X和Y軸資料
            const tiltPairs = [];

            config.tilt_pairs.forEach(pair => {
                const xData = sensorMap[pair.x_sensor_id];
                const yData = sensorMap[pair.y_sensor_id];

                if (xData && yData) {
                    tiltPairs.push({
                        group_name: pair.group_name,
                        x_name: pair.x_name,
                        y_name: pair.y_name,
                        x_data: xData,
                        y_data: yData,
                        x_thresholds: pair.x_thresholds,
                        y_thresholds: pair.y_thresholds
                    });
                }
            });

            if (tiltPairs.length > 0) {
                widgetData.push({
                    type: 'tilt',
                    widget: config.widget,
                    data: {
                        pairs: tiltPairs
                    },
                    config: {}
                });
            }
        }
        else if (config.type === 'rain') {
            // 雨量筒
            const data = sensorMap[config.sensor_id];
            if (data) {
                widgetData.push({
                    type: 'rain',
                    widget: config.widget,
                    data: data,
                    config: {
                        thresholds: config.thresholds,
                        chinese_name: config.chinese_name
                    }
                });
            }
        }
        else if (config.type === 'power') {
            // 市電監測
            const data = sensorMap[config.sensor_id];
            if (data) {
                widgetData.push({
                    type: 'power',
                    widget: config.widget,
                    data: data,
                    config: {
                        chinese_name: config.chinese_name
                    }
                });
            }
        }
        else if (config.type === 'cms') {
            // 牌面CMS
            const data = sensorMap[config.sensor_id];
            if (data) {
                widgetData.push({
                    type: 'cms',
                    widget: config.widget,
                    data: data,
                    config: {
                        chinese_name: config.chinese_name
                    }
                });
            }
        }
        else if (config.type === 'water') {
            // 水位計
            const data = sensorMap[config.sensor_id];
            if (data) {
                widgetData.push({
                    type: 'water',
                    widget: config.widget,
                    data: data,
                    config: {
                        thresholds: config.thresholds,
                        chinese_name: config.chinese_name
                    }
                });
            }
        }
        else if (config.type === 'detection') {
            // 滯留物辨識
            const data = sensorMap[config.sensor_id];
            if (data) {
                widgetData.push({
                    type: 'detection',
                    widget: config.widget,
                    data: data,
                    config: {
                        chinese_name: config.chinese_name,
                        stream_url: config.stream_url
                    }
                });
            }
        }
        else if (config.type === 'rockfall') {
            // 落石偵測
            const data = sensorMap[config.sensor_id];
            if (data) {
                widgetData.push({
                    type: 'rockfall',
                    widget: config.widget,
                    data: data,
                    config: {
                        chinese_name: config.chinese_name
                    }
                });
            }
        }
    });

    return widgetData;
}

// 檢查是否有任何感測器異常 (用於地圖標記)
function hasAbnormalStatus(widgetDataList) {
    for (const widgetData of widgetDataList) {
        // 檢查電池電壓
        if (widgetData.type === 'battery') {
            const voltage = parseFloat(widgetData.data.value || widgetData.data.comment_value || 0);
            if (voltage < (window.SENSOR_THRESHOLDS?.BATTERY_LOW_VOLTAGE || 3.0)) return true;

            const now = new Date();
            const received = new Date(widgetData.data.received_at);
            const hoursDiff = (now - received) / (1000 * 60 * 60);
            if (hoursDiff > 1) return true;
        }

        // 檢查傾斜儀
        if (widgetData.type === 'tilt') {
            // 新版：檢查所有傾斜儀組
            const pairs = widgetData.data.pairs;
            if (!pairs || pairs.length === 0) return false;

            for (const pair of pairs) {
                const xValue = parseFloat(pair.x_data.value);
                const yValue = parseFloat(pair.y_data.value);

                // 檢查是否達到預警以上 (黃色/橘色/紅色都算異常)
                if (xValue >= pair.x_thresholds.warning || yValue >= pair.y_thresholds.warning) {
                    return true;
                }

                // 檢查是否斷線
                const now = new Date();
                const xReceived = new Date(pair.x_data.received_at);
                const yReceived = new Date(pair.y_data.received_at);
                const xHoursDiff = (now - xReceived) / (1000 * 60 * 60);
                const yHoursDiff = (now - yReceived) / (1000 * 60 * 60);

                if (xHoursDiff > 1 || yHoursDiff > 1) return true;
            }
        }
    }

    return false;
}

// 創建小工具實例
function createWidget(widgetData) {

    const WidgetClass = window[widgetData.widget];
    if (!WidgetClass) {
        console.error(`找不到小工具類別: ${widgetData.widget}`);
        console.error('Available on window:', Object.keys(window).filter(k => k.includes('Widget')));
        return null;
    }

    return new WidgetClass(null, widgetData.data, widgetData.config);
}
