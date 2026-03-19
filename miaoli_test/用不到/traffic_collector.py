# -*- coding: utf-8 -*-
import requests
import xml.etree.ElementTree as ET
import json
import time
from datetime import datetime

# 1. 配置與命名空間設定
XML_URL = "https://tisvcloud.freeway.gov.tw/history/motc20/LiveTraffic.xml"
# 這個 NS 字典非常重要，它是對應掃描結果中的網址
NS = {"t": "http://traffic.transportdata.tw/standard/traffic/schema/"}
OUTPUT_FILE = "miaoli-01-live-traffic.json"
CHECK_INTERVAL = 60


def fetch_and_parse():
    print(f"[{datetime.now().strftime('%H:%M:%S')}] 抓取中...")

    try:
        res = requests.get(XML_URL, timeout=30)
        res.encoding = "utf-8"
        if res.status_code != 200:
            return

        root = ET.fromstring(res.content)
        final_results = []

        # 使用命名空間搜尋所有的 <LiveTraffic> 區塊
        for item in root.findall(".//t:LiveTraffic", NS):
            # 在子節點中尋找資料，並使用 .text 取得標籤內的文字
            sid = item.find("t:SectionID", NS).text
            speed = item.find("t:TravelSpeed", NS).text
            travel_time = item.find("t:TravelTime", NS).text

            if sid and speed:
                speed_val = int(speed)

                # 判斷等級 (跟原本邏輯一致)
                level = "1" if speed_val > 31 else ("2" if speed_val >= 15 else "3")

                route_info = {
                    "SectionID": sid,
                    "TravelTime": travel_time,
                    "TravelSpeed": str(speed_val),
                    "CongestionLevel": level,
                    "DataCollectTime2": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                }

                # 存成原本地圖要求的格式：[ID, {內容}]
                final_results.append([int(sid), route_info])

        # 存檔
        with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
            json.dump(final_results, f, ensure_ascii=False)

        print(f"更新成功！共處理 {len(final_results)} 條路段。")

    except Exception as e:
        print(f"解析發生錯誤: {e}")


if __name__ == "__main__":
    while True:
        fetch_and_parse()
        time.sleep(CHECK_INTERVAL)
