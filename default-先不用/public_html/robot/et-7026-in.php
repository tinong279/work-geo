<?php
    
    //===============================================================
    require("C:\\xampp\\htdocs\\default\\function.php");
    //===============================================================
    $m_token = "a2e7176e8ada09cf8a3c3a23556cf68700ef248b622a582512261538ad5551e8dbae1498352d7f421c50d4c6d7b9fd163300441c363fd441dd30b6ad278e7641";
    $token = "";
    $chn_id = 0;
	$chn_value = 0;
    //===============================================================
    if (isset($_POST["token"]))
    {
        $token = $_POST["token"];
    }
    if (isset($_POST["chn_id"]))
    {
        $chn_id = $_POST["chn_id"];
    }
    if (isset($_POST["chn_value"]))
    {
        $chn_value = $_POST["chn_value"];
    }
    //===============================================================
    if ($m_token === $token)
    {
		$chn_id = intval($chn_id);
		$chn_value = strval($chn_value);
		
		$time = new DateTime;
		$time = $time->format('Y-m-d H:i:s');
		
		require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
		if ($db_link == TRUE)
		{
			$db_link->query("SET NAMES \"utf8\"");
			$sql_query = "INSERT IGNORE INTO `rawdata`(time, _id, _data) VALUES (?, ?, ?);";
			$stmt = $db_link->prepare($sql_query);
			if ($stmt == TRUE)
			{
				$stmt->bind_param("sis", $time, $chn_id, $chn_value);
				$stmt->execute();
				$stmt->close();
			}
			filter_et7026_to_chndata($db_link, $time, $chn_id, $chn_value);
			$db_link->close();
		}
    }
    //----------------------------------------------------------------------
    function filter_et7026_to_chndata($db_link, $time, $id, $data)
    {
		if ($id == 101)
		{
			$chn_id = 10101;
			$val = floatval($data);
			$status = 0;
			if ($val == 1)
			{
				$status = 1;
			}
			else
			{
				$status = 3;
			}
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 102)
		{
			$chn_id = 10201;
			$val = floatval($data);
			$val /= 65535;
			$val *= 20;
			$status = get_status_code_batteryvoltage($val);
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 201)
		{
			$chn_id = 20101;
			$val = floatval($data);
			$status = 0;
			if ($val == 1)
			{
				$status = 1;
			}
			else
			{
				$status = 3;
			}
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 202)
		{
			$chn_id = 20201;
			$val = floatval($data);
			$val /= 65535;
			$val *= 20;
			$status = get_status_code_batteryvoltage($val);
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 301)
		{
			$chn_id = 30101;
			$val = floatval($data);
			$status = 0;
			if ($val == 1)
			{
				$status = 1;
			}
			else
			{
				$status = 3;
			}
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 302)
		{
			$chn_id = 30201;
			$val = floatval($data);
			$val /= 65535;
			$val *= 20;
			$status = get_status_code_batteryvoltage($val);
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 401)
		{
			$chn_id = 40101;
			$val = floatval($data);
			$status = 0;
			if ($val == 1)
			{
				$status = 1;
			}
			else
			{
				$status = 3;
			}
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
		else if ($id == 402)
		{
			$chn_id = 40201;
			$val = floatval($data);
			$val /= 65535;
			$val *= 20;
			$status = get_status_code_batteryvoltage($val);
			write_chndata($db_link, $time, $chn_id, $val, $status, 3, 3, 3);
		}
    }
    function write_chndata($db_link, $time, $chn_id, $val, $status, $s1_val, $s2_val, $s3_val)
    {
        $sql_query = "INSERT INTO `chndata`(time, chn_id, val, status, s1, s2, s3) VALUES (?, ?, ?, ?, ?, ?, ?);";
        $stmt = $db_link->prepare($sql_query);
        if ($stmt == TRUE)
        {
            $stmt->bind_param("sididdd", $time, $chn_id, $val, $status, $s1_val, $s2_val, $s3_val);
            $stmt->execute();
            $stmt->close();
        }
    }
    //----------------------------------------------------------------------
    
?>
