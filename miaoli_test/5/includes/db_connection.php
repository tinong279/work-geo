<?php
// MSSQL Database connection
function getDbConnection()
{
    $db_config = [
        // 'host' => '127.0.0.1',
        'host' => '210.71.231.140',
        'user' => 'tomoffice',
        'pass' => 'tomoffice',
        'name' => 'yunlin_gukeng'
    ];

    try {
        $conn = new PDO("sqlsrv:Server=" . $db_config['host'] . ";Database=" . $db_config['name'], $db_config['user'], $db_config['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
    }
}

// 舊資料庫連線（yunlin）- 用於查詢舊點位的 sensor_record
function getOldDbConnection()
{
    $db_config = [
        // 'host' => '127.0.0.1',
        'host' => '210.71.231.140',
        'user' => 'tomoffice',
        'pass' => 'tomoffice',
        'name' => 'yunlin'
    ];

    try {
        $conn = new PDO("sqlsrv:Server=" . $db_config['host'] . ";Database=" . $db_config['name'], $db_config['user'], $db_config['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Old database connection failed: ' . $e->getMessage()]));
    }
}
