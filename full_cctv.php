<?php
$raw_data = [
    [24.4438, 120.8968, "苗62鄉道", "3K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-23K+100"],
    [24.4438, 120.8968, "苗62鄉道", "3K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T8-006K+380"],
    [24.4592, 120.9139, "苗62鄉道", "5.5K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T7A-65K+600"],
    [24.4592, 120.9139, "苗62鄉道", "5.5K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T7A-65K+600"],
    [24.4615, 120.9149, "苗62鄉道", "5.7K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-23K+100"],
    [24.4615, 120.9149, "苗62鄉道", "5.7K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-23K+100"],
    [24.4618, 120.9152, "苗62鄉道", "5.9K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-22K+600-1"],
    [24.4618, 120.9152, "苗62鄉道", "5.9K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-22K+600-1"],
    [24.4634, 120.9167, "苗62鄉道", "6K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-013K+920W"],
    [24.4634, 120.9167, "苗62鄉道", "6K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-013K+920W"],
    [24.4653, 120.9182, "苗62鄉道", "6.5K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-013K+900E"],
    [24.4653, 120.9182, "苗62鄉道", "6.5K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T76-013K+900E"],
    [24.4672, 120.9219, "苗62鄉道", "6.9K", "(上山方向)", "https://cctv-ss04.thb.gov.tw:443/T74-1K+650"],
    [24.4672, 120.9219, "苗62鄉道", "6.9K", "(下山方向)", "https://cctv-ss04.thb.gov.tw:443/T74-1K+650"],
    [24.472447, 120.804505, "119線與119甲線路口", "", "", "https://cctv-ss04.thb.gov.tw:443/T72-29K+300"],
    [24.455985, 120.877737, "台72線與台3線路口", "", "", "https://cctv-ss04.thb.gov.tw:443/T72-030K+690"],
    [24.414874, 120.862746, "台3線與苗60線路口", "", "", "https://cctv-ss04.thb.gov.tw:443/T74-005K+300N"]
];

function h($str)
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function captureSnapshot($url)
{
    $snapshotDirFs = __DIR__ . '/snapshots';
    $snapshotDirWeb = './snapshots';
    $cacheSeconds = 30; // 30秒內直接用舊圖，可改成 10 / 60

    if (!is_dir($snapshotDirFs)) {
        mkdir($snapshotDirFs, 0777, true);
    }

    $filename = md5($url) . '.jpg';
    $savePath = $snapshotDirFs . '/' . $filename;

    // 如果快取還有效，就直接回傳，不重新抓
    if (file_exists($savePath) && filesize($savePath) > 0) {
        $fileAge = time() - filemtime($savePath);
        if ($fileAge < $cacheSeconds) {
            return $snapshotDirWeb . '/' . $filename . '?v=' . filemtime($savePath);
        }
    }

    $python = 'C:\\Users\\User\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';
    $script = __DIR__ . '/capture_snapshot.py';

    if (!file_exists($script)) {
        if (file_exists($savePath) && filesize($savePath) > 0) {
            return $snapshotDirWeb . '/' . $filename . '?v=' . filemtime($savePath);
        }
        return '';
    }

    $cmd = '"' . $python . '" '
        . escapeshellarg($script) . ' '
        . escapeshellarg($url) . ' '
        . escapeshellarg($savePath) . ' 2>&1';

    $output = shell_exec($cmd);
    clearstatcache(true, $savePath);

    if (file_exists($savePath) && filesize($savePath) > 0) {
        return $snapshotDirWeb . '/' . $filename . '?v=' . filemtime($savePath);
    }

    return '';
}

$cam_images = [];
$url_cache = [];

