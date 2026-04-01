"""
雲林古坑邊坡監測系統 - 即時警報
監控傾斜儀和雨量筒，達到管理值時發送通報

規則：
- 每日每個感測器通報上限：MAX_DAILY_ALERTS 次（包含斷線和數值警報）
- 階段變化時可再通報（level1→level2 或 正常→斷線）
- 10秒輪詢一次，只查詢最近30秒資料
- 第二次通報需間隔：ALERT_INTERVAL_MINUTES 分鐘
- 斷線判定時間：DATA_TIMEOUT_MINUTES 分鐘無資料
"""

import pyodbc
import requests
import json
import time
from datetime import datetime, timedelta
import os
from pathlib import Path

# 載入配置
CONFIG_FILE = Path(__file__).parent / 'config.json'
with open(CONFIG_FILE, 'r', encoding='utf-8') as f:
    config = json.load(f)

# 資料庫設定
DB_SERVER = config['database']['server']
DB_NAME = config['database']['database']
DB_NAME_OLD = config['database']['database_old']
DB_USER = config['database']['username']
DB_PASSWORD = config['database']['password']

# Discord 和 LINE 設定
DISCORD_WEBHOOK_URL = config['discord']['webhook_url']
LINE_CHANNEL_ACCESS_TOKEN = config['line']['channel_access_token']
LINE_GROUP_ID = config['line']['group_id']

# 警報設定
DATA_TIMEOUT_MINUTES = config['alert_settings']['data_timeout_minutes']
MAX_DAILY_ALERTS = config['alert_settings']['max_daily_alerts']
ALERT_INTERVAL_MINUTES = config['alert_settings']['alert_interval_minutes']
EXCLUDED_STATIONS = config['alert_settings']['excluded_stations']

# 警報狀態檔案
ALERT_STATE_FILE = Path(__file__).parent / 'alert_state.json'

# 氣象局古坑雨量站設定
CWA_STATION_ID = '467290'  # 古坑雨量站
CWA_STATION_NAME = '古坑雨量站'
CWA_API_URL = 'https://opendata.cwa.gov.tw/api/v1/rest/datastore/O-A0002-001'
CWA_API_KEY = 'CWA-A78C4C0C-08A4-4E02-8B90-034E82FAB07A'  # 請填入您的氣象局 API Key

# CMS牌面觸發條件（4個級別）
# Level 1: 時雨量≥40mm 或 24HR≥80mm
# Level 2: 3HR≥100mm 或 24HR≥200mm
# Level 3: 3HR≥200mm 或 24HR≥350mm
# Level 4: 24HR≥500mm
CMS_LEVEL1_HOURLY = 40
CMS_LEVEL1_DAILY = 80
CMS_LEVEL2_3HR = 100
CMS_LEVEL2_DAILY = 200
CMS_LEVEL3_3HR = 200
CMS_LEVEL3_DAILY = 350
CMS_LEVEL4_DAILY = 500

# 定義需要查詢舊資料庫的感測器 ID
OLD_DB_SENSOR_IDS = [
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
    11, 12, 13, 14, 15, 16, 17, 21, 22, 28, 29, 68, 69
]


def get_db_connection(use_old_db=False):
    """建立資料庫連線"""
    db_name = DB_NAME_OLD if use_old_db else DB_NAME
    conn_str = f'DRIVER={{SQL Server}};SERVER={DB_SERVER};DATABASE={db_name};UID={DB_USER};PWD={DB_PASSWORD}'
    return pyodbc.connect(conn_str)


