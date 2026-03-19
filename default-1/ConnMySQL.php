<?php

$db_host = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "miaoli-62";


$db_link = new mysqli($db_host, $db_username, $db_password, $db_name);


if ($db_link->connect_error) {
	die("連線失敗: " . $db_link->connect_error);
}

/*<?php

$db_host = "192.168.60.79"; 

$db_username = "root"; 

$db_password = "qaz24238721"; 

$db_name = "miaoli-62";

$db_link = new mysqli($db_host, $db_username, $db_password, $db_name);

$db_link->set_charset("utf8mb4");

if ($db_link->connect_error) {
    die("❌ 遠端連線失敗: " . $db_link->connect_error);
}*/
