<?php
	// 這個檔案的功能是將 CMS 的文字訊息轉換成 BMP 圖片檔

	$exe_path = 'C:/Tools/02_cms-text-to-bmp/main.exe';
	$type = '';
	$msg = '';
	$file_path = '';
	$root_path = $_SERVER['DOCUMENT_ROOT'];
	//========================================
	$res = '';
	$res .= '{';
	//========================================
	if (isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	
	if (isset($_REQUEST['msg']))
	{
		$msg = $_REQUEST['msg'];
	}
	
	//========================================
	$today = date('Y-m-d');
	
	$timestamp = time();
	$hash = hash('sha3-256', $timestamp . ',' . rand());
	
	$file_path = '/api/auto-cms-bmp/' . $today . '/' . $hash . '.bmp';
	$abs_file_path = $root_path . $file_path;
	
	if ($type == '1')
	{
		$msg_encoded = base64_encode($msg);
		
		$cmd = '';
		$cmd .= '' . $exe_path . '' . ' ';
		$cmd .= '"' . $type . '"' . ' ';
		$cmd .= '"' . $msg_encoded . '"' . ' ';
		$cmd .= '"' . $abs_file_path . '"';
		
		exec($cmd, $output, $return_var);
		
		$res .= '"status":"ok"' . ',';
		$res .= '"url":"' . $file_path . '"' . '';
	}
	else if($type == '2')
	{
		$msg_encoded = base64_encode($msg);
		
		$cmd = '';
		$cmd .= '' . $exe_path . '' . ' ';
		$cmd .= '"' . $type . '"' . ' ';
		$cmd .= '"' . $msg_encoded . '"' . ' ';
		$cmd .= '"' . $abs_file_path . '"';
		
		exec($cmd, $output, $return_var);
		
		$res .= '"status":"ok"' . ',';
		$res .= '"url":"' . $file_path . '"' . '';
	}
	else if ($type == '9')
	{
		$msg_encoded = base64_encode($msg);
		
		$cmd = '';
		$cmd .= '' . $exe_path . '' . ' ';
		$cmd .= '"' . '1' . '"' . ' ';
		$cmd .= '"' . $msg_encoded . '"' . ' ';
		$cmd .= '"' . $abs_file_path . '"';
		
		exec($cmd, $output, $return_var);
		
		header('Location: ' . $file_path);
		// echo $file_path;
		exit();
	}
	$res .= '}';
	header('Content-Type: application/json');
	echo $res;
	
?>