def load_alert_state():
    """載入警報狀態"""
    if ALERT_STATE_FILE.exists():
        with open(ALERT_STATE_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    return {}


def save_alert_state(state):
    """儲存警報狀態"""
    with open(ALERT_STATE_FILE, 'w', encoding='utf-8') as f:
        json.dump(state, f, ensure_ascii=False, indent=2)


def reset_daily_state(state):
    """重置每日狀態（每天凌晨執行）"""
    today = datetime.now().date().isoformat()

    # 清除非今日的狀態
    sensors_to_remove = []
    for sensor_key, sensor_state in state.items():
        if sensor_state.get('date') != today:
            sensors_to_remove.append(sensor_key)

    for sensor_key in sensors_to_remove:
        del state[sensor_key]

    return state


def get_sensors_to_monitor():
    """取得需要監控的傾斜儀和雨量筒"""
    conn = get_db_connection()
    cursor = conn.cursor()

    query = """
        SELECT id, station_id, chinese_name, type, level1, level2, level3, init
        FROM sensor
        WHERE type IN ('tilt', 'rain')
        AND station_id NOT IN ({})
        ORDER BY id
    """.format(','.join(map(str, EXCLUDED_STATIONS)))

    cursor.execute(query)
    sensors = []
    for row in cursor.fetchall():
        sensors.append({
            'id': row[0],
            'station_id': row[1],
            'name': row[2],
            'type': row[3],
            'level1': row[4],
            'level2': row[5],
            'level3': row[6],
            'init': row[7]
        })

    conn.close()
    return sensors


def check_recent_data(sensor, seconds=300):
    """檢查感測器最近的資料"""
    sensor_id = sensor['id']
    sensor_type = sensor['type']

    use_old_db = sensor_id in OLD_DB_SENSOR_IDS
    conn = get_db_connection(use_old_db)
    cursor = conn.cursor()

    # 只查詢最近300秒的資料
    cutoff_time = datetime.now() - timedelta(seconds=seconds)

    try:
        query = """
            SELECT TOP 1 trigger_time, value
            FROM sensor_record
            WHERE sensor_id = ? AND trigger_time >= ?
            ORDER BY trigger_time DESC
        """
        cursor.execute(query, sensor_id, cutoff_time)
        row = cursor.fetchone()

        if row is None:
            return None, None, 0  # 沒有新資料

        trigger_time = row[0]
        value = float(row[1]) if row[1] is not None else None

        if value is None:
            return trigger_time, None, 0

        # 判斷管理值等級
        level = 0
        if sensor_type == 'tilt':
            # 傾斜儀直接比較 value
            if sensor['level3'] and value >= sensor['level3']:
                level = 3
            elif sensor['level2'] and value >= sensor['level2']:
                level = 2
            elif sensor['level1'] and value >= sensor['level1']:
                level = 1

        elif sensor_type == 'rain':
            # 雨量筒只計算24小時累計雨量用於通報（過濾異常值）
            one_day_ago = datetime.now() - timedelta(hours=24)
            daily_query = """
                SELECT SUM(CAST(value AS FLOAT)) as daily_rain
                FROM sensor_record
                WHERE sensor_id = ? 
                AND trigger_time >= ?
                AND CAST(value AS FLOAT) <= 100
            """
            cursor.execute(daily_query, sensor_id, one_day_ago)
            daily_rain = cursor.fetchone()[0] or 0
            value = daily_rain

            # 判斷通報等級：依據資料庫的 level1(預警)/level2(警戒)/level3(行動)
            if sensor['level3'] and daily_rain >= sensor['level3']:
                level = 3  # 行動值
            elif sensor['level2'] and daily_rain >= sensor['level2']:
                level = 2  # 警戒值
            elif sensor['level1'] and daily_rain >= sensor['level1']:
                level = 1  # 預警值
            else:
                level = 0  # 正常

        return trigger_time, value, level

    except Exception as e:
        print(f"檢查感測器 {sensor['name']} 時發生錯誤: {e}")
        return None, None, 0
    finally:
        conn.close()


def check_sensor_offline(sensor):
    """檢查感測器是否斷線（根據 DATA_TIMEOUT_MINUTES 設定）"""
    try:
        sensor_id = sensor['id']
        use_old_db = sensor_id in OLD_DB_SENSOR_IDS
        conn = get_db_connection(use_old_db)
        cursor = conn.cursor()

        # 查詢最近一筆資料的時間
        timeout_threshold = datetime.now() - timedelta(minutes=DATA_TIMEOUT_MINUTES)
        query = """
            SELECT MAX(trigger_time)
            FROM sensor_record
            WHERE sensor_id = ?
        """
        cursor.execute(query, sensor_id)
        row = cursor.fetchone()

        if row and row[0]:
            last_time = row[0]
            # 如果最後一筆資料超過設定時間，視為斷線
            if last_time < timeout_threshold:
                return True, last_time
            else:
                return False, last_time
        else:
            # 完全沒有資料
            return True, None

    except Exception as e:
        print(f"檢查感測器 {sensor['name']} 斷線狀態時發生錯誤: {e}")
        return False, None
    finally:
        conn.close()


def should_send_alert(sensor_id, current_level, alert_state):
    """判斷是否應該發送警報

    current_level:
        -1: 斷線狀態
        0: 正常
        1-3: 管理值等級

    統一規則：
        - 每日上限：MAX_DAILY_ALERTS 次
        - 第二次通報需間隔：ALERT_INTERVAL_MINUTES 分鐘
        - 等級升高時可再次通報（需符合間隔時間）
    """
    today = datetime.now().date().isoformat()
    sensor_key = str(sensor_id)

    # 如果是正常狀態，不發送
    if current_level == 0:
        return False

    # 取得該感測器的狀態
    if sensor_key not in alert_state:
        # 第一次警報，需要發送
        return True

    sensor_state = alert_state[sensor_key]

    # 檢查日期，如果是新的一天，可以發送
    if sensor_state.get('date') != today:
        return True

    # 檢查當日通報次數（統一上限）
    alert_count = sensor_state.get('alert_count', 0)
    if alert_count >= MAX_DAILY_ALERTS:
        # 已達每日上限
        return False

    last_level = sensor_state.get('last_level', 0)
    last_alert_time = sensor_state.get('last_alert_time')

    # 階段變化（例如從 level1 變成 level2，或從正常變斷線），可以發送
    if current_level != last_level:
        # 但需要間隔至少設定的時間
        if last_alert_time and alert_count >= 1:
            last_time = datetime.fromisoformat(last_alert_time)
            if datetime.now() - last_time < timedelta(minutes=ALERT_INTERVAL_MINUTES):
                return False
        return True

    # 同階段，不發送
    return False


def update_alert_state(sensor_id, level, alert_state):
    """更新警報狀態"""
    today = datetime.now().date().isoformat()
    sensor_key = str(sensor_id)

    # 獲取舊狀態
    old_state = alert_state.get(sensor_key, {})
    old_date = old_state.get('date')
    old_level = old_state.get('last_level', 0)

    # 如果是新的一天或level變化，重置計數
    if old_date != today or old_level != level:
        alert_count = 1
    else:
        alert_count = old_state.get('alert_count', 0) + 1

    alert_state[sensor_key] = {
        'date': today,
        'last_level': level,
        'last_alert_time': datetime.now().isoformat(),
        'alert_count': alert_count
    }

    return alert_state


def send_to_discord(message):
    """發送訊息到 Discord"""
    try:
        data = {
            "content": message,
            "username": "雲林古坑監測系統-即時警報"
        }
        response = requests.post(DISCORD_WEBHOOK_URL, json=data)
        if response.status_code == 204:
            print("✓ Discord 通知發送成功")
            return True
        else:
            print(f"✗ Discord 通知發送失敗: {response.status_code}")
            return False
    except Exception as e:
        print(f"✗ Discord 通知發送錯誤: {e}")
        return False


def send_to_line(message):
    """發送訊息到 LINE Messaging API"""
    try:
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {LINE_CHANNEL_ACCESS_TOKEN}"
        }

        data = {
            "to": LINE_GROUP_ID,
            "messages": [
                {
                    "type": "text",
                    "text": message
                }
            ]
        }

        response = requests.post(
            "https://api.line.me/v2/bot/message/push",
            headers=headers,
            json=data
        )

        if response.status_code == 200:
            print("✓ LINE 通知發送成功")
            return True
        else:
            print(f"✗ LINE 通知發送失敗: {response.status_code} - {response.text}")
            return False
    except Exception as e:
        print(f"✗ LINE 通知發送錯誤: {e}")
        return False


