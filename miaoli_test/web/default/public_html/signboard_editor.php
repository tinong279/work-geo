<?php
// ==========================================
// signboard_editor.php
// 功能：
// 1. 顯示所有告示牌卡片
// 2. 可切換文字版 / 圖片版
// 3. 將設定存到 JSON
// ==========================================

session_start();

// ------------------------------
// 1. 告示牌基本資料
// 之後你可以改成從資料庫撈
// ------------------------------
$boards = [
    ['id' => 1, 'name' => '草嶺一號-門隧道-CMS', 'ip' => '221.120.56.129'],
    ['id' => 2, 'name' => '草嶺一號橋(橋頭)-CMS', 'ip' => '221.120.7.221'],
];

// ------------------------------
// 2. 狀態檔與上傳資料夾
// ------------------------------
$stateFile = __DIR__ . '/data/signboard_state.json';
$uploadDir = __DIR__ . '/uploads/signboards';
$uploadUrlBase = 'uploads/signboards';

if (!is_dir(dirname($stateFile))) {
    mkdir(dirname($stateFile), 0755, true);
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ------------------------------
// 3. 預設狀態
// ------------------------------
function getDefaultState(array $board): array
{
    return [
        'id' => $board['id'],
        'name' => $board['name'],
        'ip' => $board['ip'],
        'display_type' => 'text',      // text / image
        'text_content' => '路況順暢',
        'text_color' => '#c6ff00',
        'text_size' => 54,
        'image_path' => '',
        'last_updated' => '',
    ];
}

// ------------------------------
// 4. 載入既有狀態
// ------------------------------
$boardStates = [];

foreach ($boards as $board) {
    $boardStates[$board['id']] = getDefaultState($board);
}

if (file_exists($stateFile)) {
    $saved = json_decode(file_get_contents($stateFile), true);
    if (is_array($saved)) {
        foreach ($saved as $id => $state) {
            if (isset($boardStates[$id]) && is_array($state)) {
                $boardStates[$id] = array_merge($boardStates[$id], $state);
            }
        }
    }
}

$successMessage = '';
$errorMessage = '';

// ------------------------------
// 5. 處理表單送出
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $boardId = isset($_POST['board_id']) ? (int)$_POST['board_id'] : 0;
    $displayType = $_POST['display_type'] ?? 'text';

    if (!isset($boardStates[$boardId])) {
        $errorMessage = '找不到對應的告示牌';
    } else {
        try {
            $state = $boardStates[$boardId];
            $state['display_type'] = $displayType;
            $state['last_updated'] = date('Y-m-d H:i:s');

            if ($displayType === 'text') {
                $textContent = trim($_POST['text_content'] ?? '');
                $textColor = $_POST['text_color'] ?? '#c6ff00';
                $textSize = (int)($_POST['text_size'] ?? 54);

                if ($textContent === '') {
                    throw new Exception('文字內容不能為空');
                }

                $state['text_content'] = mb_substr($textContent, 0, 20, 'UTF-8');
                $state['text_color'] = $textColor;
                $state['text_size'] = max(20, min(80, $textSize));
                $state['image_path'] = '';
            } else {
                $imagePath = $state['image_path'];

                // 5-1. 上傳新圖片
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['image_file']['name'];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    $allowedExt = ['png', 'jpg', 'jpeg', 'bmp', 'webp'];
                    if (!in_array($ext, $allowedExt, true)) {
                        throw new Exception('圖片格式不支援，只能上傳 png / jpg / jpeg / bmp / webp');
                    }

                    $newFileName = 'board_' . $boardId . '_' . time() . '.' . $ext;
                    $targetFile = $uploadDir . '/' . $newFileName;

                    if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                        throw new Exception('圖片上傳失敗');
                    }

                    $imagePath = $uploadUrlBase . '/' . $newFileName;
                } else {
                    // 5-2. 使用既有圖片
                    $imagePath = trim($_POST['existing_image'] ?? $imagePath);
                }

                if ($imagePath === '') {
                    throw new Exception('請選擇或上傳圖片');
                }

                $state['image_path'] = $imagePath;
            }

            $boardStates[$boardId] = $state;

            file_put_contents(
                $stateFile,
                json_encode($boardStates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            );

            $successMessage = '告示牌內容已更新';
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    }
}

