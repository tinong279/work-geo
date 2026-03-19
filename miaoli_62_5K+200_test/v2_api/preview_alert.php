<?php
// preview_alert.php
require_once 'db.php';

$filename = $_GET['image'] ?? '';
$imageUrl = $_GET['url'] ?? ''; // 從網址接收圖片路徑

// --- 新增邏輯：從資料庫查詢該圖片所屬的攝影機 ID ---
$camId = '1'; // 預設值
if ($filename) {
    try {
        $stmt = $pdo->prepare("SELECT camera_id FROM alert_image WHERE image = ? LIMIT 1");
        $stmt->execute([$filename]);
        $row = $stmt->fetch();
        if ($row) {
            $camId = $row['camera_id'];
        }
    } catch (Exception $e) {
        // 靜態處理錯誤，不影響頁面顯示
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>影像審核中心</title>
    <style>
        body {
            font-family: "Microsoft JhengHei", sans-serif;
            background: #f4f6f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: white;
            width: 90%;
            max-width: 600px;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .img-box {
            border: 2px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
            background: #000;
        }

        img {
            width: 100%;
            display: block;
        }

        h2 {
            color: #333;
            margin: 0;
        }

        .info-text {
            color: #666;
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-confirm {
            background: #28a745;
            color: white;
        }

        .btn-confirm:hover {
            background: #218838;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .cam-badge {
            background: #007bff;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="cam-badge">攝影機 <?php echo htmlspecialchars($camId); ?></div>

        <h2>🔍 疑似落石影像審核</h2>
        <p class="info-text">請檢視下方擷圖，確認是否要示警並發佈至網頁？</p>

        <div class="img-box">
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="待審核影像">
        </div>

        <p style="font-size: 12px; color: #999;">原始檔名：<?php echo htmlspecialchars($filename); ?></p>

        <div class="btn-group">
            <a href="index.php" class="btn btn-cancel">不處理 (誤報)</a>

            <a href="do_confirm.php?image=<?php echo urlencode($filename); ?>&url=<?php echo urlencode($imageUrl); ?>&cam_id=<?php echo $camId; ?>"
                class="btn btn-confirm">確認，上傳至網頁</a>
        </div>
    </div>
</body>

</html>