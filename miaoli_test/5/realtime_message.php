<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/db_connection.php';

// 查詢可用的圖片
$image_list = [];
try {
    $pdo_img = getDbConnection();
    $stmt = $pdo_img->query("SELECT filename, file_path, category FROM cms_images ORDER BY category, filename");
    $image_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 如果資料表還沒建立，使用預設圖片
    $image_list = [
        ['filename' => '正常通行', 'file_path' => 'cms-images/normal.bmp', 'category' => 'normal'],
        ['filename' => '雨量警戒', 'file_path' => 'cms-images/alert/rain_level2.bmp', 'category' => 'alert'],
        ['filename' => '雨量行動值', 'file_path' => 'cms-images/alert/rain_level3.bmp', 'category' => 'alert']
    ];
}

// 處理即時訊息發布
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        $pdo = getDbConnection();
        $action = $_POST['action'];

        if ($action == 'publish') {
            $cms_id = intval($_POST['cms_id']);
            $display_type = $_POST['display_type'];

            if ($display_type == 'text') {
                // 文字模式
                $text_content = $_POST['text_content'];
                $text_color = $_POST['text_color'];
                $text_size = intval($_POST['text_size']);

                $stmt = $pdo->prepare("UPDATE cms_status 
                    SET current_mode = 'manual',
                        display_type = 'text',
                        text_content = ?,
                        text_color = ?,
                        text_size = ?,
                        image_path = NULL,
                        trigger_source = 'manual',
                        trigger_value = NULL,
                        trigger_time = GETDATE(),
                        last_updated = GETDATE()
                    WHERE id = ? AND is_active = 1");
                $stmt->execute([$text_content, $text_color, $text_size, $cms_id]);
                $success_message = "✓ CMS-{$cms_id} 文字訊息已發布";
            } else {
                // 圖片模式
                $image_path = '';

                // 處理圖片上傳
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
                    $upload_dir = 'cms-images/manual/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $filename = time() . '_' . basename($_FILES['image_file']['name']);
                    $target_path = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_path)) {
                        $image_path = $target_path;

                        // 記錄到圖片庫
                        $stmt = $pdo->prepare("INSERT INTO cms_images (filename, file_path, category, uploaded_by) VALUES (?, ?, 'manual', ?)");
                        $stmt->execute([$filename, $image_path, $_SESSION['username'] ?? 'admin']);
                    }
                } else {
                    // 使用現有圖片
                    $image_path = $_POST['existing_image'] ?? '';
                }

                if ($image_path) {
                    $stmt = $pdo->prepare("UPDATE cms_status 
                        SET current_mode = 'manual',
                            display_type = 'image',
                            text_content = NULL,
                            image_path = ?,
                            trigger_source = 'manual',
                            trigger_value = NULL,
                            trigger_time = GETDATE(),
                            last_updated = GETDATE()
                        WHERE id = ? AND is_active = 1");
                    $stmt->execute([$image_path, $cms_id]);
                    $success_message = "✓ CMS-{$cms_id} 圖片訊息已發布";
                } else {
                    $error_message = "請選擇或上傳圖片";
                }
            }
        } elseif ($action == 'reset_to_normal') {
            // 回歸預設模式
            $cms_id = intval($_POST['cms_id']);
            $image_path = $_POST['normal_image'] ?? 'cms-images/normal.bmp';

            $stmt = $pdo->prepare("UPDATE cms_status 
                SET current_mode = 'normal',
                    display_type = 'image',
                    text_content = NULL,
                    image_path = ?,
                    trigger_source = 'normal',
                    trigger_value = NULL,
                    trigger_time = NULL,
                    last_updated = GETDATE()
                WHERE id = ? AND is_active = 1");
            $stmt->execute([$image_path, $cms_id]);
            $success_message = "✓ CMS-{$cms_id} 已回歸預設模式";
        }
    } catch (Exception $e) {
        error_log("Publish message error: " . $e->getMessage());
        $error_message = '發布失敗: ' . $e->getMessage();
    }
}

// 獲取所有CMS牌面狀態
$cms_devices = [];

