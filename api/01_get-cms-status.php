<?php
	
	//========================================
	$dataList = [];
	$token1 = '';
	$token2 = '';
	//========================================
	if (isset($_POST['token']))
	{
		$token2 = $_POST['token'];
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
	require("ConnMySQL.php");
	
	if ($db_link == true)
	{
		$db_link->query("SET NAMES \"utf8\"");
		$sql_query = "SELECT `id`, `status`, `info`, `lat`, `lon`, `ip`, `last-ping-echo-time` FROM `01-cms-status` ORDER BY `id` ASC;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == true)
		{
			// $stmt->bind_param("s", $var);
			$stmt->execute();
			
			$result = $stmt->get_result();  // Obtain the result set
			if ($result->num_rows > 0)
			{
				while ($row = $result->fetch_assoc())
				{
					$dataList[] = $row;
				}
			}
			// print_r($dataList);
			
			$stmt->close();
		}
		$db_link->close();
	}
	
	header('Content-Type: application/json');
	$jsonString = json_encode($dataList);
	echo $jsonString;
	
?>
