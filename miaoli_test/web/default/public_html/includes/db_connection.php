<?php
function getDbConnection(): PDO
{
    $serverName = 'localhost\\SQLEXPRESS';
    $database   = 'miaoli_test';
    $username   = 'miaoli_user';
    $password   = 'Miaoli123!';

    $dsn = "sqlsrv:Server={$serverName};Database={$database};TrustServerCertificate=1";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    } catch (PDOException $e) {
        die('資料庫連線失敗：' . $e->getMessage());
    }
}