def format_offline_alert_message(sensor, last_time):
    """格式化斷線警報訊息"""
    message = f"⚠️斷線警報\n"
    message += f"地點:{sensor['name']} (ID: {sensor['id']})\n"
    message += f"時間:{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n"
    message += f"---------------------\n"
    if last_time:
        time_diff = datetime.now() - last_time
        hours = int(time_diff.total_seconds() / 3600)
        message += f"最後資料時間: {last_time.strftime('%Y-%m-%d %H:%M:%S')}\n"
        message += f"已斷線: {hours} 小時\n"
    else:
        message += f"無任何歷史資料\n"
    return message


def format_alert_message(sensor, value, level):
    """格式化警報訊息"""
    level_text = {
        1: "預警值",
        2: "警戒值",
        3: "行動值"
    }

    now = datetime.now()
    message = f"地點:{sensor['name']}\n"
    message += f"時間:{now.strftime('%Y-%m-%d %H:%M:%S')}\n"
    message += f"---------------------\n"

    if sensor['type'] == 'tilt':
        message += f"傾斜儀達{level_text.get(level)}: {value:.3f}度\n"
    elif sensor['type'] == 'rain':
        message += f"雨量筒達{level_text.get(level)}:\n"
        message += f"24小時累計雨量: {value:.1f}mm\n"

    return message


