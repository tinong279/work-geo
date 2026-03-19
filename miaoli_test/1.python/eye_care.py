import time
import tkinter as tk
from tkinter import messagebox


def show_alert(message):
    root = tk.Tk()
    root.withdraw()
    messagebox.showinfo("護眼提醒", message)
    root.destroy()


if __name__ == "__main__":
    print("護眼小助手已啟動...")

    start_time = 0

    while True:
        # 每隔 5 秒檢查一次（模擬原本的 5 秒練習）
        time.sleep(5)
        start_time += 5  # 累加經過的時間

        # 檢查是否過了 20 分鐘（這裡用 5 秒代表）
        if start_time % 5 == 0:
            show_alert("已經 20 分鐘了！去遠眺吧！")

        # 檢查是否過了 40 分鐘（這裡用 10 秒代表）
        if start_time % 10 == 0:
            show_alert("已經 40 分鐘了！該起身走走囉！")
