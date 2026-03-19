<?php
// ---------------------測試增加0114------------------------------------
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------------------
$_session_status = 0;
session_start();
if (isset($_SESSION['uid']) == TRUE) {
	if (strlen($_SESSION['uid']) > 0) {
		$_session_status = 1;
	}
}
if ($_session_status == 1) {
	header('Location: home.php');
	exit;
}

require("../../resources.php");
require("../../variable.php");

//---------------------------------------------------------
function remove_special_char($buffer, $keyword)
{
	$count_buf = strlen($keyword);
	for ($i = 0; $i < $count_buf; $i++) {
		$buffer = str_replace($keyword[$i], "", $buffer);
	}
	return $buffer;
}
function hcaptcha_check($response, $remoteip)
{
	$result = false;
	$url = 'https://hcaptcha.com/siteverify?';
	$url .= 'secret=' . '0x21E53c915CA08ebD402C3f7976eE5aedFC3A9305';
	$url .= '&response=' . $response;
	$url .= '&remoteip=' . $remoteip;
	$url .= '&sitekey=' . 'a4f5effc-2f35-426a-b18c-35b710d58ad4';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$output = curl_exec($ch);
	curl_close($ch);
	$output = json_decode($output);
	if ($output->success) {
		$result = true;
	}
	return $result;
}
//---------------------------------------------------------
$_submit_flag = isset($_POST["submit"]);

$_email = "";
$_password = "";

$_sn = 0;
$_level = 0;
$_name = '';

$_time1 = new DateTime;
$_time1 = $_time1->format('Y-m-d H:i:s');

$_time2 = new DateTime;
$_time2->modify('-10 minutes');
$_time2 = $_time2->format('Y-m-d H:i:s');

$_token1 = hash('sha3-512', $_time1 . "," . rand() . "," . rand() . "," . rand());

$_ip = $_SERVER['REMOTE_ADDR'];
$_ua = $_SERVER['HTTP_USER_AGENT'];
//---------------------------------------------------------
$_sys_status = 0;
$_sys_msg = "";
//---------------------------------------------------------
$login_fail_flag = false;
$max_login_fail_count = 5;
require("../../ConnMySQL.php");
if ($db_link == TRUE) {
	$db_link->query("SET NAMES \"utf8\"");
	$sql_query = "SELECT `sn` FROM `miaoli-62`.syslog WHERE `type`='login_fail' AND `ip`=? AND `time`>=?;";
	$stmt = $db_link->prepare($sql_query);
	if ($stmt == TRUE) {
		$stmt->bind_param("ss", $_SERVER['REMOTE_ADDR'], $_time2);
		$stmt->execute();
		$stmt->store_result();
		$data_count = $stmt->num_rows;
		if ($data_count >= $max_login_fail_count) {
			$login_fail_flag = true;
		}
		$stmt->close();
	}
	// $db_link->close();
}
//---------------------------------------------------------
if ($_sys_status == 0 && $_submit_flag == TRUE) {
	if (isset($_POST["email"])) {
		$_email = $_POST["email"];
		$_email = htmlspecialchars_decode($_email, ENT_QUOTES);
		$_email = remove_special_char($_email, "<>'\"=;");
	}
	if (isset($_POST["password"])) {
		$_password = $_POST["password"];
	}
}
// die($_password);
//---------------------------------------------------------
if ($_sys_status == 0 && $_submit_flag == TRUE) {
	$_sys_status = -1;
	$pattern = '/^([0-9A-Za-z\@\-\.]+)$/';
	if (preg_match($pattern, $_email) == FALSE) {
		$_sys_msg = "帳號或密碼錯誤";
	} else if (strlen($_email) >= 100) {
		$_sys_msg = "帳號或密碼錯誤";
	} else if (strlen($_password) == 0) {
		$_sys_msg = "帳號或密碼錯誤";
	} else {
		// 將加密與顯示邏輯移到判斷驗證碼之前，或是兩邊都加
		// $_password = hash('sha3-512', $user_login_hash_key . $_password);

		// 強制顯示結果並停止
		// echo "您的 Key 是：[" . $user_login_hash_key . "]<br>";
		// echo "您輸入的密碼是：[" . $_POST["password"] . "]<br>";
		// echo "程式算出的 Hash 是：<br><b>" . $_password . "</b>";


		// 原本的邏輯會被 die() 擋住，等複製完再刪除上面這段即可
		if ($login_fail_flag == true) {
			$check_flag_buf = hcaptcha_check($_POST['h-captcha-response'], $_ip);
			if ($check_flag_buf == true) {
				$_password = hash('sha3-512', $user_login_hash_key . $_password);
				$_sys_status = 0;
			} else {
				$_sys_msg = "hCaptcha異常";
			}
		} else {
			$_password = hash('sha3-512', $user_login_hash_key . $_password);
			$_sys_status = 0;
		}
	}
}
//---------------------------------------------------------
if ($_sys_status == 0 && $_submit_flag == TRUE) {
	$_sys_status = -1;
	require("../../ConnMySQL.php");
	if ($db_link == TRUE) {
		$db_link->query("SET NAMES \"utf8\"");
		$sql_query = "SELECT sn, level, name FROM `user-info` WHERE uid=? AND pw=? AND status=1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt == TRUE) {
			$stmt->bind_param("ss", $_email, $_password);
			$stmt->execute();
			$stmt->store_result();
			$data_count = $stmt->num_rows;
			if ($data_count == 1) {
				$stmt->bind_result($data_buf["sn"], $data_buf["level"], $data_buf["name"]);
				$stmt->fetch();
				$_sn = $data_buf["sn"];
				$_level = $data_buf["level"];
				$_name = $data_buf["name"];
				$stmt->close();

				$sql_query = "UPDATE `user-info` SET ip_addr=?, last_login=?, ua=? WHERE sn=?;";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == TRUE) {
					$stmt->bind_param("sssi", $_ip, $_time1, $_ua, $_sn);
					$stmt->execute();
					$stmt->close();
					$_sys_status = 1;

					$sql_query = "INSERT INTO syslog (type, msg, ip) VALUES ('login_success', ?, ?)";
					$stmt = $db_link->prepare($sql_query);
					if ($stmt == TRUE) {
						$str_buf = "";
						$str_buf .= "[ip:";
						$str_buf .= $_SERVER['REMOTE_ADDR'];
						$str_buf .= "]";
						$str_buf .= "[uid:";
						$str_buf .= $_email;
						$str_buf .= "]";
						$str_buf .= "[ua:";
						$str_buf .= $_SERVER['HTTP_USER_AGENT'];
						$str_buf .= "]";
						$stmt->bind_param("ss", $str_buf, $_SERVER['REMOTE_ADDR']);
						$stmt->execute();
						$stmt->close();
					}
				} else {
					$_sys_msg = "系統異常";
				}
			} else {
				$stmt->close();
				$_sys_msg = "帳號或密碼錯誤";
				$sql_query = "INSERT INTO syslog (type, msg, ip) VALUES ('login_fail', ?, ?)";
				$stmt = $db_link->prepare($sql_query);
				if ($stmt == TRUE) {
					$str_buf = "";
					$str_buf .= "[ip:";
					$str_buf .= $_SERVER['REMOTE_ADDR'];
					$str_buf .= "]";
					$str_buf .= "[uid:";
					$str_buf .= $_email;
					$str_buf .= "]";
					$str_buf .= "[ua:";
					$str_buf .= $_SERVER['HTTP_USER_AGENT'];
					$str_buf .= "]";
					$stmt->bind_param("ss", $str_buf, $_SERVER['REMOTE_ADDR']);
					$stmt->execute();
					$stmt->close();
				}
			}
		}
		$db_link->close();
	}
}
//---------------------------------------------------------
if ($_sys_status == 1) {
	session_regenerate_id();

	$_SESSION['sn'] = $_sn;
	$_SESSION['uid'] = $_email;
	$_SESSION['ip'] = $_ip;
	$_SESSION['level'] = $_level;
	$_SESSION['token1'] = $_token1;
	$_SESSION['name'] = $_name;

	header('Location: home.php');
	exit;
}
//---------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="zh-tw">