try {
    $pdo = getDbConnection();

    // 查詢新的 cms_status 表
    $stmt = $pdo->prepare("SELECT id, name, ip_address, current_mode, display_type,
                                  text_content, text_color, text_size, image_path,
                                  trigger_source, trigger_value, last_updated, 
                                  execution_status, is_active
                           FROM cms_status 
                           WHERE is_active = 1
                           ORDER BY id ASC");
    $stmt->execute();
    $cms_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>即時訊息發布設定 - 雲林縣古坑鄉草嶺地區環境監測</title>
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
                        <path d="M17,12V3A1,1 0 0,0 16,2H3A1,1 0 0,0 2,3V17L6,13H16A1,1 0 0,0 17,12M21,6H19V15H6V17A1,1 0 0,0 7,18H18L22,22V7A1,1 0 0,0 21,6Z" />
                    </svg>
                    即時訊息發布設定
                </h1>
                <p class="page-description">管理6個CMS的即時訊息顯示內容</p>
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
                        <path d="M17,12V3A1,1 0 0,0 16,2H3A1,1 0 0,0 2,3V17L6,13H16A1,1 0 0,0 17,12M21,6H19V15H6V17A1,1 0 0,0 7,18H18L22,22V7A1,1 0 0,0 21,6Z" />
                    </svg>
                    <p>目前沒有CMS設備資料</p>
                </div>
            <?php else: ?>
                <div class="cms-cards-container">
                    <?php foreach ($cms_devices as $device): ?>
                        <?php
                        // 判斷卡片顏色
                        $current_mode = $device['current_mode'];

                        // alert 和 manual = 紅色，schedule 和 normal = 綠色
                        if (in_array($current_mode, ['alert', 'manual'])) {
                            $headerClass = 'alert';  // 紅色
                        } else {
                            $headerClass = 'normal';  // 綠色
                        }
                        ?>
                        <div class="cms-card">
                            <div class="cms-card-header <?= $headerClass ?>">
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
                                    <span class="cms-info-label">顯示類型</span>
                                    <span class="cms-info-value">
                                        <?php if ($device['display_type'] == 'text'): ?>
                                            <span style="color:#2196F3;">📝 文字</span>
                                        <?php else: ?>
                                            <span style="color:#FF9800;">🖼️ 圖片</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="cms-info-row">
                                    <span class="cms-info-label">最後更新</span>
                                    <span class="cms-info-value" style="font-size: 12px;">
                                        <?= $device['last_updated'] ? htmlspecialchars($device['last_updated']) : '無資料' ?>
                                    </span>
                                </div>

                                <?php if ($device['display_type'] == 'image' && $device['image_path']): ?>
                                    <div class="cms-preview-section">
                                        <div class="cms-preview-label">目前顯示內容</div>
                                        <?php if (file_exists($device['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($device['image_path']) ?>?t=<?= time() ?>"
                                                alt="CMS內容預覽"
                                                class="cms-preview-image"
                                                onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <p style="color:#999;font-size:12px;">圖片不存在: <?= htmlspecialchars($device['image_path']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($device['display_type'] == 'text' && $device['text_content']): ?>
                                    <div class="cms-preview-section">
                                        <div class="cms-preview-label">目前顯示文字</div>
                                        <p style="color:<?= $device['text_color'] ?? 'black' ?>;font-size:<?= $device['text_size'] ?? 24 ?>px;font-weight:bold;text-align:center;padding:10px;">
                                            <?= htmlspecialchars($device['text_content']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($device['trigger_source'] && $device['trigger_source'] != 'manual' && $device['trigger_source'] != 'normal'): ?>
                                    <div class="cms-info-row" style="margin-top:10px;padding:8px;background:#fff3cd;border-radius:4px;">
                                        <small style="color:#856404;">
                                            <strong>觸發來源:</strong>
                                            <?php
                                            $source_names = [
                                                'rain_alert' => '🌧️ 雨量警報',
                                                'detection' => '⚠️ 滯留物檢測',
                                                'rockfall' => '🪨 落石檢測',
                                                'schedule' => '📅 排程'
                                            ];
                                            echo $source_names[$device['trigger_source']] ?? $device['trigger_source'];
                                            if ($device['trigger_value']) {
                                                echo '<br>' . htmlspecialchars($device['trigger_value']);
                                            }
                                            ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <div class="cms-card-actions">
                                    <button class="cms-action-btn btn-edit" onclick="editCMS(<?= $device['id'] ?>, '<?= htmlspecialchars($device['name']) ?>')">
                                        <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                                        </svg>
                                        發布訊息
                                    </button>
                                    <?php if (in_array($device['current_mode'], ['alert', 'manual'])): ?>
                                        <button class="cms-action-btn btn-reset" onclick="resetToNormal(<?= $device['id'] ?>, '<?= htmlspecialchars($device['name']) ?>')">
                                            <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12C4,13.85 4.63,15.55 5.68,16.91L16.91,5.68C15.55,4.63 13.85,4 12,4M12,20A8,8 0 0,0 20,12C20,10.15 19.37,8.45 18.32,7.09L7.09,18.32C8.45,19.37 10.15,20 12,20Z" />
                                            </svg>
                                            回歸預設
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/change_password_modal.php'; ?>

    <!-- 發布訊息Modal -->
    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h2 id="modalTitle">發布即時訊息</h2>
                <span class="modal-close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="publishForm">
                    <input type="hidden" name="action" value="publish">
                    <input type="hidden" name="cms_id" id="edit_cms_id">

                    <div class="form-group">
                        <label style="display:block;margin-bottom:10px;font-weight:bold;">顯示類型</label>
                        <label style="display:inline-block;margin-right:20px;">
                            <input type="radio" name="display_type" value="image" checked onchange="toggleDisplayType()">
                            🖼️ 圖片模式
                        </label>
                        <label style="display:inline-block;">
                            <input type="radio" name="display_type" value="text" onchange="toggleDisplayType()">
                            📝 文字模式
                        </label>
                    </div>

                    <!-- 圖片模式 -->
                    <div id="image-mode" style="display:block;">
                        <div class="form-group">
                            <label>上傳圖片 (128x256 BMP)</label>
                            <input type="file" name="image_file" accept=".bmp" class="form-control" onchange="previewImage(this)">
                            <div id="imagePreview" style="margin-top:10px;display:none;">
                                <img id="previewImg" style="max-height:150px;border:2px solid #ddd;padding:5px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>或選擇現有圖片</label>
                            <select name="existing_image" class="form-control">
                                <option value="">-- 選擇圖片 --</option>
                            </select>
                        </div>
                    </div>

                    <!-- 文字模式 -->
                    <div id="text-mode" style="display:none;">
                        <div class="form-group">
                            <label>文字內容</label>
                            <input type="text" name="text_content" class="form-control" placeholder="請輸入要顯示的文字" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label>文字顏色</label>
                            <select name="text_color" class="form-control">
                                <option value="green">綠色（正常）</option>
                                <option value="yellow">黃色（注意）</option>
                                <option value="red">紅色（警告）</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>文字大小</label>
                            <input type="number" name="text_size" class="form-control" value="24" min="16" max="48">
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:20px;text-align:right;">
                        <button type="button" class="btn-secondary" onclick="closeEditModal()">取消</button>
                        <button type="submit" class="btn-primary">✓ 立即發布</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 回歸預設模式 Modal -->
    <div id="resetModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:500px;">
            <div class="modal-header">
                <h2 id="resetModalTitle">回歸預設模式</h2>
                <span class="modal-close" onclick="closeResetModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="resetForm">
                    <input type="hidden" name="action" value="reset_to_normal">
                    <input type="hidden" name="cms_id" id="reset_cms_id">

                    <div class="form-group">
                        <label>選擇預設圖片</label>
                        <select name="normal_image" class="form-control" required>
                            <option value="cms-images/normal.bmp">（預設）</option>
                            <?php
                            foreach ($image_list as $img):
                                if ($img['category'] == 'normal' && $img['file_path'] != 'cms-images/normal.bmp'):
                            ?>
                                    <option value="<?= htmlspecialchars($img['file_path']) ?>">
                                        <?= htmlspecialchars($img['filename']) ?>
                                    </option>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </select>
                        <small style="color:#666;font-size:12px;margin-top:5px;display:block;">
                            💡 將會清除目前的即時/警報狀態，回到正常顯示
                        </small>
                    </div>

                    <div class="form-actions" style="margin-top:20px;text-align:right;">
                        <button type="button" class="btn-secondary" onclick="closeResetModal()">取消</button>
                        <button type="submit" class="btn-primary">✓ 確認回歸</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/sidebar-scripts.js"></script>
    <script src="assets/js/dashboard-scripts.js"></script>
    <script>
        function editCMS(id, name) {
            document.getElementById('edit_cms_id').value = id;
            document.getElementById('modalTitle').textContent = '發布訊息 - ' + name;
            document.getElementById('editModal').style.display = 'block';
        }

        function resetToNormal(id, name) {
            document.getElementById('reset_cms_id').value = id;
            document.getElementById('resetModalTitle').textContent = '回歸預設模式 - ' + name;
            document.getElementById('resetModal').style.display = 'block';
        }

        function closeResetModal() {
            document.getElementById('resetModal').style.display = 'none';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function toggleDisplayType() {
            const isImage = document.querySelector('input[name="display_type"]:checked').value === 'image';
            document.getElementById('image-mode').style.display = isImage ? 'block' : 'none';
            document.getElementById('text-mode').style.display = isImage ? 'none' : 'block';
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 關閉modal（點擊外部）
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const resetModal = document.getElementById('resetModal');
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == resetModal) {
                closeResetModal();
            }
        }

        // 自動刷新：每3分鐘刷新頁面
        setTimeout(function() {
            location.reload();
        }, 180000); // 180000ms = 3分鐘
    </script>
</body>

</html>