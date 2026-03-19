<?php
	
	function isValid($str) {
		// 這個正則表達式會匹配所有只包含「0-9」和「-」的字串
		return preg_match('/^[-0-9]*$/', $str) === 1;
	}
	
	if(isset($_POST["token"]))
	{
		if ($_POST["token"] == 'fGFg5tgdrgjhgf#g6')
		{
			if ($_FILES["file"]["size"] > 2000000)
			{
				exit();
			}
			if(isset($_POST["file_name"]))
			{
				if (isValid($_POST["file_name"]) == True)
				{
					$target_file = $_POST["file_name"] . '.jpg';
					move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
				}
			}
		}
	}
	
?>
