#!/usr/bin/env python3
import time

# from pymodbus.client.sync import ModbusTcpClient
from pymodbus.client import ModbusTcpClient
import requests
import threading
import requests
import json
import os
from datetime import datetime

# Modbus連線設定
# 裝置 IP
# Modbus TCP 通訊埠
MODBUS_HOST = "192.168.15.54"
# MODBUS_PORT = 502
MODBUS_PORT = 502
# 模組 ID
UNIT_ID = 1

# MySQL設定
# 更改實體位置
MYSQL_HOST = "210.71.231.250"
# MYSQL_HOST = "127.0.0.1"
MYSQL_PORT = 3306
MYSQL_USER = "timmy"
MYSQL_PASSWORD = "0000"
MYSQL_DB = "miaoli_62_5k+200"

# DI0計數器設定
# DEC為30034~30035 => offset=33
DI1_COUNTER_ADDRESS = 33
# 32位元，讀取2個16-bit暫存器
DI1_COUNTER_COUNT = 2


# 讀取 JSON 設定
def load_config():
    config_path = os.path.join(
        os.path.dirname(__file__), "miaoli_62_5K_200_setting.json"
    )
    with open(config_path, "r", encoding="utf-8") as f:
        return json.load(f)


# 全域設定變數
CONFIG = load_config()


def main():
    # 建立 Modbus TCP 客戶端
    modbus_client = ModbusTcpClient(MODBUS_HOST, port=MODBUS_PORT)
    if not modbus_client.connect():
        print("無法連線到Modbus伺服器")
        return
    print("成功連線到Modbus伺服器")

    try:
        while True:
            # Modbus讀取DI狀態(市電)
            process_di_status(modbus_client)

            # Modbus讀取 AI0 狀態 (電池電壓)
            process_ai_status(modbus_client)

            # Modbus讀取牌面狀態
            process_cms_status(modbus_client)

            # 每次循環延遲10秒
            time.sleep(10)
    except KeyboardInterrupt:
        print("\n停止讀取。")
    finally:
        modbus_client.close()
        print("已關閉Modbus與MySQL連線。")


# Modbus讀取 DI0 狀態 (市電)
def process_di_status(modbus_client):
    # 讀取離散輸入 (DI0)
    result = modbus_client.read_discrete_inputs(address=0, count=2, slave=UNIT_ID)
    if result.isError():
        print("讀取 DI 狀態錯誤:", result)
        return
    di0 = result.bits[0]
    status0 = "ON" if di0 else "OFF"
    current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    # 輸出狀態
    print(f"{current_time}, DI0 = {status0}")

    # 如果市電狀態為 OFF，傳送 POST 到中華雲端伺服器
    if status0 == "OFF":
        url = "http://210.71.231.250/v2_api/abnormal_data_receiver.php"
        # url = "http://127.0.0.1:8000/abnormal-data"
        payload = {
            "device": "utilityPower",
            "utilityPowerStatus": 0,
        }
        try:
            # verify=False 用於跳過 SSL 驗證
            response = requests.post(url, data=payload)
            if response.status_code == 200:
                print(f"成功傳送異常資料: {response.json()}")
            else:
                print(
                    f"傳送異常資料失敗，狀態碼: {response.status_code}, 回應: {response.text}"
                )
        except requests.RequestException as e:
            print(f"傳送異常資料時發生錯誤: {e}")

    return status0, current_time


# Modbus讀取 AI0 狀態 (電池電壓)
def process_ai_status(modbus_client):
    result = modbus_client.read_input_registers(address=0, count=1, slave=UNIT_ID)
    if result.isError():
        print("讀取 AI 狀態錯誤:", result)
        return

    voltage_raw = result.registers[0]
    voltage = voltage_raw / 1000  # 假設單位是 0.01V

    # voltage_raw = result.registers[0]
    # # 轉換為電流 (mA)，根據 Modbus 0~20000 對應 4~20mA
    # current_ma = voltage_raw * 20.0 / 20000

    # # 轉換為電壓，例如 4~20mA 對應 0~15V（你可以改成 0~10 或 0~30）
    # voltage = (current_ma - 4) * (15 / 16)

    current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    # 輸出狀態
    print(f"{current_time}, AI0（電池電壓）= {voltage}V")

    # 傳送 POST 到中華雲端伺服器
    url = "http://210.71.231.250/v2_api/abnormal_data_receiver.php"
    payload = {"device": "battery_voltage", "batteryVoltageStatus": voltage}
    try:
        # verify=False 用於跳過 SSL 驗證
        response = requests.post(url, data=payload)
        if response.status_code == 200:
            print(f"成功傳送異常資料: {response.json()}")
        else:
            print(
                f"傳送異常資料失敗，狀態碼: {response.status_code}, 回應: {response.text}"
            )
    except requests.RequestException as e:
        print(f"傳送異常資料時發生錯誤: {e}")

    return voltage, current_time


# 牌面是否已經啟動倒數（避免重複觸發）
shutdown_timer_started = False


# Modbus讀取牌面狀態
def process_cms_status(modbus_client):
    global shutdown_timer_started

    try:
        # 讀取線圈（Function Code 1）
        result = modbus_client.read_coils(address=0, count=1, slave=UNIT_ID)
        if result.isError():
            print("讀取牌面狀態錯誤:", result)
            return

        coil_status = result.bits[0]
        status = "開啟" if coil_status else "關閉"
        current_time = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"{current_time}, 牌面狀態: {status}")

        if coil_status:
            if not shutdown_timer_started:
                shutdown_timer_started = True
                threading.Thread(
                    target=auto_shutdown_panel, args=(modbus_client,), daemon=True
                ).start()
        else:
            # 如果牌面被手動關閉，清除倒數狀態
            if shutdown_timer_started:
                print("牌面已關閉，取消倒數")
                shutdown_timer_started = False

        return current_time

    except Exception as e:
        print(f"查詢牌面狀態時發生錯誤: {e}")
        return None


# 關閉牌面控制
def auto_shutdown_panel(modbus_client):
    # 從設定中讀取分鐘數，預設為 60 分鐘
    shutdown_minutes = CONFIG.get("cms", {}).get("cms_off_minute", 60)
    shutdown_seconds = shutdown_minutes * 60

    print(f"偵測到牌面開啟，將在 {shutdown_minutes} 分鐘後自動關閉...")
    time.sleep(shutdown_seconds)

    try:
        # 使用 write_coil 將 Coil #0 設為 False（關閉）
        result = modbus_client.write_coil(address=0, value=False, slave=UNIT_ID)
        if result.isError():
            print("自動關閉牌面失敗:", result)
        else:
            print("已自動透過 Modbus 關閉牌面")
    except Exception as e:
        print("自動關閉牌面時發生錯誤:", e)


if __name__ == "__main__":
    main()
