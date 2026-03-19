<?php
    
	if (isset($_SESSION['level']))
	{
	    if ($_SESSION['level'] < 255)
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
	
?>