<?php
    // 指定資料夾路徑
    $folderPath = 'log-1/'; // 替換為你的資料夾路徑
    
    // 使用 glob() 函數來獲取所有 .json 檔案
    $jsonFiles = glob($folderPath . "*.json");
    
    // 檢查是否找到任何檔案
    if ($jsonFiles === false)
    {
        echo "無法讀取資料夾。";
    } elseif (empty($jsonFiles))
    {
        echo "沒有找到任何 JSON 檔案。";
    }
    else
    {
        sort($jsonFiles);
        
        $res = '';
        $flag1 = false;
        // 列出所有 JSON 檔案
        foreach ($jsonFiles as $file)
        {
            $json = file_get_contents($file);
            
            // 將 JSON 轉換成 PHP 陣列
            $data = json_decode($json, true);
            
            $res .= 'DataCollectTime2: ' . $data[0]['grab_travel_route_id'] . "\n";
        }
    	$filename = 'sec-data2.txt';
    	file_put_contents($filename, $res);
    }
?>