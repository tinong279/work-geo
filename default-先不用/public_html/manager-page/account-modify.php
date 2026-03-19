<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\account-session.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    
    $creator = $_SESSION['uid'];
    $creator_level = $_SESSION['level'];
    $_email = '';
    $_password = '';

    $status = 0;
	//---------------------------------------------------------
	function remove_special_char($buffer, $keyword)
	{
		$count_buf = strlen($keyword);
		for ($i = 0; $i < $count_buf; $i++)
		{
			$buffer = str_replace($keyword[$i], "", $buffer);
		}
		return $buffer;
	}
	//---------------------------------------------------------
	if (isset($_POST['token']) == true && isset($_SESSION['token1']) == true)
	{
	    if ($_POST['token'] != $_SESSION['token1'])
	    {
	        header('Location: logout.php');
	        exit;
	    }
	}
	else
	{
	    header('Location: logout.php');
	    exit;
	}
	//---------------------------------------------------------
    if (isset($_POST['email']) == true && isset($_POST['password']) == true && isset($_POST['name']) == true)
    {
        $_email = $_POST['email'];
        $_password = $_POST['password'];
        $_password = hash('sha3-512', $user_login_hash_key . $_password);
        $_name = $_POST['name'];
		$_name = htmlspecialchars_decode($_name, ENT_QUOTES);
		$_name = remove_special_char($_name, "<>'\"=;");
        $_name = substr($_name, 0, 100);
        if (strlen($_name) == 0)
        {
            $status = 201;
        }
        $pattern = '/^([0-9a-z\@\.\-]+)$/';
        if (preg_match($pattern, $_email) == TRUE && strlen($_name) > 0)
        {
            require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    		if ($db_link == TRUE)
    		{
    			$db_link->query("SET NAMES \"utf8\"");
    			$sql_query = "SELECT sn, level, uid FROM `user-info` WHERE uid=?;";
    			$stmt = $db_link->prepare($sql_query);
    			if ($stmt == TRUE)
    			{
    				$stmt->bind_param("s", $_email);
    				$stmt->execute();
    				$stmt->store_result();
    				$data_count = $stmt->num_rows;
    				if ($data_count == 1)
    				{
    					$stmt->bind_result($data_buf["sn"], $data_buf["level"], $data_buf["uid"]);
    				    $stmt->fetch();
    				    $stmt->close();
    				    if ($data_buf["level"] < $creator_level)
    				    {
    				        $sql_query = "UPDATE `user-info` SET pw=?, name=? WHERE sn=?;";
    				        $stmt = $db_link->prepare($sql_query);
        				    if ($stmt == TRUE)
        				    {
        				        $stmt->bind_param("ssi", $_password, $_name, $data_buf["sn"]);
        				        $stmt->execute();
        				        $status = 200;
        				        $stmt->close();
        				    }
    				    }
    				    else if ($creator == $data_buf["uid"])
    				    {
    				        $sql_query = "UPDATE `user-info` SET pw=?, name=? WHERE sn=?;";
    				        $stmt = $db_link->prepare($sql_query);
        				    if ($stmt == TRUE)
        				    {
        				        $stmt->bind_param("ssi", $_password, $_name, $data_buf["sn"]);
        				        $stmt->execute();
        				        $status = 200;
        				        $stmt->close();
        				    }
    				    }
    				    else if ($data_buf["level"] >= $creator_level)
    				    {
    				        $status = 203;
    				    }
    				}
    				else
    				{
    					$stmt->close();
    					$status = 202;
    				}
    			}
    			$db_link->close();
    		}
        }
        else
        {
            $status = 201;
        }
    }
    
    header('Location: account.php?msg=' . $status); 
    
?>