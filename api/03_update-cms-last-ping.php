<?php
	// 這個檔案用來更新指定 CMS 裝置的最後一次回應時間（ping 時間）

	//========================================
	$dataList = [];
	$token1 = '';
	$token2 = '';
	//========================================
	if (isset($_REQUEST['token']))
	{
		$token2 = $_REQUEST['token'];
		if ($token1 === $token2)
		{
			
		}
		else
		{
			// exit();
		}
	}
	else
	{
		// exit();
	}
	//========================================
	$id = 0;
	
	if (isset($_REQUEST['id']))
	{
		$id = intval($_REQUEST['id']);
	}
	//========================================
	if ($id > 0)
	{
		require("ConnMySQL.php");
		if ($db_link == true)
		{
			$db_link->query("SET NAMES \"utf8\"");
			
			$sql_query = "UPDATE `01-cms-status` SET `last-ping-echo-time`=CURRENT_TIMESTAMP() WHERE `id`=?";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == true)
			{
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$stmt->close();
			}
			
			$sql_query = "INSERT IGNORE INTO `04-ping-test-log` (`id`) VALUES (?);";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == true)
			{
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$stmt->close();
			}
			
			$db_link->close();
		}
	}
	
?>
