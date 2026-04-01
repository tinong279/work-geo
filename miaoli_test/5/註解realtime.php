<?php
// 啟動 PHP Session，用於追蹤使用者的登入狀態
session_start();

// 檢查 Session 中是否存在 'user_id'。如果沒有，代表使用者未登入。
// 未登入則強制重新導向 (redirect) 到 login.php 頁面，並終止此程式執行。
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 引入資料庫連線設定檔。這個檔案通常包含 getDbConnection() 函數，用於建立 PDO 物件。
require_once 'includes/db_connection.php';

// ==========================================
// 區塊 1：取得系統中可用的預設圖片清單
// ==========================================
$image_list = [];
try {
    // 建立資料庫連線
    $pdo_img = getDbConnection();
    // 查詢 cms_images 表格中所有圖片的檔名、路徑和分類，並依分類和檔名排序
    $stmt = $pdo_img->query("SELECT filename, file_path, category FROM cms_images ORDER BY category, filename");
    // 將查詢結果存入 $image_list 陣列中
    $image_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 如果發生資料庫錯誤（例如 cms_images 表格尚未建立），則提供一組預設的靜態圖片路徑
    // 這是為了防止系統在資料庫尚未完全建置時崩潰
    $image_list = [
        ['filename' => '正常通行', 'file_path' => 'cms-images/normal.bmp', 'category' => 'normal'],
        ['filename' => '雨量警戒', 'file_path' => 'cms-images/alert/rain_level2.bmp', 'category' => 'alert'],
        ['filename' => '雨量行動值', 'file_path' => 'cms-images/alert/rain_level3.bmp', 'category' => 'alert']
    ];
}

// 初始化成功與錯誤訊息的變數，用於稍後在畫面上顯示提示給使用者
$success_message = '';
$error_message = '';

// ==========================================
// 區塊 2：處理來自前端表單的 POST 請求 (即發布訊息或回歸預設)
// ==========================================
// 檢查是否為 POST 請求，且表單中有帶入 'action' 參數
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        $pdo = getDbConnection();
        $action = $_POST['action'];

        // --- 動作 A：手動發布即時訊息 ---
        if ($action == 'publish') {
            // 取得要更新的 CMS 設備 ID
            $cms_id = intval($_POST['cms_id']);
            // 取得使用者選擇的顯示類型（text: 文字, image: 圖片）
            $display_type = $_POST['display_type'];

            if ($display_type == 'text') {
                // [文字模式] 處理邏輯
                $text_content = $_POST['text_content']; // 文字內容
                $text_color = $_POST['text_color'];   // 文字顏色
                $text_size = intval($_POST['text_size']); // 文字大小

                // 準備 SQL 語句：更新指定 CMS (id) 的狀態
                // 重要：將 current_mode 設為 'manual' (手動模式)，這會鎖定狀態，防止被自動路況 API 覆蓋
                // 同時清空 image_path (設為 NULL)，並記錄觸發來源為 'manual'
                $stmt = $pdo->prepare("UPDATE cms_status 
                    SET current_mode = 'manual',
                        display_type = 'text',
                        text_content = ?,
                        text_color = ?,
                        text_size = ?,
                        image_path = NULL,
                        trigger_source = 'manual',
                        trigger_value = NULL,
                        trigger_time = GETDATE(), -- 紀錄觸發時間
                        last_updated = GETDATE()  -- 紀錄最後更新時間
                    WHERE id = ? AND is_active = 1");
                // 執行 SQL 更新
                $stmt->execute([$text_content, $text_color, $text_size, $cms_id]);
                $success_message = "✓ CMS-{$cms_id} 文字訊息已發布";
            } else {
                // [圖片模式] 處理邏輯
                $image_path = '';

                // 檢查使用者是否透過檔案上傳欄位上傳了新圖片，且沒有發生錯誤 (error == 0)
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
                    $upload_dir = 'cms-images/manual/';
                    // 如果上傳目錄不存在，則建立目錄 (權限 0755)
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // 產生新的檔名：當前時間戳記 + 原始檔名 (避免檔名重複覆蓋)
                    $filename = time() . '_' . basename($_FILES['image_file']['name']);
                    $target_path = $upload_dir . $filename;

                    // 將暫存檔移動到目標目錄
                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_path)) {
                        $image_path = $target_path;

                        // 上傳成功後，將新圖片的資訊記錄到資料庫的圖庫表 (cms_images) 中
                        $stmt = $pdo->prepare("INSERT INTO cms_images (filename, file_path, category, uploaded_by) VALUES (?, ?, 'manual', ?)");
                        $stmt->execute([$filename, $image_path, $_SESSION['username'] ?? 'admin']);
                    }
                } else {
                    // 如果沒有上傳新圖片，則嘗試抓取使用者從下拉選單選擇的現有圖片路徑
                    $image_path = $_POST['existing_image'] ?? '';
                }

                // 如果成功取得圖片路徑 (不管是新上傳還是選現有)，則更新資料庫
                if ($image_path) {
                    // 重要：同樣將 current_mode 設為 'manual' 以鎖定狀態
                    // 清空 text_content，並寫入 image_path
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

            // --- 動作 B：解除手動狀態，回歸預設 ---
        } elseif ($action == 'reset_to_normal') {
            $cms_id = intval($_POST['cms_id']);
            // 取得使用者選擇的預設圖片，若未選擇則給定預設值
            $image_path = $_POST['normal_image'] ?? 'cms-images/normal.bmp';

            // 準備 SQL 語句：將 CMS 狀態重置
            // 重要：將 current_mode 改回 'normal'。這代表解除手動鎖定，允許外部系統 (如自動路況偵測) 再次接管
            $stmt = $pdo->prepare("UPDATE cms_status 
                SET current_mode = 'normal',
                    display_type = 'image',
                    text_content = NULL,
                    image_path = ?,
                    trigger_source = 'normal',
                    trigger_value = NULL,
                    trigger_time = NULL,  -- 清空觸發時間
                    last_updated = GETDATE()
                WHERE id = ? AND is_active = 1");
            $stmt->execute([$image_path, $cms_id]);
            $success_message = "✓ CMS-{$cms_id} 已回歸預設模式";
        }
    } catch (Exception $e) {
        // 如果執行過程發生任何錯誤，記錄到伺服器 log 並顯示給使用者
        error_log("Publish message error: " . $e->getMessage());
        $error_message = '發布失敗: ' . $e->getMessage();
    }
}

