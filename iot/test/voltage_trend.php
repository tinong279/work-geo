<?php
require 'db.php';

$stmt = $pdo->query("
    SELECT voltage, created_at
    FROM device_records
    ORDER BY created_at ASC
    LIMIT 50
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 拆成 JS 好用的陣列
$labels = [];
$values = [];

foreach ($data as $row) {
    $labels[] = $row['created_at'];
    $values[] = (float)$row['voltage'];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>電壓歷史趨勢</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h2>IOT 設備電壓歷史趨勢</h2>

<canvas id="voltageChart" height="100"></canvas>

<script>
const labels = <?= json_encode($labels) ?>;
const values = <?= json_encode($values) ?>;

new Chart(document.getElementById('voltageChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: '電壓 (V)',
            data: values,
            borderColor: 'green',
            backgroundColor: 'rgba(46,204,113,0.2)',
            tension: 0.3,
            pointRadius: 3
        }]
    },
    options: {
        scales: {
            y: {
                min: 3.56,
                max: 3.64
            }
        }
    }
});

datasets: [
{
  label: '電壓 (V)',
  data: values,
  borderColor: 'green'
},
{
  label: '低電壓警戒 (3.3V)',
  data: Array(values.length).fill(3.3),
  borderColor: 'red',
  borderDash: [5,5],
  pointRadius: 0
}
]

</script>

</body>
</html>
