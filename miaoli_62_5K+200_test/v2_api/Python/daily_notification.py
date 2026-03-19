import subprocess
import os
from datetime import datetime

# 專案目錄
project_dir = r"C:\xampp\htdocs\default\public\miaoli_62_5K_200"

# 切換目錄
os.chdir(project_dir)

# PHP 執行檔完整路徑
php_path = r"C:\php-8.4.5-nts-Win32-vs17-x64\php.exe"

# 執行 artisan schedule:run
with open("schedule_log.txt", "a", encoding="utf-8") as logfile:
    # 記錄執行時間
    logfile.write(f"\n=== {datetime.now()} ===\n")
    
    result = subprocess.run(
        [php_path, "artisan", "schedule:run", "--verbose"],  # 加上 --verbose
        stdout=logfile,
        stderr=logfile,  # 分別記錄錯誤
        shell=False
    )
    
    # 記錄返回碼
    logfile.write(f"Return code: {result.returncode}\n")