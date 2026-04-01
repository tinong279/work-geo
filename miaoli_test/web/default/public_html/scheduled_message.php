<?php
session_start();

// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }

require_once __DIR__ . '/includes/db_connection.php';

// ============================
// 1. 查詢可用圖片
// ============================
$image_list = [];
try {
    $pdo_img = getDbConnection();
    $stmt = $pdo_img->query("
        SELECT filename, file_path, category
        FROM cms_images
        ORDER BY category, filename
    ");
    $image_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $image_list = [
        ['filename' => '正常通行', 'file_path' => 'cms-images/normal.bmp', 'category' => 'normal'],
        ['filename' => '雨量警戒', 'file_path' => 'cms-images/alert/rain_level2.bmp', 'category' => 'alert'],
        ['filename' => '雨量行動值', 'file_path' => 'cms-images/alert/rain_level3.bmp', 'category' => 'alert']
    ];
}

// ============================
// 2. 處理即時訊息發布
// ============================
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = getDbConnection();
        $action = $_POST['action'];

        // A. 發布訊息
        if ($action === 'publish') {
            $cms_id = intval($_POST['cms_id']);
            $display_type = $_POST['display_type'] ?? 'image';

            if ($display_type === 'text') {
                $text_content = trim($_POST['text_content'] ?? '');
                $text_color   = $_POST['text_color'] ?? 'green';
                $text_size    = intval($_POST['text_size'] ?? 24);

                if ($text_content === '') throw new Exception('請輸入文字內容');

                $stmt = $pdo->prepare("
                    UPDATE cms_status
                    SET current_mode = 'manual', display_type = 'text', text_content = ?, text_color = ?, text_size = ?, image_path = NULL, trigger_source = 'manual', trigger_value = NULL, trigger_time = GETDATE(), last_updated = GETDATE()
                    WHERE id = ? AND is_active = 1
                ");
                $stmt->execute([$text_content, $text_color, $text_size, $cms_id]);
                $success_message = "✓ CMS-{$cms_id} 文字訊息已發布";
            } else {
                $image_path = '';

                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
                    $upload_dir = __DIR__ . '/cms-images/manual/';
                    $upload_url = 'cms-images/manual/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                    $originalName = basename($_FILES['image_file']['name']);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $allowed = ['bmp', 'png', 'jpg', 'jpeg', 'webp'];
                    if (!in_array($ext, $allowed, true)) throw new Exception('圖片格式不支援');

                    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
                    $target_physical = $upload_dir . $filename;
                    $target_relative = $upload_url . $filename;

                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_physical)) {
                        $image_path = $target_relative;
                        $stmt = $pdo->prepare("INSERT INTO cms_images (filename, file_path, category, uploaded_by) VALUES (?, ?, 'manual', ?)");
                        $stmt->execute([$filename, $image_path, $_SESSION['username'] ?? 'admin']);
                    } else {
                        throw new Exception('圖片上傳失敗');
                    }
                } else {
                    $image_path = trim($_POST['existing_image'] ?? '');
                }

                if ($image_path === '') throw new Exception('請選擇或上傳圖片');

                $stmt = $pdo->prepare("
                    UPDATE cms_status
                    SET current_mode = 'manual', display_type = 'image', text_content = NULL, image_path = ?, trigger_source = 'manual', trigger_value = NULL, trigger_time = GETDATE(), last_updated = GETDATE()
                    WHERE id = ? AND is_active = 1
                ");
                $stmt->execute([$image_path, $cms_id]);
                $success_message = "✓ CMS-{$cms_id} 圖片訊息已發布";
            }
        }
        // B. 回歸預設模式
        elseif ($action === 'reset_to_normal') {
            $cms_id = intval($_POST['cms_id']);
            $image_path = $_POST['normal_image'] ?? 'cms-images/normal.bmp';

            $stmt = $pdo->prepare("
                UPDATE cms_status
                SET current_mode = 'normal', display_type = 'image', text_content = NULL, image_path = ?, trigger_source = 'normal', trigger_value = NULL, trigger_time = NULL, last_updated = GETDATE()
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$image_path, $cms_id]);
            $success_message = "✓ CMS-{$cms_id} 已回歸預設模式";
        }
    } catch (Exception $e) {
        $error_message = '發布失敗: ' . $e->getMessage();
    }
}

