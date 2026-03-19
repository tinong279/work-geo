import requests
import xml.etree.ElementTree as ET

url = "https://tisvcloud.freeway.gov.tw/history/motc20/LiveTraffic.xml"
print("--- 正在抓取高公局大檔案，請稍候... ---")

try:
    # 增加 timeout 到 30 秒，因為這個檔案很大
    res = requests.get(url, timeout=30)
    res.encoding = "utf-8"

    if res.status_code == 200:
        print(f"下載成功！檔案大小: {len(res.content) / 1024 / 1024:.2f} MB")

        root = ET.fromstring(res.content)
        print(f"根節點標籤是: <{root.tag}>")

        # 試著找前幾個子節點，看看結構長怎樣
        print("\n--- 掃描 XML 結構 ---")
        found_any = False

        # 使用 iter() 找出「所有」標籤，看看裡面到底有沒有 Info
        for count, elem in enumerate(root.iter()):
            if count > 20:  # 只看前 20 個標籤
                break
            # 印出標籤名與它的屬性
            print(f"標籤: <{elem.tag}> | 屬性: {list(elem.attrib.keys())}")

            if "sectionid" in elem.attrib:
                print(
                    f"  >>> 找到資料了！ ID: {elem.get('sectionid')} | 時速: {elem.get('travelspeed')}"
                )
                found_any = True

        if not found_any:
            print(
                "\n[警告] 在前 20 個節點中沒看到 sectionid，這份 XML 的格式可能與預期不同。"
            )

    else:
        print(f"連線失敗，錯誤碼: {res.status_code}")

except Exception as e:
    print(f"發生錯誤: {e}")
