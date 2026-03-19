<?php
// --- 第一步：全域資源載入 (全頁只執行一次，大幅加快讀取) ---
require("function.php");
require("variable.php");
require("ConnMySQL.php");
require("lora-offset.php");

// 設定連線語系 (全域設定)
if ($db_link) {
	$db_link->query("SET NAMES \"utf8\"");
}

// --- 第二步：定義數據獲取函式 ---

function home_get_inclinometer_all()
{
	// 依序執行 6.2K 的左、右傾斜儀數據抓取
	home_get_inclinometer_with_offset_11("6.2K 傾斜儀(左)");
	home_get_inclinometer_with_offset_12("6.2K 傾斜儀(右)");
}

function home_get_raingauge_all()
{
	global $id_19_count_offset;
	home_get_raingauge_with_offset("6.2K 雨量筒", 19, $id_19_count_offset);
}

function home_get_inclinometer_with_offset_11($loc_name)
{
	global $db_link;
	if ($db_link) {
		$time = '';
		$ad1 = 0;
		$ad2 = 0;
		$battery = 0;

		// X軸 (chn_id=1101)
		$sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=1101 ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->execute();
			$stmt->bind_result($t, $v);
			if ($stmt->fetch()) {
				$time = $t;
				$ad1 = $v;
			}
			$stmt->close();
		}

		// Y軸 (chn_id=1102)
		$sql_query = "SELECT `val` FROM `chndata` WHERE `chn_id`=1102 ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->execute();
			$stmt->bind_result($v);
			if ($stmt->fetch()) {
				$ad2 = $v;
			}
			$stmt->close();
		}

		// 電池 (chn_id=1105)
		$sql_query = "SELECT `val` FROM `chndata` WHERE `chn_id`=1105 ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->execute();
			$stmt->bind_result($v);
			if ($stmt->fetch()) {
				$battery = $v;
			}
			$stmt->close();
		}

		render_62k_inclinometer_row($loc_name, $ad1, $ad2, $battery, $time);
	}
}

function home_get_inclinometer_with_offset_12($loc_name)
{
	global $db_link;
	if ($db_link) {
		$time = '';
		$ad1 = 0;
		$ad2 = 0;
		$battery = 0;

		// X軸 (chn_id=1201)
		$sql_query = "SELECT `time`, `val` FROM `chndata` WHERE `chn_id`=1201 ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->execute();
			$stmt->bind_result($t, $v);
			if ($stmt->fetch()) {
				$time = $t;
				$ad1 = $v;
			}
			$stmt->close();
		}

		// Y軸 (chn_id=1202)
		$sql_query = "SELECT `val` FROM `chndata` WHERE `chn_id`=1202 ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->execute();
			$stmt->bind_result($v);
			if ($stmt->fetch()) {
				$ad2 = $v;
			}
			$stmt->close();
		}

		// 電池 (chn_id=1205)
		$sql_query = "SELECT `val` FROM `chndata` WHERE `chn_id`=1205 ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->execute();
			$stmt->bind_result($v);
			if ($stmt->fetch()) {
				$battery = $v;
			}
			$stmt->close();
		}

		render_62k_inclinometer_row($loc_name, $ad1, $ad2, $battery, $time);
	}
}

// 抽取共用的 HTML 渲染邏輯
function render_62k_inclinometer_row($loc_name, $ad1, $ad2, $battery, $time)
{
	$output = '';
	$output .= '<tr>' . "\n";
	$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' X軸' . '</td>' . "\n";
	$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($ad1, strtotime($time)) . '</span></td>' . "\n";
	$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $ad1) . '</td>' . "\n";
	$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $battery) . '</td>' . "\n";
	$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">' . $time . '</td>' . "\n";
	$output .= '<td rowspan="2" style="text-align:center;vertical-align:middle;">10分鐘一筆資料</td>' . "\n";
	$output .= '</tr>' . "\n";
	$output .= '<tr>' . "\n";
	$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . ' Y軸' . '</td>' . "\n";
	$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_inclinometer($ad2, strtotime($time)) . '</span></td>' . "\n";
	$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $ad2) . '</td>' . "\n";
	$output .= '</tr>' . "\n";
	echo $output;
}

function home_get_raingauge_with_offset($loc_name, $id_number, $count_offset)
{
	global $db_link;
	if ($db_link) {
		$sql_query = "SELECT time, _count, _battery FROM `rawdata` WHERE _id=? ORDER BY sn DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->bind_param("i", $id_number);
			$stmt->execute();
			$stmt->bind_result($time, $count, $battery);
			if ($stmt->fetch()) {
				$final_count = ($count * 0.5) + $count_offset;
				$output = '<tr>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">' . $loc_name . '</td>' . "\n";
				// 注意此處調用 1903 作為雨量筒識別
				$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_raingauge2(1903, strtotime($time)) . '</span></td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $final_count) . '</td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">' . sprintf("%01.2f", $battery) . '</td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">' . $time . '</td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">10分鐘一筆資料</td>' . "\n";
				$output .= '</tr>' . "\n";
				echo $output;
			}
			$stmt->close();
		}
	}
}

function home_get_line_power($id_number)
{
	global $db_link;
	if ($db_link) {
		$sql_query = "SELECT `time`, `_data` FROM `rawdata` WHERE `_id`=? ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->bind_param("i", $id_number);
			$stmt->execute();
			$stmt->bind_result($time, $data);
			if ($stmt->fetch()) {
				$output = '<tr>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">市電監測</td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_linepower(strval($data), strtotime($time)) . '</span></td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">' . $time . '</td>' . "\n";
				$output .= '</tr>' . "\n";
				echo $output;
			}
			$stmt->close();
		}
	}
}

function home_get_battery_voltage($id_number)
{
	global $db_link;
	if ($db_link) {
		$sql_query = "SELECT `time`, `_data` FROM `rawdata` WHERE _id=? ORDER BY `sn` DESC LIMIT 1;";
		$stmt = $db_link->prepare($sql_query);
		if ($stmt) {
			$stmt->bind_param("i", $id_number);
			$stmt->execute();
			$stmt->bind_result($time, $val);
			if ($stmt->fetch()) {
				$v_calc = (floatval($val) / 65535) * 20;
				$output = '<tr>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">電池電壓</td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;"><span>' . get_status_circle_batteryvoltage($v_calc, strtotime($time)) . ' ' . number_format($v_calc, 2) . ' V</span></td>' . "\n";
				$output .= '<td style="text-align:center;vertical-align:middle;">' . $time . '</td>' . "\n";
				$output .= '</tr>' . "\n";
				echo $output;
			}
			$stmt->close();
		}
	}
}
