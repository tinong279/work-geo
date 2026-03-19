<?php
	
	set_time_limit(3);
	
	require("C:\\xampp\\htdocs\\default\\session.php");
	
	$cms_status = -1;
	$cms_img_path = '-1';
	$modbus_response = (array) null;;
	$socket = stream_socket_client("tcp://111.70.30.252:50210", $errno, $errstr, 5);
	
	if ($socket)
	{
		fwrite($socket, pack('C*', 0, 0, 0, 0, 0, 6, 1, 2, 0, 0, 0, 1));
		stream_set_timeout($socket, 3);
		$modbus_response = unpack('C*', fread($socket, 1024));
		fclose($socket);
	}
	if (count($modbus_response) == 10)
	{
		if ($modbus_response[7] == 1 &&
			$modbus_response[8] == 2 &&
			$modbus_response[9] == 1 &&
			$modbus_response[10] == 0)
			{
				$cms_status = 0;
			}
		else if ($modbus_response[7] == 1 &&
			$modbus_response[8] == 2 &&
			$modbus_response[9] == 1 &&
			$modbus_response[10] == 1)
			{
				$cms_status = 1;
			}
	}
	
	// print_r($modbus_response);
	echo $cms_status;
	
?>