<head>
	<title>系統登入 - <?php echo $system_name; ?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php get_css_js_link() ?>

	<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
	<script language="javascript">
		function checkForm() {
			var response_len = hcaptcha.getResponse().length;


			<?php
			if ($login_fail_flag == false) {
				echo 'response_len = 1;';
			}
			?>


			if (response_len == 0) {
				alert("未勾選圖形驗證");
				return false;
			} else {
				if ($("#email").val().length == 0) {
					alert("帳號不能空白");
					return false;
				} else if ($("#password").val().length == 0) {
					alert("密碼不能空白");
					return false;
				} else {
					return true;
				}
			}
		}
	</script>
</head>

<body>

	<div style="padding-top:60px;"></div>

	<div class="w3-container w3-content">
		<div class="w3-card-4" style="max-width:420px;margin:auto;">
			<header class="w3-container w3-blue">
				<h4 class="w3-center"><b><?php echo $system_name; ?></b></h4>
				<h4 class="w3-center"><b>系統登入</b></h4>
			</header>
			<div class="w3-container">
				<div class="w3-text-red w3-center" style="margin-top:12px;"><?php echo $_sys_msg; ?></div>
				<form action="" method="post" onSubmit="return checkForm()">
					<p>
						<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="text" name="email" id="email" value="" placeholder="帳號" required="" />
					</p>
					<p>
						<input class="w3-input w3-padding w3-border" style="font-size:16px;" type="password" name="password" id="password" value="" placeholder="密碼" required="" />
					</p>

					<?php
					if ($login_fail_flag == false) {
						echo '<!--';
					}
					?>

					<div class="h-captcha w3-center" data-sitekey="a4f5effc-2f35-426a-b18c-35b710d58ad4"></div>

					<?php
					if ($login_fail_flag == false) {
						echo '-->';
					}
					?>

					<div class="w3-center">
						<button class="w3-blue w3-btn" style="width:25%;font-size:16px;margin-bottom:18px;" type="submit" name="submit" value="submit">登入</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>

</html>