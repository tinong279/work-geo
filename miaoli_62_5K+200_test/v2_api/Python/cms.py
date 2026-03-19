import socket
import struct
import random
import time
import json
import os
from datetime import datetime
import logging
import requests

# 嘗試匯入資料庫函式庫，如果失敗則提供安裝指引
try:
    import mysql.connector
except ImportError:
    print("錯誤：找不到 'mysql-connector-python' 函式庫。")
    print("請在你的終端機執行以下指令來安裝：")
    print("pip install mysql-connector-python")
    exit()

# --- 整合設定區 ---

# 主要設定 (取代 config.json)
APP_CONFIG = {
    "app": {
        "name": "苗栗62線5K+200落石告警系統",
        "discord_webhook": "https://discord.com/api/webhooks/1359724859395150055/HM5FPZF5P8ZKHFbYeQIIdLe0UZwr7ZqOaMzEnxXFbfLYT4aD_o8dQjZyu7WZ-WdPotgc",
        "internal_discord_webhook": "https://discord.com/api/webhooks/1410154321085202493/PtEes6e50lsG24DYFFg6ldRpsgEPZa615l5JWRlsxfsXJj8RSpTUYmD4fhCKsVECPmJV"
    },
    "cms": {
        "auto_shutdown_minutes": 60
    }
}

# Modbus 設備設定
MODBUS_HOST = '116.59.9.174'
MODBUS_PORT = 502
MODBUS_UNIT_ID = 1

# 資料庫設定
DB_CONFIG = {
    'host': '210.71.231.250',
    'user': 'timmy',
    'password': '0000',
    'database': 'miaoli_62_5k_200'
}

# --- 核心功能區 ---

# 將快取改為全域變數 (取代 cache.json)
APP_CACHE = {}

def setup_logging():
    """設定日誌記錄器"""
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler("modbus_monitor.log"),
            logging.StreamHandler()
        ]
    )

def send_discord_notification(message, internal):
    """發送 Discord 通知"""
    key = 'internal_discord_webhook' if internal else 'discord_webhook'
    webhook_url = APP_CONFIG['app'].get(key)
    
    if not webhook_url:
        logging.error(f"在 APP_CONFIG 中找不到 Discord Webhook URL: {key}")
        return False
        
    try:
        response = requests.post(webhook_url, json={'content': message})
        if 200 <= response.status_code < 300:
            logging.info("Discord 通知發送成功。")
            return True
        else:
            logging.error(f"Discord 通知發送失敗，狀態碼: {response.status_code}, 回應: {response.text}")
            return False
    except requests.RequestException as e:
        logging.error(f"Discord 通知發送時發生網路錯誤: {e}")
        return False

def get_db_connection():
    """建立資料庫連線"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as e:
        logging.error(f"資料庫連線失敗: {e}")
        return None

def process_cms_status(response):
    """處理 Modbus 回應，偵測狀態變化並發送通知"""
    if len(response) < 10:
        logging.warning(f"Modbus 回應長度不足 (收到 {len(response)} bytes)。")
        return

    # 1. 【關鍵修正】從回應中取出第 10 個 byte (索引值 9)，再進行位元運算
    current_coil_status = response[9] & 0x01
    current_status_text = "開啟" if current_coil_status else "關閉"
    logging.info(f"牌面硬體狀態: {current_status_text}")

    # 2. 讀取記憶體快取中的上次狀態
    last_observed_status = APP_CACHE.get('cms_last_observed_status')

    # 3. 比較狀態是否發生變化
    if current_coil_status != last_observed_status:
        logging.warning(f"狀態變化偵測！從 [{last_observed_status}] 變為 [{current_coil_status}]")
        
        APP_CACHE['cms_last_observed_status'] = current_coil_status

        if last_observed_status is None:
            logging.info(f"首次執行，已記錄初始狀態: {current_status_text}")
            return
            
        last_status_text = "開啟" if last_observed_status else "關閉"
        
        # 4. 查詢資料庫，找出變更原因
        source = '未知情形更改牌面狀態'
        conn = get_db_connection()
        if conn:
            try:
                with conn.cursor(dictionary=True) as cursor:
                    cursor.execute("SELECT status, source, trigger_time FROM cms_status ORDER BY id DESC LIMIT 1")
                    latest_db_record = cursor.fetchone()
                    
                    if latest_db_record:
                        last_db_record_time = latest_db_record['trigger_time']
                        minutes_since_db_change = (datetime.now() - last_db_record_time).total_seconds() / 60
                        
                        if minutes_since_db_change <= 3 and latest_db_record['status'] == current_coil_status:
                            source = latest_db_record['source']
                            logging.info(f"原因分析：此變化與 {minutes_since_db_change:.2f} 分鐘內的軟體操作吻合。")
            except Exception as e:
                logging.error(f"查詢資料庫時發生錯誤: {e}")
            finally:
                if conn.is_connected():
                    conn.close()

        if source == '未知情形更改牌面狀態':
            logging.warning("原因分析：判定為未知干預！將寫入資料庫。")
            conn = get_db_connection()
            if conn:
                try:
                    with conn.cursor() as cursor:
                        query = "INSERT INTO cms_status (trigger_time, status, source) VALUES (%s, %s, %s)"
                        values = (datetime.now(), current_coil_status, source)
                        cursor.execute(query, values)
                        conn.commit()
                except Exception as e:
                    logging.error(f"寫入未知狀態至資料庫時發生錯誤: {e}")
                finally:
                    if conn.is_connected():
                        conn.close()

        # 5. 發送通知
        now_str = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        app_name = APP_CONFIG['app'].get('name', '監控系統')
        
        message = (
            f"【{app_name}內部通知】\n"
            f"通報時間: {now_str}\n"
            f"牌面狀態變化: 從 {last_status_text} 變為 {current_status_text}\n"
            f"牌面變更來源: {source}"
        )
        send_discord_notification(message, internal=True)

def main():
    """主執行函式"""
    transaction_id = random.randint(1, 65535)
    # packet = struct.pack('>HHHBBHH', transaction_id, 0, 6, MODBUS_UNIT_ID, 1, 0(DO通道), 1)
    packet = struct.pack('>HHHBBHH', transaction_id, 0, 6, MODBUS_UNIT_ID, 1, 0, 1)

    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(10.0)
            s.connect((MODBUS_HOST, MODBUS_PORT))
            s.sendall(packet)
            response = s.recv(1024)
            
            if response:
                process_cms_status(response)

    except socket.timeout:
        logging.error(f"連線到 {MODBUS_HOST}:{MODBUS_PORT} 超時。")
    except socket.error as e:
        logging.error(f"Socket 錯誤: {e}")
    except Exception as e:
        logging.critical(f"執行過程中發生未預期的錯誤: {e}")

if __name__ == "__main__":
    setup_logging()
    logging.info("Modbus 監控腳本啟動。")
    
    while True:
        try:
            main()
            logging.info("執行完畢，等待 60 秒後再次執行...")
            time.sleep(60)
        except KeyboardInterrupt:
            logging.info("收到中斷訊號，腳本即將關閉。")
            break
        except Exception as e:
            logging.critical(f"主迴圈發生致命錯誤: {e}")
            time.sleep(60)
