<?php
    
	set_time_limit(3);
	
	$token = '7654354354675426652656';
	
	if ($token == $_GET['token'])
	{
		//===========================================================
		require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
		if ($db_link == TRUE)
		{
			$ip = $_SERVER['REMOTE_ADDR'];
			$site = '苗縣道126 23.1K邊坡';
			$info = 'RDWS cms turn on';
			$db_link->query("SET NAMES \"utf8\"");
			$sql_query = "INSERT INTO `cms-op-log`(`ip`, `site`, `info`, `user`) VALUES (?, ?, ?, 'RDWS');";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == TRUE)
			{
				$stmt->bind_param("sss", $ip, $site, $info);
				$stmt->execute();
				$stmt->close();
			}
			$db_link->close();
		}
		//===========================================================
	}
	else
	{
		exit();
	}
	
	$cms_status = -1;
	$cms_img_path = '';
	$modbus_response = (array) null;;
	$socket = stream_socket_client("tcp://111.70.30.252:50210", $errno, $errstr, 5);
	
	if ($socket)
	{
		fwrite($socket, pack('C*', 0, 0, 0, 0, 0, 6, 1, 5, 0, 0, 255, 0));
		stream_set_timeout($socket, 3);
		$modbus_response = unpack('C*', fread($socket, 1024));
		fclose($socket);
	}
	if (count($modbus_response) == 12)
	{
		if ($modbus_response[8] == 5 &&
			$modbus_response[9] == 0 &&
			$modbus_response[10] == 0 &&
			$modbus_response[11] == 255 &&
			$modbus_response[12] == 0)
			{
				$cms_status = 1;
			}
	}
	if ($cms_status == 1)
	{
		echo "ok";
	}
	else
	{
		echo "error";
	}
?>