// ------------------------------
// 6. 掃描已上傳圖片，給下拉選單用
// ------------------------------
$existingImages = [];
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $existingImages[] = $uploadUrlBase . '/' . $file;
    }
}
rsort($existingImages);
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>即時訊息發布設定</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, "Microsoft JhengHei", sans-serif;
            background: #1f1f1f;
            color: #222;
        }

        .page-wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: #f3f3f3;
            border-radius: 14px;
            padding: 26px 28px;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 34px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .page-desc {
            color: #666;
            font-size: 15px;
        }

        .alert {
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .alert-success {
            background: #e8f7ee;
            color: #137333;
        }

        .alert-error {
            background: #fdecec;
            color: #b42318;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 22px;
        }

        .board-card {
            background: #f7f7f7;
            border-radius: 14px;
            overflow: hidden;
        }

        .board-header {
            background: #15b27f;
            color: #fff;
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .board-name {
            font-size: 22px;
            font-weight: 700;
        }

        .board-id {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
        }

        .board-body {
            padding: 18px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #e2e2e2;
            font-size: 15px;
        }

        .info-label {
            color: #666;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .badge-green {
            background: #52b561;
        }

        .badge-blue {
            background: #2563eb;
        }

        .preview-wrap {
            padding: 22px 0 12px;
            text-align: center;
        }

        .preview-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .sign-preview {
            width: 120px;
            height: 220px;
            margin: 0 auto;
            background: #000;
            border: 4px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            writing-mode: vertical-rl;
            text-orientation: upright;
            letter-spacing: 3px;
            font-weight: 700;
            line-height: 1.1;
        }

        .sign-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }

        .card-actions {
            margin-top: 20px;
        }

        .btn {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 14px 16px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
        }

        .btn-primary {
            background: #6f7ee8;
            color: #fff;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            padding: 20px;
        }

        .modal-content {
            max-width: 720px;
            margin: 40px auto;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
        }

        .modal-header {
            padding: 18px 22px;
            background: #f5f5f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
        }

        .modal-close {
            font-size: 28px;
            cursor: pointer;
        }

        .modal-body {
            padding: 22px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select,
        .form-file {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 15px;
        }

        .radio-group {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .color-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .color-box {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            border: 2px solid #ddd;
            cursor: pointer;
        }

        .image-preview-box {
            margin-top: 10px;
            text-align: center;
            display: none;
        }

        .image-preview-box img {
            max-width: 220px;
            max-height: 220px;
            border: 1px solid #ddd;
            padding: 6px;
            border-radius: 8px;
        }

        .form-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="page-wrap">
        <div class="page-header">
            <h1 class="page-title">即時訊息發布設定</h1>
            <div class="page-desc">管理每面告示牌的顯示內容，可切換文字版或圖片版</div>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="card-grid">
            <?php foreach ($boardStates as $state): ?>
                <div class="board-card">
                    <div class="board-header">
                        <div class="board-name"><?= htmlspecialchars($state['name']) ?></div>
                        <div class="board-id">#<?= str_pad((string)$state['id'], 2, '0', STR_PAD_LEFT) ?></div>
                    </div>

                    <div class="board-body">
                        <div class="info-row">
                            <div class="info-label">IP位址</div>
                            <div><?= htmlspecialchars($state['ip']) ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">顯示類型</div>
                            <div>
                                <?php if ($state['display_type'] === 'text'): ?>
                                    <span class="badge badge-blue">文字版</span>
                                <?php else: ?>
                                    <span class="badge badge-green">圖片版</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">最後更新</div>
                            <div><?= $state['last_updated'] ? htmlspecialchars($state['last_updated']) : '尚未更新' ?></div>
                        </div>

                        <div class="preview-wrap">
                            <div class="preview-label">目前顯示內容</div>
                            <div class="sign-preview"
                                style="color: <?= htmlspecialchars($state['text_color']) ?>; font-size: <?= (int)$state['text_size'] ?>px;">
                                <?php if ($state['display_type'] === 'image' && $state['image_path']): ?>
                                    <img src="<?= htmlspecialchars($state['image_path']) ?>?t=<?= time() ?>" alt="告示牌圖片">
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars($state['text_content'])) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button class="btn btn-primary" onclick="openEditor(<?= (int)$state['id'] ?>)">發布訊息</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 編輯 Modal -->
    <div class="modal" id="editorModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">發布訊息</div>
                <div class="modal-close" onclick="closeEditor()">&times;</div>
            </div>

            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="board_id" id="board_id">

                    <div class="form-group">
                        <label class="form-label">顯示類型</label>
                        <div class="radio-group">
                            <label><input type="radio" name="display_type" value="text" checked onchange="toggleDisplayType()"> 文字版</label>
                            <label><input type="radio" name="display_type" value="image" onchange="toggleDisplayType()"> 圖片版</label>
                        </div>
                    </div>

                    <!-- 文字版 -->
                    <div id="textFields">
                        <div class="form-group">
                            <label class="form-label">文字內容</label>
                            <input type="text" class="form-control" name="text_content" id="text_content" maxlength="20" placeholder="例如：前方道路壅塞">
                        </div>

                        <div class="form-group">
                            <label class="form-label">文字顏色</label>
                            <input type="color" class="form-control" name="text_color" id="text_color" value="#c6ff00">
                        </div>

                        <div class="form-group">
                            <label class="form-label">文字大小</label>
                            <input type="number" class="form-control" name="text_size" id="text_size" min="20" max="80" value="54">
                        </div>
                    </div>

                    <!-- 圖片版 -->
                    <div id="imageFields" class="hidden">
                        <div class="form-group">
                            <label class="form-label">上傳新圖片</label>
                            <input type="file" class="form-file" name="image_file" accept=".png,.jpg,.jpeg,.bmp,.webp" onchange="previewImage(this)">
                            <div class="image-preview-box" id="uploadPreviewBox">
                                <img id="uploadPreviewImg" src="" alt="預覽圖片">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">或選擇既有圖片</label>
                            <select class="form-select" name="existing_image" id="existing_image">
                                <option value="">-- 請選擇圖片 --</option>
                                <?php foreach ($existingImages as $img): ?>
                                    <option value="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars(basename($img)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" style="width:auto;" onclick="closeEditor()">取消</button>
                        <button type="submit" class="btn btn-primary" style="width:auto;">儲存並發布</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const boardStates = <?= json_encode($boardStates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function openEditor(boardId) {
            const modal = document.getElementById('editorModal');
            const board = boardStates[boardId];

            document.getElementById('board_id').value = boardId;
            document.getElementById('modalTitle').textContent = '發布訊息 - ' + board.name;

            // 預填資料
            const displayType = board.display_type || 'text';
            document.querySelector(`input[name="display_type"][value="${displayType}"]`).checked = true;

            document.getElementById('text_content').value = board.text_content || '';
            document.getElementById('text_color').value = board.text_color || '#c6ff00';
            document.getElementById('text_size').value = board.text_size || 54;
            document.getElementById('existing_image').value = board.image_path || '';

            toggleDisplayType();
            modal.style.display = 'block';
        }

        function closeEditor() {
            document.getElementById('editorModal').style.display = 'none';
        }

        function toggleDisplayType() {
            const type = document.querySelector('input[name="display_type"]:checked').value;
            document.getElementById('textFields').classList.toggle('hidden', type !== 'text');
            document.getElementById('imageFields').classList.toggle('hidden', type !== 'image');
        }

        function previewImage(input) {
            const box = document.getElementById('uploadPreviewBox');
            const img = document.getElementById('uploadPreviewImg');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    box.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                box.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editorModal');
            if (event.target === modal) {
                closeEditor();
            }
        };
    </script>
</body>

</html>