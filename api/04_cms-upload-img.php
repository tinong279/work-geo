<?php
	// 這個檔案主要負責接收使用者上傳的圖片檔案，並將其儲存到指定目錄中（用於 CMS 的即時訊息設定或預覽）

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
	
	$directory_path = dirname($abs_file_path);
	
	if (!is_dir($directory_path)) {
		// 使用 mkdir() 建立目錄
		// 0777 是設置權限（在 Windows 上可能無效），true 代表遞迴建立所有不存在的目錄
		if (mkdir($directory_path, 0777, true)) {
			// echo "目錄建立成功：$directory_path";
		} else {
			// echo "目錄建立失敗：$directory_path";
		}
	} else {
		// echo "目錄已存在：$directory_path";
	}
	
	$fileTmpPath = $_FILES["image"]["tmp_name"];
	move_uploaded_file($fileTmpPath, $abs_file_path);
	
	$res .= '"status":"ok"' . ',';
	$res .= '"url":"' . $file_path . '"' . '';
	
	
	$res .= '}';
	header('Content-Type: application/json');
	echo $res;
	
?>
