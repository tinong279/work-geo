/**
 * 感測器閾值配置 (JavaScript)
 * 集中管理所有感測器的閾值設定
 * 對應 PHP 版本：includes/sensor_thresholds.php
 */

const SENSOR_THRESHOLDS = {
    // 電池電壓閾值
    BATTERY_LOW_VOLTAGE: 3.0,  // 低電量警告閾值 (V)

    // 其他感測器閾值可以在此添加
    // WATER_LEVEL_WARNING: 100,  // 水位警告閾值 (cm)
    // RAIN_ALERT_THRESHOLD: 50,   // 降雨警戒閾值 (mm)
};

// 將配置掛載到 window 物件
window.SENSOR_THRESHOLDS = SENSOR_THRESHOLDS;