// ==========================================
// 區塊 3：查詢所有 CMS 設備的當前狀態 (用於畫面渲染)
// ==========================================
$cms_devices = [];

try {
    $pdo = getDbConnection();

    // 查詢 is_active = 1 (啟用中) 的所有 CMS 設備詳細狀態
    $stmt = $pdo->prepare("SELECT id, name, ip_address, current_mode, display_type,
                                  text_content, text_color, text_size, image_path,
                                  trigger_source, trigger_value, last_updated, 
                                  execution_status, is_active
                           FROM cms_status 
                           WHERE is_active = 1
                           ORDER BY id ASC");
    $stmt->execute();
    $cms_devices = $stmt->fetchAll(PDO::FETCH_ASSOC); // 取得所有結果為關聯陣列
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

    <link rel="stylesheet" href="assets/css/core/variables.css">
    <link rel="stylesheet" href="assets/css/core/reset.css">
    <link rel="stylesheet" href="assets/css/core/layout.css">
    <link rel="stylesheet" href="assets/css/components/buttons.css">
    <link rel="stylesheet" href="assets/css/components/forms.css">
    <link rel="stylesheet" href="assets/css/components/modals.css">
    <link rel="stylesheet" href="assets/css/components/sidebar.css">
    <link rel="stylesheet" href="assets/css/utils/animations.css">
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
                        // 判斷卡片標題的顏色：若是警報或手動模式，標題列顯示紅色，否則顯示綠色
                        $current_mode = $device['current_mode'];
                        if (in_array($current_mode, ['alert', 'manual'])) {
                            $headerClass = 'alert';  // 對應 CSS，紅色背景
                        } else {
                            $headerClass = 'normal'; // 對應 CSS，綠色背景
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
                                        // 依據模式顯示不同顏色的 Badge (標籤)
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
                            // 印出分類為 'normal' 的可用圖片
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
        // 開啟「發布訊息」視窗，並將 CMS 的 ID 與名稱動態帶入表單中
        function editCMS(id, name) {
            document.getElementById('edit_cms_id').value = id;
            document.getElementById('modalTitle').textContent = '發布訊息 - ' + name;
            document.getElementById('editModal').style.display = 'block';
        }

        // 開啟「回歸預設」視窗
        function resetToNormal(id, name) {
            document.getElementById('reset_cms_id').value = id;
            document.getElementById('resetModalTitle').textContent = '回歸預設模式 - ' + name;
            document.getElementById('resetModal').style.display = 'block';
        }

        // 關閉「回歸預設」視窗
        function closeResetModal() {
            document.getElementById('resetModal').style.display = 'none';
        }

        // 關閉「發布訊息」視窗
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // 切換發布訊息表單中「圖片模式」與「文字模式」的顯示/隱藏
        function toggleDisplayType() {
            const isImage = document.querySelector('input[name="display_type"]:checked').value === 'image';
            document.getElementById('image-mode').style.display = isImage ? 'block' : 'none';
            document.getElementById('text-mode').style.display = isImage ? 'none' : 'block';
        }

        // 處理圖片上傳的預覽功能：當使用者選擇檔案後，透過 FileReader 讀取並顯示在畫面上
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

        // 監聽點擊事件：如果使用者點擊了 Modal 外的半透明黑色區域，自動關閉 Modal
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

        // 自動刷新機制：每 3 分鐘 (180,000 毫秒) 重新載入頁面，以取得最新 CMS 狀態
        setTimeout(function() {
            location.reload();
        }, 180000);
    </script>
</body>

</html>