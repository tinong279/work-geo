<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\account-session.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    
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
    
    $creator = $_SESSION['uid'];
    $creator_level = $_SESSION['level'];
    $_email = '';
    
    $status = 0;
    
    if (isset($_POST['email']) == true)
    {
        $_email = $_POST['email'];
        $pattern = '/^([0-9a-z\@\.\-]+)$/';
        if (preg_match($pattern, $_email) == TRUE)
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
    				        $sql_query = "DELETE FROM `user-info` WHERE sn=?;";
    				        $stmt = $db_link->prepare($sql_query);
        				    if ($stmt == TRUE)
        				    {
        				        $stmt->bind_param("i", $data_buf["sn"]);
        				        $stmt->execute();
        				        $status = 300;
        				        $stmt->close();
        				    }
    				    }
    				    else if ($data_buf["level"] >= $creator_level)
    				    {
    				        $status = 303;
    				    }
    				}
    				else
    				{
    					$stmt->close();
    					$status = 302;
    				}
    			}
    			$db_link->close();
    		}
        }
        else
        {
            $status = 301;
        }
    }
    
    header('Location: account.php?msg=' . $status); 
    
?>