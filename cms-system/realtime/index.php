<?php

    $cms_dataList = [];
    //========================================
    require("../ConnMySQL.php");
    
    if ($db_link == TRUE) {
        $db_link->query("SET NAMES \"utf8\"");
    
        $sql_query = "SELECT `id`, `status`, `info`, `last-ping-echo-time`, `lat`, `lon` FROM `01-cms-status` ORDER BY `id` ASC;";
        $stmt = $db_link->prepare($sql_query);
    
        if ($stmt == true) {
            // $stmt->bind_param("s", $var); // 如果需要傳參數，取消註解並調整參數
            $stmt->execute();
    
            // 取代 get_result()，使用 bind_result() 獲取結果
            $stmt->store_result(); // 儲存結果集
            $stmt->bind_result($id, $status, $info, $last_ping_echo_time, $lat, $lon);
    
            $cms_dataList = [];
            while ($stmt->fetch()) {
                $cms_dataList[] = [
                    "id" => $id,
                    "status" => $status,
                    "info" => $info,
                    "last-ping-echo-time" => $last_ping_echo_time,
                    "lat" => $lat,
                    "lon" => $lon
                ];
            }
    
            // print_r($cms_dataList);
    
            $stmt->close();
        }
        $db_link->close();
    }

?>

<!DOCTYPE html>
<html lang="zh-tw">

<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<meta http-equiv="refresh" content="30">
	<link rel="stylesheet" href="/css/w3-4.15.css"/>
	<script src="/js/jquery-3.6.0.js"></script>
	<script>
	
	</script>
	<style>
	</style>
	<title>即時訊息發布設定</title>
	

</head>

<body>

	<div class="w3-top" style="z-index:15;">
		<div class="w3-bar w3-card" id="Navbar" class="" style="background-color:#606060;height:51px;">
			<div href="/" class="w3-wide" style="position:absolute;top:8px;left:10px;text-decoration:none;"><b style="font-size:24px;color:white;">即時訊息發布設定</b></div>
			<div class="w3-right" style="font-size:16px;">
				<!--
				<a href="/" class="w3-bar-item w3-button w3-hide-small w3-hide-medium"><span style="color:white;"><b>AAA</b></span></a>
				-->
			</div>
		</div>
	</div>

<div style="padding-top:51px;"></div>

<div class="w3-sidebar w3-bar-block w3-card w3-animate-left w3-light-gray" style="display:block;left:0px;top:51px;z-index:16;font-size:14px;border:1px solid gray;width:160px;" id="Sidebar">
	<div style="height:10px;"></div>
	
	<!-- ====================================================================== -->
	
    <a href="index.php" class="w3-padding-small w3-bar-item w3-button"><img src="/img/stacks_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:4px;">狀態總覽</span></a>
	
	<!-- ====================================================================== -->
	
    <a href="edit.php?id=1" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-1</span></a>
	<a href="edit.php?id=2" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-2</span></a>
	<a href="edit.php?id=3" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-3</span></a>
	<a href="edit.php?id=4" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-4</span></a>
	<a href="edit.php?id=5" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-5</span></a>
	<a href="edit.php?id=6" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-6</span></a>
	<a href="edit.php?id=7" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-7</span></a>
	<a href="edit.php?id=8" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-8</span></a>
	<a href="edit.php?id=9" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-9</span></a>
	<a href="edit.php?id=10" class="w3-bar-item w3-button" ><img src="/img/view_kanban_24dp_FILL0_wght400_GRAD0_opsz24.svg"></img><span style="margin-left:8px;">CMS-10</span></a>
	
	<!-- ====================================================================== -->
    <div class="w3-padding-32"></div>

</div>


	<div class="w3-container w3-padding-large" style="margin-left:160px;">
	
		<div class="w3-card-4" style="margin-top:20px;width:100%;">
			<header class="w3-container w3-light-gray w3-padding-large w3-border">
				<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M464 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V80c0-26.51-21.49-48-48-48zM224 416H64v-96h160v96zm0-160H64v-96h160v96zm224 160H288v-96h160v96zm0-160H288v-96h160v96z"></path>
				</svg>
				<span style="font-size:16px;margin-left:4px;">詳細資料</span>
			</header>
			<div class="w3-container w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
				<table class="w3-table-all" style="margin-top:14px;margin-bottom:14px;">
					<tr>
						<th style="text-align:center;vertical-align:middle;">ID</th>
						<th style="text-align:center;vertical-align:middle;">狀態</th>
						<th style="text-align:center;vertical-align:middle;">地點</th>
						<th style="text-align:center;vertical-align:middle;">最後一次回應</th>
						<th style="text-align:center;vertical-align:middle;">訊息內容</th>
						<th style="text-align:center;vertical-align:middle;"></th>
					</tr>

<?php
    
    $int_rand_buf = rand();
    
    for($i=0; $i<count($cms_dataList); $i++)
    {
        $status_str_buf = '';
        if ($cms_dataList[$i]["status"] == 0)
        {
            $status_str_buf = '<td style="text-align:center;vertical-align:middle;color:gray;">停用</td>';
        }
        else if ($cms_dataList[$i]["status"] == 1)
        {
            $status_str_buf = '<td style="text-align:center;vertical-align:middle;color:blue;">自動</td>';
        }
        else if ($cms_dataList[$i]["status"] == 2)
        {
            $status_str_buf = '<td style="text-align:center;vertical-align:middle;color:red;">手動</td>';
        }
        
        echo '
<tr>
<td style="text-align:center;vertical-align:middle;">' . $cms_dataList[$i]["id"] . '</td>

' . $status_str_buf . '

<td style="text-align:center;vertical-align:middle;">' . $cms_dataList[$i]["info"] . '</td>
<td style="text-align:center;vertical-align:middle;">' . $cms_dataList[$i]["last-ping-echo-time"] . '</td>
<td style="text-align:center;vertical-align:middle;"><img src="/cms-system/cms-content-img/' . str_pad($cms_dataList[$i]["id"], 2, '0', STR_PAD_LEFT) . '.bmp?t=' . $int_rand_buf . '" width="64px" height="128px" /></td>
<td style="text-align:center;vertical-align:middle;"><a href="edit.php?id=' . $cms_dataList[$i]["id"] . '" class="w3-button w3-padding w3-round w3-orange" style="width:80px;font-size:16px;margin-top:8px;">修改</a></td>
</tr>
';
        
        
    }
    
    
    
?>






				</table>
			</div>
		</div>
		<!--
		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
		
		</footer>
		-->
	</div>

    <div class="w3-padding-32"></div>



    <script>
        var Sidebar = document.getElementById("Sidebar");
        function w3_open()
        {
        	if (Sidebar.style.display === "block")
        	{
        		Sidebar.style.display = "none";
        	}
        	else
        	{
        		Sidebar.style.display = "block";
        	}
        }
        function w3_close()
        {
        	Sidebar.style.display = "none";
        }
    </script>
</body>
</html>
