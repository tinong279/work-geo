import requests
import xml.etree.ElementTree as ET
import json
from datetime import datetime

# 設定
XML_URL = "https://tisvcloud.freeway.gov.tw/history/motc20/LiveTraffic.xml"
NS = {"t": "http://traffic.transportdata.tw/standard/traffic/schema/"}
CONFIG_FILE = "miaoli-01.json"
OUTPUT_FILE = "miaoli-01-live-traffic.json"


def update_traffic():
    final_results = []

    # A. 讀取座標檔 ID
    try:
        with open(CONFIG_FILE, "r", encoding="utf-8") as f:
            coords_data = json.load(f)
            # 必須使用完整的 SectionID (例如 000101100)
            target_ids = {str(item[0]) for item in coords_data}
    except Exception as e:
        print(f"讀取座標檔失敗: {e}")
        return

    # --- 第一軌：從高公局抓取國道 ---
    print("正在抓取國道路況...")
    try:
        res = requests.get(XML_URL, timeout=20)
        res.encoding = "utf-8"
        root = ET.fromstring(res.content)

        for item in root.findall(".//t:LiveTraffic", NS):
            sid = item.find("t:SectionID", NS).text
            speed_elem = item.find("t:TravelSpeed", NS)

            if speed_elem is not None and sid in target_ids:
                speed = int(speed_elem.text)

                # --- 核心門檻修正：國道標準 ---
                if speed >= 70:
                    level = "1"  # 綠色
                elif speed >= 30:
                    level = "2"  # 黃色
                elif speed > 0:
                    level = "3"  # 紅色
                else:
                    level = "1"  # 避免 0 或異常值導致全紅，預設綠或灰

                # 重要：直接用原始 sid，不要縮短成 "0001"！
                final_results.append(
                    [
                        sid,
                        {
                            "SectionID": sid,
                            "TravelSpeed": str(speed),
                            "CongestionLevel": level,
                            "DataCollectTime2": datetime.now().strftime(
                                "%Y-%m-%d %H:%M:%S"
                            ),
                        },
                    ]
                )
    except Exception as e:
        print(f"國道抓取異常: {e}")

    # (南庄部分先維持預設，如你所說之後再處理)
    nanzhuang_ids = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"]
    for nid in nanzhuang_ids:
        final_results.append(
            [
                nid,
                {
                    "SectionID": nid,
                    "TravelSpeed": "40",
                    "CongestionLevel": "1",
                    "DataCollectTime2": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                },
            ]
        )

    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        json.dump(final_results, f, ensure_ascii=False)

    print(f"✅ 更新完成！當前國道時速門檻已調優。")


if __name__ == "__main__":
    update_traffic()
