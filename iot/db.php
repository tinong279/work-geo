<?php
   try {
    $pdo = new PDO(
    //建立一個資料庫連線物件
    "mysql:host=localhost;dbname=iot_db;charset=utf8",
    //host=localhos資料庫在什麼地方  
    //iot_db資料表名字,你現在所有 sensor_measurements 都在這個 DB  

    "root",
    "",
    // 帳號密碼, 
[
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    // 只要 SQL 或連線出錯，就「丟出例外（Exception）」 會顯示錯誤訊息
    ]);
} catch (PDOException $e) {
    // 寫入系統 log（工程人員看）
    error_log($e->getMessage());

    // 顯示給使用者看的訊息（不暴露內部資訊）
    die("系統暫時無法連線，請稍後再試");
}


