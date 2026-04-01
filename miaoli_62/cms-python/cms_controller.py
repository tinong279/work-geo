"""
CMS牌面控制系統 - 主控制程式（簡化版）
負責讀取 cms_status 表並執行牌面更新

執行方式：
1. Windows排程每分鐘執行一次：python cms_controller.py --once
2. 或使用持續監控模式：python cms_controller.py

功能：
- 讀取 cms_status 表獲取當前應顯示的內容
- 呼叫對應的發送程式（文字或圖片）
- 記錄執行日誌

簡化邏輯：
- 不需要優先級判斷
- 不需要規則掃描
- 直接讀取 cms_status 並執行
"""

from led_display import LEDDisplay
import pymysql
import subprocess
import time
from datetime import datetime, timedelta
import os
import sys
from pathlib import Path
from pymysql.cursors import DictCursor

# 設定路徑
BASE_DIR = Path(__file__).parent.parent
CMS_CONTROL_DIR = BASE_DIR / "cms-system" / "cms-control"
sys.path.insert(0, str(CMS_CONTROL_DIR))

# 匯入LED控制模組

# 資料庫設定
# DB_SERVER = "210.71.231.140"
# DB_NAME = "yunlin_gukeng"
# DB_USER = "tomoffice"
# DB_PASSWORD = "tomoffice"
DB_HOST = "localhost"
DB_NAME = "miaoli_nz"
DB_USER = "root"
DB_PASSWORD = ""


# CMS控制程式路徑
CMS_IMAGE_SENDER_EXE = CMS_CONTROL_DIR / "cms-send-bmp" / "main.exe"


def get_db_connection():
    """建立 MySQL 資料庫連線"""
    return pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASSWORD,
        database=DB_NAME,
        charset="utf8mb4",
        cursorclass=DictCursor,
        autocommit=False,
    )


def execute_cms_updates():
    """
    執行CMS牌面更新
    讀取 cms_status 並發送到實體設備
    """
    conn = get_db_connection()
    cursor = conn.cursor()

    try:
        # 獲取所有需要更新的CMS
        # 條件：
        # 1. 尚未執行過 (last_executed IS NULL)
        # 2. 內容已更新 (last_updated > last_executed)
        # 3. 超過30分鐘未發送 (防止牌面斷線重啟後內容遺失)
        cursor.execute(
            """
            SELECT id, ip_address, display_type, text_content, text_color, 
                   text_size, image_path, current_mode
            FROM cms_status
            WHERE is_active = 1
            AND (
                last_executed IS NULL 
                OR last_updated > last_executed
                OR TIMESTAMPDIFF(MINUTE, last_executed, NOW()) >= 1
            )
        """
        )

        cms_updates = cursor.fetchall()

        if not cms_updates:
            print(f"[{datetime.now()}] 無需更新的CMS")
            return

        for cms in cms_updates:
            (
                cms_id,
                ip,
                display_type,
                text_content,
                text_color,
                text_size,
                image_path,
                mode,
            ) = cms

            success = False
            message = ""
            start_time = datetime.now()

            try:
                if display_type == "text":
                    # 發送文字
                    success, message = send_text_to_cms(
                        ip, text_content, text_color, text_size
                    )
                else:  # image
                    # 發送圖片
                    success, message = send_image_to_cms(ip, image_path)

                # 計算執行時間
                duration = int((datetime.now() - start_time).total_seconds() * 1000)

                # 更新執行狀態
                status = "success" if success else "failed"
                cursor.execute(
                    """
                    UPDATE cms_status
                    SET last_executed = NOW(),
                        execution_status = ?,
                        execution_message = ?
                    WHERE id = ?
                """,
                    status,
                    message,
                    cms_id,
                )

                # 記錄執行日誌
                content_summary = text_content if display_type == "text" else image_path
                cursor.execute(
                    """
                    INSERT INTO cms_execution_log 
                    (cms_id, execution_mode, display_type, content, status, message, execution_duration)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """,
                    cms_id,
                    mode,
                    display_type,
                    content_summary,
                    status,
                    message,
                    duration,
                )

                conn.commit()

                print(f"[{datetime.now()}] CMS-{cms_id} ({ip}) 更新{status}: {message}")

            except Exception as e:
                message = f"執行失敗: {e}"
                print(f"[{datetime.now()}] CMS-{cms_id} 錯誤: {message}")

                cursor.execute(
                    """
                    UPDATE cms_status
                    SET execution_status = 'failed',
                        execution_message = ?
                    WHERE id = ?
                """,
                    message,
                    cms_id,
                )
                conn.commit()

    except Exception as e:
        print(f"執行CMS更新時發生錯誤: {e}")
    finally:
        conn.close()


