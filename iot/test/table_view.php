<?php
require 'db.php';

/* 1️⃣ 取最新一筆 record */
$record = $pdo->query(
    "SELECT * FROM device_records ORDER BY id DESC LIMIT 1"
)->fetch();

if (!$record) {
    die("尚無資料");
}

/* 2️⃣ 取該 record 的 AI 資料 */
$stmt = $pdo->prepare(
    "SELECT ai_channel, ai_value
     FROM device_ai_values
     WHERE record_id = ?
     ORDER BY ai_channel"
);
$stmt->execute([$record['id']]);
$aiRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* 3️⃣ 位置對照（你之後可改成資料表） */
$positions = [
    0 => '測試機器0',
    1 => '測試機器1',
    2 => '測試機器2',
    3 => '測試機器3',
    4 => '測試機器4',
    5 => '測試機器5',
];
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>傾斜儀 詳細資料</title>
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }

.status-dot {
    display: inline-block;
    width: 14px;
    height: 14px;
    border-radius: 50%;
}

.green { background-color: #2ecc71; }
.red   { background-color: #e74c3c; }
</style>

</style>
</head>
<body>

<h2>傾斜儀 詳細資料</h2>

<table>
<thead>
<tr>
    <th>監測位置</th>
    <th>燈號狀態</th>
    <th>數值(V)</th>
    <th>IOT設備電壓(V)</th>
    <th>接收時間</th>
    <th>傳輸頻率</th>
</tr>
</thead>

<tbody>
<?php
// 🔴 先算好電壓燈號（只算一次）
$voltageStatus = ($record['voltage'] < 3.3) ? 'red' : 'green';
?>
<?php foreach ($aiRows as $row): ?>
<tr>
    <td><?= $positions[$row['ai_channel']] ?></td>
    <!-- <td class="green">●</td> -->
    <td><?= number_format($row['ai_value'], 3) ?></td>

    <!-- 只有 AI0 顯示電壓與時間 -->
    <?php if ($row['ai_channel'] == 0): ?>
        <td rowspan="6">
            <span class="status-dot <?= $voltageStatus ?>"></span>
        </td>
        <td rowspan="6"><?= number_format($record['voltage'], 2) ?></td>
        <td rowspan="6"><?= $record['created_at'] ?></td>
        <td rowspan="6">10分鐘一筆資料</td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</body>
</html>