def check_cwa_rainfall():
    """檢查氣象局古坑雨量站資料，返回1hr/3hr/24hr雨量"""
    try:
        # 呼叫氣象局 API
        params = {
            'Authorization': CWA_API_KEY,
            'StationId': CWA_STATION_ID
        }

        response = requests.get(CWA_API_URL, params=params, timeout=10)
        response.raise_for_status()
        data = response.json()

        if 'records' not in data or 'Station' not in data['records']:
            print(f"⚠️ 氣象局 API 回傳格式異常")
            return None, 0, 0, 0

        stations = data['records']['Station']
        if not stations:
            print(f"⚠️ 找不到站號 {CWA_STATION_ID} 的資料")
            return None, 0, 0, 0

        station_data = stations[0]

        # 取得觀測時間
        obs_time_str = station_data.get('ObsTime', {}).get('DateTime')
        if obs_time_str:
            obs_time = datetime.fromisoformat(
                obs_time_str).replace(tzinfo=None)
        else:
            obs_time = datetime.now()

        # 取得雨量資料
        rainfall_element = station_data.get('RainfallElement', {})

        # 取得時雨量（1小時）
        hourly_rain = 0
        if 'Past1hr' in rainfall_element:
            hourly_precip = rainfall_element['Past1hr'].get('Precipitation')
            if hourly_precip and hourly_precip != '-':
                try:
                    hourly_rain = float(hourly_precip)
                except ValueError:
                    hourly_rain = 0

        # 取得3小時雨量
        rain_3hr = 0
        if 'Past3hr' in rainfall_element:
            precip_3hr = rainfall_element['Past3hr'].get('Precipitation')
            if precip_3hr and precip_3hr != '-':
                try:
                    rain_3hr = float(precip_3hr)
                except ValueError:
                    rain_3hr = 0

        # 取得日雨量（24小時）
        daily_rain = 0
        if 'Past24hr' in rainfall_element:
            daily_precip = rainfall_element['Past24hr'].get('Precipitation')
            if daily_precip and daily_precip != '-':
                try:
                    daily_rain = float(daily_precip)
                except ValueError:
                    daily_rain = 0

        return obs_time, hourly_rain, rain_3hr, daily_rain

    except requests.exceptions.RequestException as e:
        print(f"✗ 氣象局 API 請求失敗: {e}")
        return None, 0, 0, 0
    except Exception as e:
        print(f"✗ 檢查氣象局雨量時發生錯誤: {e}")
        return None, 0, 0, 0


def get_sensor_rainfall_data(sensor_id):
    """取得感測器的1hr/3hr/24hr雨量數據"""
    try:
        use_old_db = sensor_id in OLD_DB_SENSOR_IDS
        conn = get_db_connection(use_old_db)
        cursor = conn.cursor()

        now = datetime.now()

        # 1小時雨量
        one_hour_ago = now - timedelta(hours=1)
        cursor.execute("""
            SELECT SUM(CAST(value AS FLOAT))
            FROM sensor_record
            WHERE sensor_id = ? AND trigger_time >= ?
            AND CAST(value AS FLOAT) <= 100
        """, sensor_id, one_hour_ago)
        rain_1hr = cursor.fetchone()[0] or 0

        # 3小時雨量
        three_hours_ago = now - timedelta(hours=3)
        cursor.execute("""
            SELECT SUM(CAST(value AS FLOAT))
            FROM sensor_record
            WHERE sensor_id = ? AND trigger_time >= ?
            AND CAST(value AS FLOAT) <= 100
        """, sensor_id, three_hours_ago)
        rain_3hr = cursor.fetchone()[0] or 0

        # 24小時雨量
        one_day_ago = now - timedelta(hours=24)
        cursor.execute("""
            SELECT SUM(CAST(value AS FLOAT))
            FROM sensor_record
            WHERE sensor_id = ? AND trigger_time >= ?
            AND CAST(value AS FLOAT) <= 100
        """, sensor_id, one_day_ago)
        rain_24hr = cursor.fetchone()[0] or 0

        conn.close()
        return rain_1hr, rain_3hr, rain_24hr

    except Exception as e:
        print(f"✗ 取得感測器 {sensor_id} 雨量數據失敗: {e}")
        return 0, 0, 0


