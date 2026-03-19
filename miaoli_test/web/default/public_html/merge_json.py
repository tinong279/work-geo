import json

# 合併兩個檔案freeway_coords_cleaned.json miaoli-01.json


# 1. 定義檔案路徑
file_miaoli = "miaoli-01.json"
file_freeway = "freeway_coords_cleaned.json"
output_file = "miaoli-01.json"  # 合併後直接覆蓋原本的，地圖程式就不用改路徑

try:
    # 2. 讀取苗栗市區道路資料
    with open(file_miaoli, "r", encoding="utf-8") as f:
        data_miaoli = json.load(f)

    # 3. 讀取國道座標資料
    with open(file_freeway, "r", encoding="utf-8") as f:
        data_freeway = json.load(f)

    # 4. 合併列表 (使用 + 號即可將兩個 List 接起來)
    # Python 的 List 合併非常直覺： [1, 2] + [3, 4] = [1, 2, 3, 4]
    combined_data = data_miaoli + data_freeway

    # 5. 寫回檔案
    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(combined_data, f, ensure_ascii=False)

    print(f"✅ 合併成功！")
    print(f"📍 原有路段：{len(data_miaoli)} 條")
    print(f"🛣️ 新增國道：{len(data_freeway)} 條")
    print(f"📊 總計路段：{len(combined_data)} 條，已存入 {output_file}")

except Exception as e:
    print(f"❌ 合併時發生錯誤: {e}")
