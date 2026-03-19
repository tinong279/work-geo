<?php
require 'db.php';

/* 1️⃣ 最新一筆 record */
$latestTime = $pdo->query("
    SELECT MAX(trigger_time) 
    FROM sensor_measurements
")->fetchColumn();

if (!$latestTime) {
    die("尚無資料");
}

/* 2️⃣ AI 資料 */
$stmt = $pdo->prepare("
    SELECT sensor_id, value,raw_value
    FROM sensor_measurements
    WHERE trigger_time = ?
    ORDER BY sensor_id
");
$stmt->execute([$latestTime]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* 3️⃣ 電壓歷史（給圖表用） */
$history = $pdo->query("
    SELECT value, trigger_time
    FROM sensor_measurements
    WHERE sensor_id = 1
    ORDER BY trigger_time ASC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];
foreach ($history as $row) {
    $labels[] = $row['trigger_time'];
    $values[] = (float)$row['value'];
}


/* 4️⃣ 位置對照 */
$positions = [
    1 => '測試機器1',
    2 => '測試機器2',
    3 => '測試機器3',
    4 => '測試機器4',
    5 => '測試機器5',
    6 => '測試機器6',
];
/* 5️⃣ 燈號狀態 */
$deviceVoltage = null;
foreach ($rows as $r) {
    if ($r['sensor_id'] == 1) {
        $deviceVoltage = $r['value'];
        break;
    }
}

$voltageStatus = ($deviceVoltage < 3.3) ? 'red' : 'green';

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>傾斜儀監控</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
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
</head>
<body>

<h2>傾斜儀 詳細資料</h2>

<table>
<thead>
<tr>
    <th>監測位置</th>
    <th>數值(V)</th>
    <th>原始數值</th>
    <th>燈號狀態</th>
    <th>IOT設備電壓(V)</th>
    <th>接收時間</th>
    <th>傳輸頻率</th>
</tr>
</thead>
<tbody>

<?php foreach ($rows as $row): ?>
<tr>
    <td><?= $positions[$row['sensor_id']] ?></td>
    <td><?= number_format($row['value'], 3) ?></td>
    <td><?= $row['raw_value'] ?></td>
    <?php if ($row['sensor_id'] == 1): ?>
        <td rowspan="6">
            <span class="status-dot <?= $voltageStatus ?>"></span>
        </td>
        <td rowspan="6"><?= number_format($deviceVoltage, 2) ?></td>
        <td rowspan="6"><?= $latestTime ?></td>
        <td rowspan="6">10分鐘一筆資料</td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>


</tbody>
</table>

<h2>IOT 設備電壓歷史趨勢</h2>
<canvas id="voltageChart" height="120"></canvas>

<script>
const labels = <?= json_encode($labels) ?>;
const values = <?= json_encode($values) ?>;

new Chart(document.getElementById('voltageChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
        {
            label: '電壓 (V)',
            data: values,
            borderColor: 'green',
            backgroundColor: 'rgba(46,204,113,0.2)',
            tension: 0.3,
            pointRadius: 3
        },
        {
            label: '低電壓警戒 (3.3V)',
            data: Array(values.length).fill(3.3),
            borderColor: 'red',
            borderDash: [5,5],
            pointRadius: 0
        }
        ]
    },
    options: {
        scales: {
            y: {
                min: 3.25,
                max: 3.8
            }
        }
    }
});
</script>

</body>
</html>