def determine_cms_level(rain_1hr, rain_3hr, rain_24hr):
    """根據雨量數據判斷CMS級別（1-4）"""
    # Level 4: 24HR≥500mm（最高優先）
    if rain_24hr >= CMS_LEVEL4_DAILY:
        return 4

    # Level 3: 3HR≥200mm 或 24HR≥350mm
    if rain_3hr >= CMS_LEVEL3_3HR or rain_24hr >= CMS_LEVEL3_DAILY:
        return 3

    # Level 2: 3HR≥100mm 或 24HR≥200mm
    if rain_3hr >= CMS_LEVEL2_3HR or rain_24hr >= CMS_LEVEL2_DAILY:
        return 2

    # Level 1: 時雨量≥40mm 或 24HR≥80mm
    if rain_1hr >= CMS_LEVEL1_HOURLY or rain_24hr >= CMS_LEVEL1_DAILY:
        return 1

    return 0


def trigger_cms_for_rain(level):
    """
    觸發CMS顯示雨量警報
    直接更新 cms_status 表

    Args:
        level: 警戒層級 (1-4)
            1: 時雨量≥40mm 或 24HR≥80mm
            2: 3HR≥100mm 或 24HR≥200mm
            3: 3HR≥200mm 或 24HR≥350mm
            4: 24HR≥500mm
    """
    try:
        conn = get_db_connection()
        cursor = conn.cursor()

        # 決定圖片和訊息
        level_config = {
            1: ('cms-images/alert/rain_level1.bmp', '雨量警報 Level 1'),
            2: ('cms-images/alert/rain_level2.bmp', '雨量警報 Level 2'),
            3: ('cms-images/alert/rain_level3.bmp', '雨量警報 Level 3'),
            4: ('cms-images/alert/rain_level4.bmp', '雨量警報 Level 4')
        }

        if level not in level_config:
            print(f"⚠️ 無效的CMS level: {level}")
            return

        image_path, trigger_value = level_config[level]

        # 直接更新 cms_status（所有CMS統一更新）
        cursor.execute("""
            UPDATE cms_status
            SET current_mode = 'alert',
                display_type = 'image',
                image_path = ?,
                trigger_source = 'rain_alert',
                trigger_value = ?,
                trigger_time = GETDATE(),
                last_updated = GETDATE()
            WHERE is_active = 1
        """, image_path, trigger_value)

        rows_updated = cursor.rowcount
        conn.commit()
        print(f"✓ 已觸發CMS雨量警報 Level {level}，更新 {rows_updated} 座CMS")

    except Exception as e:
        print(f"✗ 觸發CMS警報失敗: {e}")
    finally:
        if conn:
            conn.close()


