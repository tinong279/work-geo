<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/db_connection.php';

// 處理排程新增/刪除
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo = getDbConnection();
        $action = $_POST['action'] ?? '';

        if ($action == 'add_schedule') {
            $cms_ids = $_POST['cms_ids'] ?? 'all';
            $schedule_type = $_POST['schedule_type'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $priority = intval($_POST['priority'] ?? 10);

            // 處理圖片上傳
            $image_path = '';
            if (isset($_FILES['schedule_image']) && $_FILES['schedule_image']['error'] == 0) {
                $upload_dir = 'cms-images/schedule/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $filename = time() . '_' . basename($_FILES['schedule_image']['name']);
                $target_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['schedule_image']['tmp_name'], $target_path)) {
                    $image_path = $target_path;

                    // 記錄到圖片庫
                    $stmt = $pdo->prepare("INSERT INTO cms_images (filename, file_path, category, uploaded_by) VALUES (?, ?, 'schedule', ?)");
                    $stmt->execute([$filename, $image_path, $_SESSION['username'] ?? 'admin']);
                }
            }

            if ($image_path) {
                if ($schedule_type == 'daily') {
                    $stmt = $pdo->prepare("INSERT INTO cms_schedule_messages 
                        (cms_ids, schedule_type, start_time, end_time, image_path, priority, created_by)
                        VALUES (?, 'daily', ?, ?, ?, ?, ?)");
                    $stmt->execute([$cms_ids, $start_time, $end_time, $image_path, $priority, $_SESSION['username'] ?? 'admin']);
                } elseif ($schedule_type == 'weekly') {
                    $weekdays = implode(',', $_POST['weekdays'] ?? []);
                    $stmt = $pdo->prepare("INSERT INTO cms_schedule_messages 
                        (cms_ids, schedule_type, start_time, end_time, weekdays, image_path, priority, created_by)
                        VALUES (?, 'weekly', ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$cms_ids, $start_time, $end_time, $weekdays, $image_path, $priority, $_SESSION['username'] ?? 'admin']);
                } elseif ($schedule_type == 'date_range') {
                    $start_date = $_POST['start_date'];
                    $end_date = $_POST['end_date'];
                    $stmt = $pdo->prepare("INSERT INTO cms_schedule_messages 
                        (cms_ids, schedule_type, start_time, end_time, start_date, end_date, image_path, priority, created_by)
                        VALUES (?, 'date_range', ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$cms_ids, $start_time, $end_time, $start_date, $end_date, $image_path, $priority, $_SESSION['username'] ?? 'admin']);
                }

                $success_message = "✓ 排程已新增";
            } else {
                $error_message = "請上傳排程圖片";
            }
        } elseif ($action == 'delete_schedule') {
            $schedule_id = intval($_POST['schedule_id']);
            $stmt = $pdo->prepare("UPDATE cms_schedule_messages SET is_active = 0 WHERE id = ?");
            $stmt->execute([$schedule_id]);
            $success_message = "✓ 排程已刪除";
        }
    } catch (Exception $e) {
        error_log("Schedule operation error: " . $e->getMessage());
        $error_message = '操作失敗: ' . $e->getMessage();
    }
}

// 獲取所有CMS和排程
$cms_devices = [];
$all_schedules = [];

try {
    $pdo = getDbConnection();

    // 查詢CMS狀態
    $stmt = $pdo->prepare("SELECT id, name, ip_address, current_mode FROM cms_status WHERE is_active = 1 ORDER BY id ASC");
    $stmt->execute();
    $cms_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 查詢每個CMS的排程數量
    foreach ($cms_devices as $key => $device) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cms_schedule_messages 
                               WHERE is_active = 1 AND (cms_ids = 'all' OR cms_ids LIKE ?)");
        $stmt->execute(['%' . $device['id'] . '%']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cms_devices[$key]['schedule_count'] = $result['count'] ?? 0;
    }

    // 查詢所有排程（用於管理頁面）
    $stmt = $pdo->prepare("SELECT * FROM cms_schedule_messages WHERE is_active = 1 ORDER BY priority DESC, start_time ASC");
    $stmt->execute();
    $all_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database query error: " . $e->getMessage());
    $error_message = '資料庫查詢錯誤: ' . $e->getMessage();
    $cms_devices = [];
}

