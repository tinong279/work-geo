<?php
    
    require("C:\\xampp\\htdocs\\default\\session.php");
    require("C:\\xampp\\htdocs\\default\\account-session.php");
    require("C:\\xampp\\htdocs\\default\\resources.php");
    require("C:\\xampp\\htdocs\\default\\variable.php");
    require("C:\\xampp\\htdocs\\default\\history-func.php");
    require("C:\\xampp\\htdocs\\default\\public-func.php");
    
    $err_msg = '';
    $user_account_table = '';
    
    if (isset($_GET['msg']))
    {
        switch ($_GET['msg'])
        {
            case 100:
                $err_msg = '帳號新增成功';
                break;
            case 101:
                $err_msg = '字元異常';
                break;
            case 102:
                $err_msg = '帳號已存在';
                break;
            case 200:
                $err_msg = '編輯成功';
                break;
            case 201:
                $err_msg = '字元異常';
                break;
            case 202:
                $err_msg = '帳號不存在';
                break;
            case 203:
                $err_msg = '權限不足';
                break;
            case 300:
                $err_msg = '刪除成功';
                break;
            case 301:
                $err_msg = '字元異常';
                break;
            case 302:
                $err_msg = '帳號不存在';
                break;
            case 303:
                $err_msg = '權限不足';
                break;
        }
    }
    
    require("C:\\xampp\\htdocs\\default\\ConnMySQL.php");
	if ($db_link == TRUE)
	{
		$db_link->query("SET NAMES \"utf8\"");
		
		$sql_query = "SELECT uid, last_login, ip_addr, creator, name FROM `user-info` WHERE level<=2 ORDER BY sn ASC;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == TRUE)
		{
			// $stmt->bind_param("iss", $chn, $date_start, $date_stop);
			$stmt->execute();
			$stmt->store_result();
			$data_count = $stmt->num_rows;
			if ($data_count > 0)
			{
				$stmt->bind_result
				(
			        $data_buf["uid"],
			        $data_buf["last_login"],
			        $data_buf["ip_addr"],
			        $data_buf["creator"],
			        $data_buf["name"]
			    );
			    
			    $count_buf = 0;
			    while ($stmt->fetch())
			    {
			        $user_account_table .= '<tr>';
			        $user_account_table .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["uid"] . '</td>' . "\n";
			        $user_account_table .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["name"] . '</td>' . "\n";
			        $user_account_table .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["last_login"] . '</td>' . "\n";
			        $user_account_table .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["ip_addr"] . '</td>' . "\n";
			        $user_account_table .= '<td style="text-align:center;vertical-align:middle;">' . $data_buf["creator"] . '</td>' . "\n";
			        $user_account_table .= '<td style="text-align:center;vertical-align:middle;">' . "\n";
			        $user_account_table .= '<button onclick="document.getElementById(\'email_modify\').value=\'' . $data_buf["uid"] . '\';document.getElementById(\'name_modify\').value=\'' . $data_buf["name"] . '\';document.getElementById(\'modify_user\').style.display=\'block\';" class="w3-button w3-round w3-orange" style="width:60px;font-size:12px;margin-left:2px;margin-right:2px;">編輯</button>' . "\n";
			        $user_account_table .= '<button onclick="document.getElementById(\'email_delete\').value=\'' . $data_buf["uid"] . '\';document.getElementById(\'name_delete\').value=\'' . $data_buf["name"] . '\';document.getElementById(\'delete_user\').style.display=\'block\';" class="w3-button w3-round w3-red" style="width:60px;font-size:12px;margin-left:2px;margin-right:2px;">刪除</button>' . "\n";
			        $user_account_table .= '</tr>' . "\n";
			    }
				$stmt->close();
			}
			else
			{
				$stmt->close();
				//$err_msg = "資料庫異常";
			}
		}
		$db_link->close();
	}
    
?>
<!DOCTYPE html>
<html lang="zh-tw">
	<head>
		<title>使用者管理設定 - <?php echo $system_name; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