def send_text_to_cms(ip, text, color="green", size=24):
    """
    發送文字到CMS
    使用 LED Display Python 模組
    """
    try:
        # 顏色對應
        color_map = {
            "red": LEDDisplay.COLOR_RED,
            "green": LEDDisplay.COLOR_GREEN,
            "yellow": LEDDisplay.COLOR_YELLOW,
            "orange": LEDDisplay.COLOR_YELLOW,  # 橘色使用黃色
            "blue": LEDDisplay.COLOR_BLUE,
            "white": LEDDisplay.COLOR_WHITE,
        }
        led_color = color_map.get(color.lower(), LEDDisplay.COLOR_GREEN)

        # 使用 LEDDisplay 發送文字 (牌面尺寸: 256x128)
        with LEDDisplay(ip=ip, port=5200, width=128, height=256) as led:
            led.show_text(text, color=led_color)

        return True, f"文字發送成功: {text}"

    except Exception as e:
        return False, f"發送錯誤: {e}"


def send_image_to_cms(ip, image_path):
    """
    發送圖片到CMS
    使用cms-send-bmp/main.exe
    """
    try:
        # 檢查圖片是否存在
        full_path = BASE_DIR / image_path
        if not full_path.exists():
            return False, f"圖片檔案不存在: {image_path}"

        # 執行命令
        # 格式: main.exe 1 IP 5200 255.255.255.255 60 128 256 1 圖片路徑
        cmd = [
            str(CMS_IMAGE_SENDER_EXE),
            "1",
            ip,
            "5200",
            "255.255.255.255",
            "60",
            "128",
            "256",
            "1",
            str(full_path),
        ]

        result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)

        if result.returncode == 0:
            return True, f"圖片發送成功: {image_path}"
        else:
            return False, f"發送失敗 (code {result.returncode}): {result.stderr}"

    except Exception as e:
        return False, f"發送錯誤: {e}"


def main_loop():
    """主執行迴圈"""
    print("=" * 60)
    print("CMS牌面控制系統啟動（簡化版）")
    print(f"啟動時間: {datetime.now()}")
    print("=" * 60)

    loop_count = 0

    while True:
        try:
            loop_count += 1

            # 執行CMS更新
            execute_cms_updates()

            # 每10次顯示狀態
            if loop_count % 10 == 0:
                print(f"[{datetime.now()}] 系統運行中... (執行次數: {loop_count})")

            # 等待60秒
            time.sleep(60)

        except KeyboardInterrupt:
            print("\n\n系統停止")
            break
        except Exception as e:
            print(f"\n主迴圈錯誤: {e}")
            import traceback

            traceback.print_exc()
            time.sleep(60)


def run_once():
    """單次執行模式（用於Windows排程）"""
    print(f"[{datetime.now()}] CMS控制程式執行")

    try:
        execute_cms_updates()
        print(f"[{datetime.now()}] 執行完成\n")

    except Exception as e:
        print(f"執行失敗: {e}")
        import traceback

        traceback.print_exc()


if __name__ == "__main__":
    import sys

    # 檢查命令列參數
    if len(sys.argv) > 1 and sys.argv[1] == "--once":
        # 單次執行模式
        run_once()
    else:
        # 持續監控模式
        main_loop()