?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>排程訊息發布設定 - 雲林縣古坑鄉草嶺地區環境監測</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- 核心樣式 -->
    <link rel="stylesheet" href="assets/css/core/variables.css">
    <link rel="stylesheet" href="assets/css/core/reset.css">
    <link rel="stylesheet" href="assets/css/core/layout.css">

    <!-- 元件樣式 -->
    <link rel="stylesheet" href="assets/css/components/buttons.css">
    <link rel="stylesheet" href="assets/css/components/forms.css">
    <link rel="stylesheet" href="assets/css/components/modals.css">
    <link rel="stylesheet" href="assets/css/components/sidebar.css">

    <!-- 工具樣式 -->
    <link rel="stylesheet" href="assets/css/utils/animations.css">

    <!-- CMS 專用樣式 -->
    <link rel="stylesheet" href="assets/css/cms-styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <svg style="width: 28px; height: 28px; vertical-align: middle; margin-right: 8px;" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,20A7,7 0 0,1 5,13A7,7 0 0,1 12,6A7,7 0 0,1 19,13A7,7 0 0,1 12,20M12,4A9,9 0 0,0 3,13A9,9 0 0,0 12,22A9,9 0 0,0 21,13A9,9 0 0,0 12,4M12.5,8H11V14L15.75,16.85L16.5,15.62L12.5,13.25V8M7.88,3.39L6.6,1.86L2,5.71L3.29,7.24L7.88,3.39M22,5.72L17.4,1.86L16.11,3.39L20.71,7.25L22,5.72Z" />
                    </svg>
                    排程訊息發布設定
                </h1>
                <p class="page-description">設定6個CMS的排程訊息，依星期和時段自動切換顯示內容</p>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="cms-alert cms-alert-success">
                    <strong><?= htmlspecialchars($success_message) ?></strong>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="cms-alert cms-alert-warning">
                    <strong>⚠️ 提示：</strong> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cms_devices)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,20A7,7 0 0,1 5,13A7,7 0 0,1 12,6A7,7 0 0,1 19,13A7,7 0 0,1 12,20M12,4A9,9 0 0,0 3,13A9,9 0 0,0 12,22A9,9 0 0,0 21,13A9,9 0 0,0 12,4M12.5,8H11V14L15.75,16.85L16.5,15.62L12.5,13.25V8M7.88,3.39L6.6,1.86L2,5.71L3.29,7.24L7.88,3.39M22,5.72L17.4,1.86L16.11,3.39L20.71,7.25L22,5.72Z" />
                    </svg>
                    <p>目前沒有CMS設備資料</p>
                </div>
            <?php else: ?>
                <div class="cms-cards-container">
                    <?php foreach ($cms_devices as $device):
                        // 判斷是否為排程模式
                        $is_schedule = ($device['current_mode'] === 'schedule');
                        $card_class = $is_schedule ? '' : ' inactive';
                        $header_class = $is_schedule ? 'schedule' : 'default';
                    ?>
                        <div class="cms-card<?= $card_class ?>">
                            <div class="cms-card-header <?= $header_class ?>">
                                <div class="cms-card-title">
                                    <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z" />
                                    </svg>
                                    <?= htmlspecialchars($device['name']) ?>
                                </div>
                                <span class="cms-card-id">#<?= str_pad($device['id'], 2, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="cms-card-body">
                                <div class="cms-info-row">
                                    <span class="cms-info-label">IP位址</span>
                                    <span class="cms-info-value"><?= htmlspecialchars($device['ip_address']) ?></span>
                                </div>
                                <div class="cms-info-row">
                                    <span class="cms-info-label">當前模式</span>
                                    <span class="cms-info-value">
                                        <?php
                                        $mode_badges = [
                                            'normal' => '<span class="cms-status-badge" style="background:#4CAF50;color:white;">預設</span>',
                                            'manual' => '<span class="cms-status-badge status-manual">即時</span>',
                                            'schedule' => '<span class="cms-status-badge status-auto">排程</span>',
                                            'alert' => '<span class="cms-status-badge" style="background:#f44336;color:white;">警報</span>'
                                        ];
                                        echo $mode_badges[$device['current_mode']] ?? $device['current_mode'];
                                        ?>
                                    </span>
                                </div>
                                <div class="cms-info-row">
                                    <span class="cms-info-label">排程數量</span>
                                    <span class="cms-info-value">
                                        <span class="schedule-count-badge">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M19,19H5V8H19M16,1V3H8V1H6V3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3H18V1M17,12H12V17H17V12Z" />
                                            </svg>
                                            <?= $device['schedule_count'] ?> 個
                                        </span>
                                    </span>
                                </div>

                                <?php if ($device['schedule_count'] > 0): ?>
                                    <div class="schedule-hint">
                                        <svg style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M13,9H11V7H13M13,17H11V11H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                        </svg>
                                        已設定 <?= $device['schedule_count'] ?> 個時段排程
                                    </div>
                                <?php endif; ?>

                                <div class="cms-card-actions">
                                    <button class="cms-action-btn btn-manage" onclick="manageSchedule(<?= $device['id'] ?>)">
                                        <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19,19H5V8H19M16,1V3H8V1H6V3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3H18V1M17,12H12V17H17V12Z" />
                                        </svg>
                                        管理排程
                                    </button>
                                    <button class="cms-action-btn btn-add" onclick="addSchedule()">
                                        <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                                        </svg>
                                        新增排程
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 排程列表 -->
            <?php if (count($all_schedules) > 0): ?>
                <div style="margin-top:30px;">
                    <h3 style="margin-bottom:15px;">📋 所有排程列表</h3>
                    <div style="background:white;border-radius:8px;padding:20px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f5f5f5;border-bottom:2px solid #ddd;">
                                    <th style="padding:12px;text-align:left;color:#333;font-weight:600;">CMS</th>
                                    <th style="padding:12px;text-align:left;color:#333;font-weight:600;">類型</th>
                                    <th style="padding:12px;text-align:left;color:#333;font-weight:600;">時間</th>
                                    <th style="padding:12px;text-align:left;color:#333;font-weight:600;">條件</th>
                                    <th style="padding:12px;text-align:center;color:#333;font-weight:600;" title="當多個排程時段重疊時，優先級高的會優先顯示">優先級 ℹ️</th>
                                    <th style="padding:12px;text-align:center;color:#333;font-weight:600;">圖片</th>
                                    <th style="padding:12px;text-align:center;color:#333;font-weight:600;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_schedules as $schedule): ?>
                                    <tr style="border-bottom:1px solid #eee;">
                                        <td style="padding:12px;color:#1a202c;">
                                            <?php
                                            if ($schedule['cms_ids'] == 'all') {
                                                echo '<span style="color:#2196F3;font-weight:bold;">全部</span>';
                                            } else {
                                                echo 'CMS-' . str_replace(',', ', CMS-', $schedule['cms_ids']);
                                            }
                                            ?>
                                        </td>
                                        <td style="padding:12px;color:#1a202c;">
                                            <?php
                                            $types = ['daily' => '每日', 'weekly' => '每週', 'date_range' => '日期範圍'];
                                            echo $types[$schedule['schedule_type']] ?? $schedule['schedule_type'];
                                            ?>
                                        </td>
                                        <td style="padding:12px;color:#1a202c;">
                                            <?= substr($schedule['start_time'], 0, 5) ?> ~ <?= substr($schedule['end_time'], 0, 5) ?>
                                        </td>
                                        <td style="padding:12px;color:#1a202c;">
                                            <?php
                                            if ($schedule['schedule_type'] == 'weekly') {
                                                $days = ['日', '一', '二', '三', '四', '五', '六'];
                                                $selected = explode(',', $schedule['weekdays']);
                                                $weekday_names = array_map(function ($d) use ($days) {
                                                    return '週' . $days[intval($d)];
                                                }, $selected);
                                                echo implode(', ', $weekday_names);
                                            } elseif ($schedule['schedule_type'] == 'date_range') {
                                                echo $schedule['start_date'] . ' ~ ' . $schedule['end_date'];
                                            } else {
                                                echo '每天';
                                            }
                                            ?>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <span style="background:<?= $schedule['priority'] >= 50 ? '#f44336' : ($schedule['priority'] >= 20 ? '#ff9800' : '#4caf50') ?>;color:white;padding:4px 12px;border-radius:12px;font-size:12px;">
                                                <?= $schedule['priority'] ?>
                                            </span>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <?php if ($schedule['image_path'] && file_exists($schedule['image_path'])): ?>
                                                <img src="<?= htmlspecialchars($schedule['image_path']) ?>?t=<?= time() ?>" style="height:60px;border:1px solid #ddd;">
                                            <?php else: ?>
                                                <span style="color:#999;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('確定要刪除此排程？');">
                                                <input type="hidden" name="action" value="delete_schedule">
                                                <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                                                <button type="submit" style="background:#f44336;color:white;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">刪除</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/change_password_modal.php'; ?>

    <!-- 新增排程Modal -->
    <div id="addScheduleModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:700px;">
            <div class="modal-header">
                <h2>新增排程</h2>
                <span class="modal-close" onclick="closeScheduleModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_schedule">

                    <div class="form-group">
                        <label>套用到 CMS</label>
                        <select name="cms_ids" class="form-control">
                            <option value="all">全部 CMS</option>
                            <?php foreach ($cms_devices as $device): ?>
                                <option value="<?= $device['id'] ?>"><?= htmlspecialchars($device['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>排程類型</label>
                        <select name="schedule_type" class="form-control" onchange="toggleScheduleType(this.value)" required>
                            <option value="daily">每日</option>
                            <option value="weekly">每週</option>
                            <option value="date_range">日期範圍</option>
                        </select>
                    </div>

                    <div id="weekly-options" style="display:none;">
                        <div class="form-group">
                            <label>選擇星期</label>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <label><input type="checkbox" name="weekdays[]" value="0"> 週日</label>
                                <label><input type="checkbox" name="weekdays[]" value="1"> 週一</label>
                                <label><input type="checkbox" name="weekdays[]" value="2"> 週二</label>
                                <label><input type="checkbox" name="weekdays[]" value="3"> 週三</label>
                                <label><input type="checkbox" name="weekdays[]" value="4"> 週四</label>
                                <label><input type="checkbox" name="weekdays[]" value="5"> 週五</label>
                                <label><input type="checkbox" name="weekdays[]" value="6"> 週六</label>
                            </div>
                        </div>
                    </div>

                    <div id="daterange-options" style="display:none;">
                        <div class="form-group">
                            <label>開始日期</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>結束日期</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>開始時間</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>結束時間</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>優先級 (數字越大越優先)</label>
                        <input type="number" name="priority" class="form-control" value="10" min="1" max="100">
                        <small style="color:#666;font-size:12px;margin-top:5px;display:block;">💡 當同一時段有多個排程重疊時，優先級高的會被顯示</small>
                    </div>

                    <div class="form-group">
                        <label>排程圖片 (128x256 BMP) *必填</label>
                        <input type="file" name="schedule_image" class="form-control" accept=".bmp" required onchange="previewScheduleImage(this)">
                        <div id="scheduleImagePreview" style="margin-top:10px;display:none;">
                            <img id="schedulePreviewImg" style="max-height:150px;border:2px solid #ddd;padding:5px;">
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:20px;text-align:right;">
                        <button type="button" class="btn-secondary" onclick="closeScheduleModal()">取消</button>
                        <button type="submit" class="btn-primary">✓ 新增排程</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/sidebar-scripts.js"></script>
    <script src="assets/js/dashboard-scripts.js"></script>
    <script>
        function manageSchedule(id) {
            // 滾動到排程列表並高亮該CMS的排程
            const scheduleSection = document.querySelector('h3');
            if (scheduleSection) {
                scheduleSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // 高亮顯示該CMS的排程（0.5秒後移除高亮）
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cmsCell = row.cells[0].textContent;
                    if (cmsCell.includes('CMS-' + id) || cmsCell.includes('全部')) {
                        row.style.background = '#fff3cd';
                        row.style.transition = 'background 2s';
                        setTimeout(() => {
                            row.style.background = '';
                        }, 2000);
                    }
                });
            }
        }

        function addSchedule() {
            document.getElementById('addScheduleModal').style.display = 'block';
        }

        function closeScheduleModal() {
            document.getElementById('addScheduleModal').style.display = 'none';
        }

        function toggleScheduleType(type) {
            document.getElementById('weekly-options').style.display = type === 'weekly' ? 'block' : 'none';
            document.getElementById('daterange-options').style.display = type === 'date_range' ? 'block' : 'none';
        }

        function previewScheduleImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('schedulePreviewImg').src = e.target.result;
                    document.getElementById('scheduleImagePreview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('addScheduleModal');
            if (event.target == modal) {
                closeScheduleModal();
            }
        }

        // 自動刷新：每3分鐘刷新頁面
        setTimeout(function() {
            location.reload();
        }, 180000); // 180000ms = 3分鐘
    </script>
</body>

</html>