// 同一個遠端網址只抓一次，避免重複抓圖拖慢頁面
foreach ($raw_data as $i => $cam) {
    $url = $cam[5];

    if (!isset($url_cache[$url])) {
        $url_cache[$url] = captureSnapshot($url);
    }

    $cam_images[$i] = $url_cache[$url];
}
?>
<!DOCTYPE html>
<html lang="zh-tw">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/w3-4.15.css" />
    <title>即時影像</title>
    <style>
        body {
            background: #f3f3f3;
            font-family: "Microsoft JhengHei", sans-serif;
            margin: 0;
        }

        .page-title {
            padding: 18px 24px 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .video-card {
            position: relative;
            overflow: hidden;
            background: #000;
        }

        .video-card img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
            cursor: pointer;
            background: #000;
        }

        .fallback-box {
            height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1f1f1f;
            color: #fff;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .expand-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.72);
            border: none;
            padding: 6px;
            cursor: pointer;
            border-radius: 4px;
            z-index: 2;
        }

        .expand-btn:hover {
            background: rgba(0, 0, 0, 0.88);
        }

        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.65);
            color: #fff;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            letter-spacing: 1px;
            z-index: 2;
        }

        .w3-modal {
            padding-top: 30px;
            background-color: rgba(0, 0, 0, 0.88);
        }

        .w3-modal-content {
            background: #111;
            max-width: 92%;
            max-height: 92%;
            margin: auto;
            padding: 12px;
            box-sizing: border-box;
        }

        .modal-image-wrap {
            text-align: center;
        }

        #modalImg {
            width: 100%;
            max-height: 82vh;
            object-fit: contain;
            display: block;
            background: #000;
        }

        .modal-caption {
            color: #ddd;
            text-align: center;
            padding-top: 10px;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .modal-close {
            color: #fff !important;
            font-size: 30px !important;
            z-index: 10001;
        }

        @media (max-width: 768px) {

            .video-card img,
            .fallback-box {
                height: 240px;
            }

            .page-title {
                font-size: 20px;
                padding: 16px 16px 0;
            }
        }
    </style>
</head>

<body>

    <div class="page-title">即時影像</div>

    <div class="w3-row-padding">
        <?php foreach ($raw_data as $i => $cam): ?>
            <?php
            $title = trim(preg_replace('/\s+/', ' ', $cam[2] . ' ' . $cam[3] . ' ' . $cam[4]));
            $localImg = $cam_images[$i];
            $remoteUrl = $cam[5];
            ?>
            <div class="w3-half w3-container w3-padding">
                <div class="w3-card-4" style="margin-top:20px;">
                    <header class="w3-container w3-light-gray w3-padding w3-border">
                        <span style="font-size:14px;"><?php echo h($title); ?></span>
                    </header>

                    <div class="w3-container w3-border-left w3-border-right w3-border-bottom" style="overflow-x:auto;">
                        <div class="video-card">


                            <?php if ($localImg !== ''): ?>
                                <img
                                    src="<?php echo h($localImg); ?>"
                                    data-image="<?php echo h($remoteUrl); ?>"
                                    data-title="<?php echo h($title); ?>"
                                    onclick="openModal(this.dataset.image, this.dataset.title)"
                                    alt="<?php echo h($title); ?>">

                                <button
                                    type="button"
                                    class="expand-btn"
                                    data-image="<?php echo h($remoteUrl); ?>"
                                    data-title="<?php echo h($title); ?>"
                                    onclick="event.stopPropagation(); openModal(this.dataset.image, this.dataset.title)">
                                    <svg width="20" height="20" viewBox="0 0 24 20" fill="white">
                                        <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" />
                                    </svg>
                                </button>
                            <?php else: ?>
                                <div class="fallback-box">抓圖失敗</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="imgModal" class="w3-modal">
        <span class="w3-button w3-display-topright modal-close" onclick="closeModal()">&times;</span>
        <div class="w3-modal-content w3-animate-zoom">
            <div class="modal-image-wrap">
                <img id="modalImg" src="" alt="放大畫面">
                <div id="modalCaption" class="modal-caption"></div>
            </div>
        </div>
    </div>

    <script>
        let modalAutoCloseTimer = null;

        function buildNoCacheUrl(url) {
            const separator = url.includes('?') ? '&' : '?';
            return url + separator + 't=' + Date.now();
        }

        function clearModalTimer() {
            if (modalAutoCloseTimer) {
                clearTimeout(modalAutoCloseTimer);
                modalAutoCloseTimer = null;
            }
        }

        function openModal(src, title) {
            const modal = document.getElementById('imgModal');
            const modalImg = document.getElementById('modalImg');
            const modalCaption = document.getElementById('modalCaption');

            // 先清掉前一次計時，避免重複倒數
            clearModalTimer();

            modalImg.src = buildNoCacheUrl(src);
            modalCaption.textContent = title || '';
            modal.style.display = 'block';

            // 60 秒後自動關閉
            modalAutoCloseTimer = setTimeout(function() {
                closeModal();
            }, 60000);
        }

        function closeModal() {
            const modal = document.getElementById('imgModal');
            const modalImg = document.getElementById('modalImg');
            const modalCaption = document.getElementById('modalCaption');

            clearModalTimer();

            modal.style.display = 'none';
            modalImg.src = '';
            modalCaption.textContent = '';
        }

        document.getElementById('imgModal').addEventListener('click', function(e) {
            if (e.target.id === 'imgModal') {
                closeModal();
            }
        });
    </script>

</body>

</html>