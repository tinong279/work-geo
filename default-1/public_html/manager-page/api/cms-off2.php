<?php

set_time_limit(3);

require("../../../session.php");
//===========================================================
require("../../../ConnMySQL.php");
if ($db_link == TRUE) {
	$ip = $_SERVER['REMOTE_ADDR'];
	$site = '苗62鄉道 6.2K邊坡';
	$info = 'cms turn off';
	$db_link->query("SET NAMES \"utf8\"");
	$sql_query = "INSERT INTO `cms-op-log`(`ip`, `site`, `info`, `user`) VALUES (?, ?, ?, ?);";
	$stmt = $db_link->prepare($sql_query);
	if ($stmt == TRUE) {
		$stmt->bind_param("ssss", $ip, $site, $info, $_SESSION['uid']);
		$stmt->execute();
		$stmt->close();
	}
	$db_link->close();
}
//===========================================================
$cms_status = -1;
$cms_img_path = '';
$modbus_response = (array) null;;
$socket = stream_socket_client("tcp://111.70.30.251:50210", $errno, $errstr, 5);

if ($socket) {
	fwrite($socket, pack('C*', 0, 0, 0, 0, 0, 6, 1, 5, 0, 0, 0, 0));
	stream_set_timeout($socket, 3);
	$modbus_response = unpack('C*', fread($socket, 1024));
	fclose($socket);
}
if (count($modbus_response) == 12) {
	if (
		$modbus_response[8] == 5 &&
		$modbus_response[9] == 0 &&
		$modbus_response[10] == 0 &&
		$modbus_response[11] == 0 &&
		$modbus_response[12] == 0
	) {
		$cms_status = 1;
	}
}
if ($cms_status == 1) {
	echo "ok";
} else {
	echo "error";
}
