x = 10
y = 20
z = x + y
print(f"結果是: {z}")  # 這是最後一次用 print，等一下我們要用偵錯看它


def calculate_disaster_risk(level):
    # 假設這是公司程式碼中複雜的計算邏輯
    result = level * 100
    return result


risk_level = 5
# 當黃色高亮停在下面這一行時：
final_score = calculate_disaster_risk(risk_level)
print(final_score)