<?php get_css_js_link() ?>

		<script language="javascript">
		
		</script>
		
		<style>
			table, td, th
			{
				border: 1px solid #cccccc;
				border-collapse: collapse;
			}
		</style>
	</head>
	<body>

	<!-- Top -->
	<div class="w3-top" style="background-color:#343a40;">
		<div class="w3-bar w3-large">
			<a class="w3-bar-item w3-button w3-left w3-large w3-hide-large w3-padding-16" style="color:white;" href="javascript:void(0)" onclick="w3_open()">&#9776;</a>
			<label class="w3-bar-item w3-padding-16" style="color:white;font-size:18px;"><?php echo $system_name; ?></label>
			<a class="w3-bar-item w3-button w3-right w3-large w3-padding-16" style="color:white;" title="登出" href="logout.php">
				<svg style="height:20px;filter:invert(0%);" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 512 512">
					<path d="M512 128v256c0 53.02-42.98 96-96 96h-72C330.7 480 320 469.3 320 456c0-13.26 10.75-24 24-24H416c26.4 0 48-21.6 48-48V128c0-26.4-21.6-48-48-48h-72C330.7 80 320 69.25 320 56C320 42.74 330.7 32 344 32H416C469 32 512 74.98 512 128zM367.9 273.9L215.5 407.6C209.3 413.1 201.3 416 193.3 416c-4.688 0-9.406-.9687-13.84-2.969C167.6 407.7 160 396.1 160 383.3V328H40C17.94 328 0 310.1 0 288V224c0-22.06 17.94-40 40-40H160V128.7c0-12.75 7.625-24.41 19.41-29.72C191.5 93.56 205.7 95.69 215.5 104.4l152.4 133.6C373.1 242.6 376 249.1 376 256S373.1 269.4 367.9 273.9zM315.8 256L208 161.1V232h-160v48h160v70.03L315.8 256z"></path>
				</svg>
			</a>
		</div>
	</div>

	<!-- Sidebar -->
	<div class="w3-sidebar w3-bar-block w3-collapse w3-animate-left" style="z-index:3;width:240px;background-color:#212529;" id="mySidebar">
	
		<div>

<?php get_sidebar_html(3); ?>

			<div style="margin-bottom:100px;"></div>
		</div>
		
	</div>
	
	<div class="w3-overlay w3-hide-large" onclick="w3_close()" id="myOverlay"></div>
	
	<div class="w3-main w3-container w3-padding-large" style="margin-left:240px;margin-top:59px;">
	
		<h1>使用者管理設定</h1>
		
		<div class="w3-card-4" style="margin-top:10px;">

			<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom">

                    <div>
                		<button onclick="document.getElementById('create_user').style.display='block';" class="w3-button w3-padding w3-round w3-blue" style="width:100px;font-size:16px;">新增</button>
                		<span class="w3-padding" style="font-size:16px;color:red;text-align:center;vertical-align:middle;"><b><?php echo $err_msg; ?></b></span>
                    </div>
                
			</div>

		</div>

<div class="w3-card-4" style="margin-top:20px;">
<header class="w3-container w3-light-gray w3-padding-large w3-border">
<svg style="height:16px;" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 192 512">
<path d="M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z"></path>
</svg>
<span style="font-size:16px;margin-left:4px;">帳號清單</span>
</header>
<div class="w3-container w3-padding-16 w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
<table class="w3-table-all">
<tbody><tr>
<th style="text-align:center;vertical-align:middle;">帳號</th>
<th style="text-align:center;vertical-align:middle;">名稱</th>
<th style="text-align:center;vertical-align:middle;">最近登入時間</th>
<th style="text-align:center;vertical-align:middle;">登入位址</th>
<th style="text-align:center;vertical-align:middle;">建立者</th>
<th style="text-align:center;vertical-align:middle;">功能選單</th>
</tr>

<?php echo $user_account_table; ?>

</tbody></table>
</div>
</div>

		<footer class="w3-panel w3-padding-32 w3-card-4 w3-light-grey w3-center w3-opacity">
				Copyright © 基能科技股份有限公司
		</footer>
		
	</div>

