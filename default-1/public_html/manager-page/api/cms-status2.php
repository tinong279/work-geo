<?php

set_time_limit(10);

require("../../../session.php");
session_write_close();
$cms_status = -1;
$cms_img_path = '';
$modbus_response = (array) null;;
$socket = stream_socket_client("tcp://111.70.30.251:50210", $errno, $errstr, 5);

if ($socket) {
	fwrite($socket, pack('C*', 0, 0, 0, 0, 0, 6, 1, 1, 0, 0, 0, 1));
	stream_set_timeout($socket, 3);
	$modbus_response = unpack('C*', fread($socket, 1024));
	fclose($socket);
}
if (count($modbus_response) == 10) {
	if (
		$modbus_response[7] == 1 &&
		$modbus_response[8] == 1 &&
		$modbus_response[9] == 1 &&
		$modbus_response[10] == 0
	) {
		$cms_status = 0;
	} else if (
		$modbus_response[7] == 1 &&
		$modbus_response[8] == 1 &&
		$modbus_response[9] == 1 &&
		$modbus_response[10] == 1
	) {
		$cms_status = 1;
	}
}
if ($cms_status == 1) {
	$cms_img_path = 'img/on1.png';
} else if ($cms_status == 0) {
	$cms_img_path = 'img/off1.png';
} else {
	// $cms_img_path = 'img/unknown.png';
	$cms_img_path = 'img/off1.png';
}
echo $cms_img_path;
