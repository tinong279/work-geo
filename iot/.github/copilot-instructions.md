# Copilot 使用說明 — iot (PHP)

目的：讓 AI 編碼/修正時能快速上手本專案的結構、常見模式與開發流程。

- **架構重點**:
  - 簡單 PHP + MySQL Web 前端，放在 htdocs 下以 XAMPP 運行。
  - DB 連線集中在 [db.php](db.php#L1-L40)（使用 PDO、ERRMODE_EXCEPTION、root/空密碼，DB 名稱為 `iot_db`）。
  - 前端頁面以單一檔案 PHP 呈現（例如 [voltage_view.php](voltage_view.php#L1-L220)、[test/table_view.php](test/table_view.php#L1-L80)），直接 `require 'db.php'` 後執行 SQL 查詢再輸出 HTML/JS。

- **資料流與關鍵表 / 欄位**:
  - 主資料來自 `sensor_measurements`（見 [voltage_view.php](voltage_view.php#L1-L120) 的查詢）。
  - `sensor_id = 1` 通常代表「設備電壓」，程式會以此欄位計算燈號狀態與圖表數據（見 [voltage_view.php](voltage_view.php#L40-L110) 與 [test/voltage_view_b.php](test/voltage_view_b.php#L1-L80)）。
  - 常見查詢模式：
    - 取最新時間：`SELECT MAX(trigger_time) FROM sensor_measurements`（用於顯示最新資料列）
    - 時間區間查詢：`WHERE trigger_time BETWEEN ? AND ?`
    - 取最近 50 筆：`ORDER BY trigger_time DESC LIMIT 50`

- **專案慣例與實作模式（可直接仿寫）**:
  - 使用 PDO prepared statements (`$pdo->prepare()` / `$stmt->execute([...])`) 。
  - 只在 `db.php` 設定 PDO 與錯誤模式，其他檔案 `require 'db.php'` 即可取得 `$pdo`。
  - 前端圖表使用 Chart.js CDN（見 [voltage_view.php](voltage_view.php#L200-L320) 的 script 標籤），資料由 PHP `json_encode()` 傳入 JS。
  - UI 小元件與表示（例如紅/綠燈）由伺服端先計算好再輸出到 HTML，避免在前端重複邏輯（見 [test/table_view.php](test/table_view.php#L1-L80) 中先計算 `$voltageStatus`）。
  - 時間輸入採用 `datetime-local`，接收參數為 `start` / `end`（見 [voltage_view.php](voltage_view.php#L30-L60) 的參數處理）。

- **錯誤處理與除錯提示**:
  - `db.php` 會在例外時 `error_log($e->getMessage())` 並 `die("系統暫時無法連線，請稍後再試")`，因此查錯時檢查 Apache/PHP error log 與此訊息（檔案：xampp/php/logs 或 Apache error log）。
  - 若資料查詢回傳空集合，頁面通常會 `die("尚無資料")`，非錯誤但代表資料庫無資料／時間參數錯誤。

- **開發/執行流程（專案特有）**:
  - 將專案放在 XAMPP 的 `htdocs/iot`，啟動 Apache + MySQL。
  - 瀏覽器開啟 `http://localhost/iot/voltage_view.php` 或 `http://localhost/iot/test/table_view.php` 以檢視頁面。
  - 預設 DB：`iot_db`，預設使用者 `root`、密碼空字串（如要變更請更新 `db.php`）。

- **常見改動與注意事項（給 AI 的具體建議）**:
  - 當新增查詢或欄位時，先檢查 `sensor_id = 1` 的業務意義；不要改動該判斷而忽略對燈號/電壓圖的影響。
  - 若需要把 `positions` 拆成 DB 表，請同時更新所有使用該陣列的檔案（`voltage_view.php`、`test/voltage_view_b.php`、`test/table_view.php`）。
  - Chart.js 的資料長度會影響第二條警戒線陣列長度：若改變資料範圍，確保 `Array(values.length).fill(3.3)` 同步更新。
  - 保留現有使用 `die(...)` 的簡短使用者訊息，不要回傳敏感的 DB 錯誤給前端。

- **重要檔案（快速連結範例）**:
  - [db.php](db.php#L1-L40)
  - [voltage_view.php](voltage_view.php#L1-L220)
  - [test/table_view.php](test/table_view.php#L1-L80)
  - [test/voltage_trend.php](test/voltage_trend.php#L1-L80)
  - [test/voltage_view_b.php](test/voltage_view_b.php#L1-L80)

請檢查上述內容是否與你的預期一致，或指出要補充的專案細節（例如：實際 DB schema、部署步驟、或想加入的自動化測試）。