<div id="create_user" class="w3-modal">
<div class="w3-modal-content" style="max-width:480px;">
<div class="w3-bar w3-blue">
<a class="w3-bar-item w3-padding-8" style="font-size:16px; vertical-align:middle;">新增帳號</a>
<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('create_user').style.display='none'">&times;</a>
</div>
<div class="w3-content w3-center w3-padding">
<form action="account-add.php" method="post">
<input type="hidden" name="token" value="<?php echo $_SESSION['token1']; ?>">
<p>
<h5 class="w3-left">帳號</h5>
<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="text" name="email" id="email_add" value="" placeholder="" required="">
</p>
<p>
<h5 class="w3-left">密碼</h5>
<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="password" name="password" id="password_add" value="" placeholder="" required="">
</p>
<p>
<h5 class="w3-left">名稱</h5>
<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="text" name="name" id="name" value="" placeholder="" required="">
</p>
<div class="w3-center">
<button class="w3-blue w3-btn" style="width:25%;font-size:16px;margin-bottom:12px;" name="submit" type="submit">送出</button>
</div>
</form>
</div>
</div>
</div>

<div id="modify_user" class="w3-modal">
<div class="w3-modal-content" style="max-width:480px;">
<div class="w3-bar w3-blue">
<a class="w3-bar-item w3-padding-8" style="font-size:16px; vertical-align:middle;">編輯帳號</a>
<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('modify_user').style.display='none'">&times;</a>
</div>
<div class="w3-content w3-center w3-padding">
<form action="account-modify.php" method="post">
<input type="hidden" name="token" value="<?php echo $_SESSION['token1']; ?>">
<p>
<h5 class="w3-left">帳號</h5>
<input class="w3-input w3-padding w3-border w3-light-gray" style="font-size:16px;" type="text" name="email" id="email_modify" value="" placeholder="" readonly required="">
</p>
<p>
<h5 class="w3-left">變更密碼</h5>
<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="password" name="password" id="password_modify" value="" placeholder="" required="">
</p>
<p>
<h5 class="w3-left">名稱</h5>
<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="text" name="name" id="name_modify" value="" placeholder="" required="">
</p>
<div class="w3-center">
<button class="w3-blue w3-btn" style="width:25%;font-size:16px;margin-bottom:12px;" name="submit" type="submit">送出</button>
</div>
</form>
</div>
</div>
</div>

<div id="delete_user" class="w3-modal">
<div class="w3-modal-content" style="max-width:480px;">
<div class="w3-bar w3-blue">
<a class="w3-bar-item w3-padding-8" style="font-size:16px; vertical-align:middle;">刪除帳號</a>
<a class="w3-bar-item w3-button w3-hover-grey w3-right" style="cursor:pointer; padding-top:12px; padding-bottom:12px;" onclick="document.getElementById('delete_user').style.display='none'">&times;</a>
</div>
<div class="w3-content w3-center w3-padding">
<form action="account-delete.php" method="post">
<input type="hidden" name="token" value="<?php echo $_SESSION['token1']; ?>">
<p>
<h5 class="w3-left">帳號</h5>
<input class="w3-input w3-padding w3-border w3-light-gray" style="font-size:16px;" type="text" name="email" id="email_delete" value="" placeholder="" readonly required="">
</p>
<p>
<h5 class="w3-left">名稱</h5>
<input class="w3-input w3-padding w3-border w3-light-gray" style="font-size:16px;" type="text" name="name" id="name_delete" value="" placeholder="" readonly required="">
</p>
<button class="w3-red w3-btn" style="width:25%;font-size:16px;margin-bottom:12px;" name="submit" type="submit">刪除</button>
</form>
</div>
</div>
</div>

	<script>
    	function w3_open()
    	{
    	  document.getElementById("mySidebar").style.display = "block";
    	  document.getElementById("myOverlay").style.display = "block";
    	}
    	function w3_close()
    	{
    	  document.getElementById("mySidebar").style.display = "none";
    	  document.getElementById("myOverlay").style.display = "none";
    	}
	</script>
	</body>
</html>
