import json
import re

# 這支程式會讀取你的 freeway_shape_raw.json，自動解析那些複雜的字串，並產出一份可以直接合併到 miaoli-01.json 的檔案。

# 1. 讀取原始檔案
raw_file = "freeway_shape_raw.json"
output_file = "freeway_coords_cleaned.json"

try:
    with open(raw_file, "r", encoding="utf-8") as f:
        data = json.load(f)

    cleaned_data = []

    # 2. 開始解析每個路段
    # 根據你的截圖，資料放在 "SectionShapes" 鍵值下
    for item in data.get("SectionShapes", []):
        sid = item["SectionID"]
        wkt_string = item["Geometry"]

        # 使用正規表達式把 LINESTRING(...) 括號內的數字挖出來
        # 找到所有像 "121.123 25.123" 這樣的組合
        coords_str = re.findall(r"[-+]?\d*\.\d+|\d+", wkt_string)

        # 轉換格式：[經度1, 緯度1, 經度2, 緯度2...] -> [[緯度1, 經度1], [緯度2, 經度2]...]
        path = []
        for i in range(0, len(coords_str), 2):
            lng = float(coords_str[i])
            lat = float(coords_str[i + 1])
            path.append([lat, lng])  # 這裡對調順序，符合地圖需求

        # 存成跟你的 miaoli-01.json 一樣的格式: [ID, 座標陣列]
        cleaned_data.append([sid, path])

    # 3. 輸出結果
    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(cleaned_data, f, ensure_ascii=False)

    print(f"✅ 轉換完成！共處理 {len(cleaned_data)} 條國道路段。")
    print(f"📂 乾淨的座標已存入: {output_file}")

except Exception as e:
    print(f"❌ 發生錯誤: {e}")
