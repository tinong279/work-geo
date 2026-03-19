import time
from pathlib import Path

# 設定資料夾清單
folders = [
    r"D:\Geonerve\RDWS-1\image_web\ch1",
    r"D:\Geonerve\RDWS-1\image_web\ch2"
]

def keep_latest_5_images(folder, use_mtime=False):
    path = Path(folder)
    if not path.exists():
        print(f"資料夾不存在：{folder}")
        return

    # 選取所有 JPG 檔案
    image_files = list(path.glob("*.jpg"))

    # 根據檔案名稱或修改時間排序
    if use_mtime:
        image_files.sort(key=lambda x: x.stat().st_mtime, reverse=True)
    else:
        image_files.sort(key=lambda x: x.name, reverse=True)

    # 保留最新的5個
    files_to_delete = image_files[5:]

    # 開始刪除
    for file in files_to_delete:
        try:
            file.unlink()
            print(f"刪除：{file}")
        except Exception as e:
            print(f"無法刪除 {file}：{e}")

for folder in folders:
    keep_latest_5_images(folder, use_mtime=False)  # 設成 True 則改用修改時間排序

# 💡 每 60 秒執行一次
while True:
    print("執行攝影機圖片清理...")
    print(folders)
    for folder in folders:
        keep_latest_5_images(folder, use_mtime=False)
    print("等待 60 秒後再執行...\n")
    time.sleep(60)