def monitor_loop():
    """監控主迴圈"""
    print("=" * 60)
    print("雲林古坑邊坡監測系統 - 即時警報啟動")
    print(f"啟動時間: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)
    print()

    # 載入警報狀態
    alert_state = load_alert_state()
    last_reset_date = datetime.now().date()

    # 取得要監控的感測器
    sensors = get_sensors_to_monitor()
    print(f"監控中: {len(sensors)} 個感測器 (傾斜儀 + 雨量筒)")
    print()

    loop_count = 0
    last_cms_check_time = datetime.now()
    cms_check_interval_minutes = 10  # CMS檢查間隔（分鐘）

    try:
        while True:
            loop_count += 1
            current_time = datetime.now()

            # 每天凌晨重置狀態
            if current_time.date() != last_reset_date:
                print(f"\n[{current_time}] 新的一天，重置警報狀態")
                alert_state = reset_daily_state(alert_state)
                save_alert_state(alert_state)
                last_reset_date = current_time.date()

            # 顯示監控狀態
            print(
                f"[{current_time.strftime('%H:%M:%S')}] 監控中... (已通報: {len(alert_state)} 個感測器)")

            # 檢查每個感測器
            for sensor in sensors:
                # 先檢查是否斷線（3小時無資料）
                is_offline, last_time = check_sensor_offline(sensor)

                if is_offline:
                    # 感測器斷線
                    if should_send_alert(sensor['id'], -1, alert_state):
                        # 發送斷線警報
                        message = format_offline_alert_message(
                            sensor, last_time)
                        print(f"\n{'='*60}")
                        print(f"⚠️ 發送斷線警報: {sensor['name']}")
                        if last_time:
                            print(f"   最後資料: {last_time}")
                        else:
                            print(f"   無歷史資料")
                        print(f"{'='*60}\n")

                        # 發送到 Discord 和 LINE
                        send_to_discord(message)
                        send_to_line(message)

                        # 更新狀態（level=-1 代表斷線）
                        alert_state = update_alert_state(
                            sensor['id'], -1, alert_state)
                        save_alert_state(alert_state)
                else:
                    # 感測器正常，檢查數值
                    trigger_time, value, level = check_recent_data(
                        sensor, seconds=30)

                    # 如果有新資料且達到管理值
                    if trigger_time and level > 0:
                        if should_send_alert(sensor['id'], level, alert_state):
                            # 發送警報（不觸發CMS）
                            message = format_alert_message(
                                sensor, value, level)
                            print(f"\n{'='*60}")
                            print(f"⚠️ 發送通報: {sensor['name']}")
                            print(f"   時間: {trigger_time}")
                            print(f"   數值: {value}")
                            print(f"   等級: Level {level}")
                            print(f"{'='*60}\n")

                            # 發送到 Discord 和 LINE
                            send_to_discord(message)
                            send_to_line(message)

                            # 更新狀態
                            alert_state = update_alert_state(
                                sensor['id'], level, alert_state)
                            save_alert_state(alert_state)

            # 每10分鐘檢查CMS觸發條件（整合所有雨量數據源）
            if (current_time - last_cms_check_time).total_seconds() >= cms_check_interval_minutes * 60:
                print(f"\n{'='*60}")
                print(f"檢查CMS觸發條件...")
                print(f"{'='*60}")

                max_cms_level = 0
                triggered_sources = []

                # 1. 檢查所有系統雨量筒
                rain_sensors = [s for s in sensors if s['type'] == 'rain']
                for sensor in rain_sensors:
                    rain_1hr, rain_3hr, rain_24hr = get_sensor_rainfall_data(
                        sensor['id'])
                    cms_level = determine_cms_level(
                        rain_1hr, rain_3hr, rain_24hr)

                    if cms_level > 0:
                        print(
                            f"  {sensor['name']}: 1hr={rain_1hr:.1f}mm, 3hr={rain_3hr:.1f}mm, 24hr={rain_24hr:.1f}mm → Level {cms_level}")
                        if cms_level > max_cms_level:
                            max_cms_level = cms_level
                            triggered_sources = [sensor['name']]
                        elif cms_level == max_cms_level:
                            triggered_sources.append(sensor['name'])

                # 2. 檢查氣象局古坑站
                obs_time, cwa_1hr, cwa_3hr, cwa_24hr = check_cwa_rainfall()
                if obs_time:
                    cwa_cms_level = determine_cms_level(
                        cwa_1hr, cwa_3hr, cwa_24hr)
                    if cwa_cms_level > 0:
                        print(
                            f"  {CWA_STATION_NAME}: 1hr={cwa_1hr:.1f}mm, 3hr={cwa_3hr:.1f}mm, 24hr={cwa_24hr:.1f}mm → Level {cwa_cms_level}")
                        if cwa_cms_level > max_cms_level:
                            max_cms_level = cwa_cms_level
                            triggered_sources = [CWA_STATION_NAME]
                        elif cwa_cms_level == max_cms_level:
                            triggered_sources.append(CWA_STATION_NAME)

                # 3. 觸發CMS（取最高級別）
                if max_cms_level > 0:
                    print(f"\n✓ 觸發來源: {', '.join(triggered_sources)}")
                    print(f"✓ CMS警報級別: Level {max_cms_level}")
                    trigger_cms_for_rain(max_cms_level)
                else:
                    print(f"  所有雨量數據未達CMS觸發條件")

                print(f"{'='*60}\n")
                last_cms_check_time = current_time

            # 等待10分鐘
            time.sleep(600)

    except KeyboardInterrupt:
        print("\n\n程式已停止")
    except Exception as e:
        print(f"\n錯誤: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    monitor_loop()
