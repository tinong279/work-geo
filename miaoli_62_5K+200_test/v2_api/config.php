<?php

// config.php
return [
    'db' => [
        'host'    => '210.71.231.250',   // 改成遠端主機
        'dbname'  => 'miaoli_62_5k+200',
        'user'    => 'timmy',
        'pass'    => '0000',
        'charset' => 'utf8mb4',
    ],
    'app_token' => '12345678',
    'line_notify_token' => '你的_LINE_NOTIFY_TOKEN',
];

/**
 * 根據類型與攝影機編號取得基礎網址
 * @param string $type   類型 ('cam' 或 'alert')
 * @param string $camera 攝影機編號 ('1' 或 '2')
 * @return string|null
 */
function getCameraBaseUrl($type, $camera)
{
    // 1. 定義網址清單
    $baseUrls = [
        'cam' => [
            '1' => 'http://116.59.9.174:8020/axis-cgi/jpg/image.cgi?resolution=640x360',
            '2' => 'http://116.59.9.174:8030/axis-cgi/jpg/image.cgi?resolution=640x360',
        ],
        'alert' => [
            '1' => 'http://220.133.109.237:85/warning_img/ch1/line-notify/',
            '2' => 'http://220.133.109.237:85/warning_img/ch2/line-notify/',
        ],
    ];

    // 2. 驗證並回傳對應的網址
    if (isset($baseUrls[$type][$camera])) {
        return $baseUrls[$type][$camera];
    }

    return null;
}
