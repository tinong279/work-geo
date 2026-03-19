<?php
// api_refresh_data.php
require("../../home-func-1.php"); // 載入你提供的函式檔案路徑

// 取得請求類型
$type = isset($_GET['type']) ? $_GET['type'] : '';

switch ($type) {
    case 'inclinometer':
        home_get_inclinometer_all();
        break;
    case 'raingauge':
        home_get_raingauge_all();
        break;
    case 'battery':
        home_get_battery_voltage(102);
        break;
    default:
        echo "Invalid Type";
        break;
}
