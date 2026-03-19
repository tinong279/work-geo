<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\account-session.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    
    $creator = $_SESSION['uid'];
    $_email = '';
    $_password = '';
    $_name = '';
    
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
            $status = 101;
        }
        $pattern = '/^([0-9a-z\@\.\-]+)$/';
        if (preg_match($pattern, $_email) == TRUE && strlen($_name) > 0)
        {
            require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
    		if ($db_link == TRUE)
    		{
    			$db_link->query("SET NAMES \"utf8\"");
    			$sql_query = "SELECT sn FROM `user-info` WHERE uid=?;";
    			$stmt = $db_link->prepare($sql_query);
    			if ($stmt == TRUE)
    			{
    				$stmt->bind_param("s", $_email);
    				$stmt->execute();
    				$stmt->store_result();
    				$data_count = $stmt->num_rows;
    				if ($data_count == 0)
    				{
    				    $stmt->close();
    				    $sql_query = "INSERT INTO `user-info` (status, level, uid, pw, creator, name) VALUES (1, 1, ?, ?, ?, ?);";
    				    $stmt = $db_link->prepare($sql_query);
    				    if ($stmt == TRUE)
    				    {
    				        $stmt->bind_param("ssss", $_email, $_password, $creator, $_name);
    				        $stmt->execute();
    				        $status = 100;
    				        $stmt->close();
    				    }
    				}
    				else
    				{
    					$stmt->close();
    					$status = 102;
    				}
    			}
    			$db_link->close();
    		}
        }
        else
        {
            $status = 101;
        }
    }
    
    header('Location: account.php?msg=' . $status); 
    
?>