// ============================
// 3. 獲取所有 CMS 狀態
// ============================
$cms_devices = [];
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT id, name, ip_address, current_mode, display_type,
               text_content, text_color, text_size, image_path,
               trigger_source, trigger_value, last_updated,
               execution_status, is_active
        FROM cms_status
        WHERE is_active = 1
        ORDER BY id ASC
    ");
    $stmt->execute();
    $cms_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = '資料庫查詢錯誤: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>即時訊息發布設定 - 雲林縣古坑鄉草嶺地區環境監測</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #ffffff;
            --panel-soft: #f8fafc;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
            --danger-soft: #fee2e2;
            --success-soft: #dcfce7;
            --warning-soft: #fef3c7;
            --shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            --radius: 18px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Microsoft JhengHei", "PingFang TC", "Noto Sans TC", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        img,
        svg {
            max-width: 100%;
        }

        button,
        input,
        select {
            font: inherit;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 24px;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 8px;
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
        }

        .page-description {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
        }

        .cms-alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid transparent;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .cms-alert-success {
            color: #166534;
            background: var(--success-soft);
            border-color: #86efac;
        }

        .cms-alert-warning {
            color: #92400e;
            background: var(--warning-soft);
            border-color: #fcd34d;
        }

        .empty-state {
            display: grid;
            place-items: center;
            gap: 12px;
            min-height: 260px;
            padding: 24px;
            text-align: center;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            color: #94a3b8;
        }

        .empty-state p {
            margin: 0;
            font-size: 16px;
            color: var(--muted);
        }

        .cms-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .cms-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .cms-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
            color: #fff;
        }

        .cms-card-header.normal {
            background: linear-gradient(135deg, #16a34a, #15803d);
        }

        .cms-card-header.alert {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .cms-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
        }

        .cms-card-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .cms-card-body {
            padding: 20px;
        }

        .cms-info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .cms-info-label {
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .cms-info-value {
            color: #0f172a;
            text-align: right;
            font-weight: 600;
            word-break: break-word;
        }

        .cms-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
        }

        .status-manual {
            background: #2563eb;
            color: #fff;
        }

        .status-auto {
            background: #7c3aed;
            color: #fff;
        }

        .cms-preview-section {
            margin-top: 16px;
            padding: 16px;
            background: var(--panel-soft);
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
        }

        .cms-preview-label {
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
        }

        .cms-preview-image {
            display: block;
            width: 100%;
            max-height: 180px;
            object-fit: contain;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px;
        }

        .cms-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .cms-action-btn,
        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-height: 42px;
            padding: 10px 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: transform 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        }

        .cms-action-btn:hover,
        .btn-primary:hover,
        .btn-secondary:hover {
            transform: translateY(-1px);
        }

        .btn-edit,
        .btn-primary {
            color: #fff;
            background: var(--primary);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.16);
        }

        .btn-edit:hover,
        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-reset {
            color: #fff;
            background: var(--danger);
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.14);
        }

        .btn-reset:hover {
            background: #b91c1c;
        }

        .btn-secondary {
            color: #1f2937;
            background: #e5e7eb;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            overflow-y: auto;
            padding: 24px;
            background: rgba(15, 23, 42, 0.56);
        }

        .modal-content {
            width: 100%;
            margin: 40px auto;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.2);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
            background: var(--panel-soft);
            border-bottom: 1px solid var(--border);
        }

        .modal-header h2 {
            margin: 0;
            font-size: 22px;
            color: #0f172a;
        }

        .modal-close {
            cursor: pointer;
            color: var(--muted);
            font-size: 30px;
            line-height: 1;
            transition: color 0.15s ease;
        }

        .modal-close:hover {
            color: #111827;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            color: #334155;
            font-weight: 700;
        }

        .form-control,
        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
            color: #0f172a;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus,
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="file"]:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        input[type="radio"] {
            margin-right: 6px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        #imagePreview {
            margin-top: 10px;
        }

        #previewImg {
            display: block;
            max-width: 100%;
            max-height: 150px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            padding: 8px;
        }

        small {
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 16px;
            }

            .page-title {
                font-size: 26px;
            }

            .cms-cards-container {
                grid-template-columns: 1fr;
            }

            .cms-info-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .cms-info-value {
                text-align: left;
            }

            .cms-card-actions,
            .form-actions {
                flex-direction: column;
            }

            .cms-action-btn,
            .btn-primary,
            .btn-secondary {
                width: 100%;
            }

            .modal {
                padding: 16px;
            }

            .modal-content {
                margin: 20px auto;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <svg style="width: 28px; height: 28px; vertical-align: middle; margin-right: 8px;" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17,12V3A1,1 0 0,0 16,2H3A1,1 0 0,0 2,3V17L6,13H16A1,1 0 0,0 17,12M21,6H19V15H6V17A1,1 0 0,0 7,18H18L22,22V7A1,1 0 0,0 21,6Z" />
                    </svg>
                    即時訊息發布設定
                </h1>
                <p class="page-description">管理 CMS 的即時訊息顯示內容</p>
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
                    <p>目前沒有 CMS 設備資料</p>
                </div>
            <?php else: ?>
                <div class="cms-cards-container">
                    <?php foreach ($cms_devices as $device): ?>
                        <?php
                        $current_mode = $device['current_mode'];
                        $headerClass = in_array($current_mode, ['alert', 'manual'], true) ? 'alert' : 'normal';
                        $deviceNameJs = htmlspecialchars(
                            json_encode($device['name'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        $physicalImagePath = '';
                        if (!empty($device['image_path'])) {
                            $physicalImagePath = __DIR__ . '/' . ltrim($device['image_path'], '/\\');
                        }

                        // 【重點修改 1】打包 JSON 資料給前端回填
                        $deviceNameSafe = htmlspecialchars(json_encode($device['name'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        $cmsCurrentData = htmlspecialchars(json_encode([
                            'mode'  => $device['current_mode'],
                            'type'  => $device['display_type'],
                            'image' => $device['image_path'] ?? '',
                            'text'  => $device['text_content'] ?? '',
                            'color' => $device['text_color'] ?? 'green',
                            'size'  => $device['text_size'] ?? 24
                        ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>
                        <div class="cms-card">
                            <div class="cms-card-header <?= $headerClass ?>">
                                <div class="cms-card-title"><?= htmlspecialchars($device['name']) ?></div>
                                <span class="cms-card-id">#<?= str_pad((string)$device['id'], 2, '0', STR_PAD_LEFT) ?></span>
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
                                            'normal'   => '<span class="cms-status-badge" style="background:#4CAF50;color:white;">預設</span>',
                                            'manual'   => '<span class="cms-status-badge status-manual">即時</span>',
                                            'schedule' => '<span class="cms-status-badge status-auto">排程</span>',
                                            'alert'    => '<span class="cms-status-badge" style="background:#f44336;color:white;">警報</span>'
                                        ];
                                        echo $mode_badges[$device['current_mode']] ?? htmlspecialchars($device['current_mode']);
                                        ?>
                                    </span>
                                </div>
                                <div class="cms-info-row">
                                    <span class="cms-info-label">顯示類型</span>
                                    <span class="cms-info-value">
                                        <?php if ($device['display_type'] === 'text'): ?>
                                            <span style="color:#2196F3;">📝 文字</span>
                                        <?php else: ?>
                                            <span style="color:#FF9800;">🖼️ 圖片</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="cms-info-row">
                                    <span class="cms-info-label">最後更新</span>
                                    <span class="cms-info-value" style="font-size: 12px;">
                                        <?= $device['last_updated'] ? htmlspecialchars((string)$device['last_updated']) : '無資料' ?>
                                    </span>
                                </div>

                                <?php if ($device['display_type'] === 'image' && !empty($device['image_path'])): ?>
                                    <div class="cms-preview-section">
                                        <div class="cms-preview-label">目前顯示內容</div>
                                        <?php if ($physicalImagePath && file_exists($physicalImagePath)): ?>
                                            <img src="<?= htmlspecialchars($device['image_path']) ?>?t=<?= time() ?>" alt="預覽" class="cms-preview-image">
                                        <?php else: ?>
                                            <p style="color:#999;font-size:12px;">圖片不存在</p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($device['display_type'] === 'text' && !empty($device['text_content'])): ?>
                                    <div class="cms-preview-section">
                                        <div class="cms-preview-label">目前顯示文字</div>
                                        <p style="color:<?= htmlspecialchars($device['text_color'] ?? 'black') ?>;font-size:<?= (int)($device['text_size'] ?? 24) ?>px;font-weight:bold;text-align:center;padding:10px;">
                                            <?= htmlspecialchars($device['text_content']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="cms-card-actions">
                                    <button class="cms-action-btn btn-edit" onclick="editCMS(<?= (int)$device['id'] ?>, <?= $deviceNameJs ?>, <?= $cmsCurrentData ?>)">
                                        <svg style="width: 16px; height: 16px; margin-right: 4px;" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                                        </svg>
                                        發布訊息
                                    </button>

                                    <?php if (in_array($device['current_mode'], ['alert', 'manual'], true)): ?>
                                        <button class="cms-action-btn btn-reset" onclick="resetToNormal(<?= (int)$device['id'] ?>, <?= $deviceNameJs ?>)">回歸預設</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="editModal" class="modal">
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
                            <input type="radio" name="display_type" value="image" checked onchange="toggleDisplayType()"> 🖼️ 圖片模式
                        </label>
                        <label style="display:inline-block;">
                            <input type="radio" name="display_type" value="text" onchange="toggleDisplayType()"> 📝 文字模式
                        </label>
                    </div>

                    <div id="image-mode" style="display:block;">
                        <div class="form-group">
                            <label>上傳圖片 (BMP / PNG / JPG / WEBP)</label>
                            <input type="file" name="image_file" accept=".bmp,.png,.jpg,.jpeg,.webp" class="form-control" onchange="previewImage(this)">
                            <div id="imagePreview" style="margin-top:10px;display:none;">
                                <img id="previewImg" style="max-height:150px;border:2px solid #ddd;padding:5px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>或選擇現有圖片</label>
                            <select name="existing_image" class="form-control" onchange="previewExistingImage(this)">
                                <option value="">-- 選擇圖片 --</option>
                                <?php foreach ($image_list as $img): ?>
                                    <option value="<?= htmlspecialchars($img['file_path']) ?>">
                                        [<?= htmlspecialchars($img['category']) ?>] <?= htmlspecialchars($img['filename']) ?>
                                    </option>
                                <?php endforeach; ?>
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

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeEditModal()">取消</button>
                        <button type="submit" class="btn-primary">✓ 立即發布</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="resetModal" class="modal">
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
                            <?php foreach ($image_list as $img): ?>
                                <?php if ($img['category'] === 'normal' && $img['file_path'] !== 'cms-images/normal.bmp'): ?>
                                    <option value="<?= htmlspecialchars($img['file_path']) ?>"><?= htmlspecialchars($img['filename']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeResetModal()">取消</button>
                        <button type="submit" class="btn-primary">✓ 確認回歸</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 【重點修改 4】完整 JavaScript 邏輯，包含防呆與回填
        function editCMS(id, name, currentData) {
            document.getElementById('edit_cms_id').value = id;
            document.getElementById('modalTitle').textContent = '發布訊息 - ' + name;

            // 開啟前先重置所有表單狀態與預覽區塊，避免殘留上一次的操作
            document.getElementById('publishForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('previewImg').src = '';
            document.querySelector('input[name="image_file"]').value = '';

            // 如果這塊牌子目前正在發布「即時訊息(manual)」，就把資料自動帶入
            if (currentData && currentData.mode === 'manual') {
                if (currentData.type === 'text') {
                    // 切換到文字模式並填入內容
                    document.querySelector('input[name="display_type"][value="text"]').checked = true;
                    document.querySelector('input[name="text_content"]').value = currentData.text;
                    document.querySelector('select[name="text_color"]').value = currentData.color;
                    document.querySelector('input[name="text_size"]').value = currentData.size;
                } else if (currentData.type === 'image') {
                    // 切換到圖片模式
                    document.querySelector('input[name="display_type"][value="image"]').checked = true;
                    if (currentData.image) {
                        // 嘗試在下拉選單中尋找這張圖片並自動選取
                        const selectEl = document.querySelector('select[name="existing_image"]');
                        const optionExists = Array.from(selectEl.options).some(opt => opt.value === currentData.image);
                        if (optionExists) {
                            selectEl.value = currentData.image;
                        }
                        // 顯示目前的圖片預覽
                        document.getElementById('previewImg').src = currentData.image + '?t=' + new Date().getTime();
                        document.getElementById('imagePreview').style.display = 'block';
                    }
                }
            } else {
                // 如果目前是預設模式，維持空白圖片表單
                document.querySelector('input[name="display_type"][value="image"]').checked = true;
            }

            toggleDisplayType();
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

        // 處理「上傳新圖片」的預覽
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.querySelector('select[name="existing_image"]').value = '';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                document.getElementById('imagePreview').style.display = 'none';
            }
        }

        // 處理「選擇現有圖片」的預覽
        function previewExistingImage(selectElement) {
            const previewDiv = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            if (selectElement.value !== "") {
                previewImg.src = selectElement.value + '?t=' + new Date().getTime();
                previewDiv.style.display = 'block';
                document.querySelector('input[name="image_file"]').value = '';
            } else {
                previewDiv.style.display = 'none';
                previewImg.src = '';
            }
        }

        window.onclick = function(event) {
            if (event.target === document.getElementById('editModal')) closeEditModal();
            if (event.target === document.getElementById('resetModal')) closeResetModal();
        };

        setTimeout(function() {
            location.reload();
        }, 180000);
    </script>
</body>

</html>