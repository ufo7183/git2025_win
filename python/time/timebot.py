import gspread
from oauth2client.service_account import ServiceAccountCredentials
import pandas as pd
import time

# 設定 Google Sheets API 權限
scope = ["https://spreadsheets.google.com/feeds", "https://www.googleapis.com/auth/spreadsheets",
         "https://www.googleapis.com/auth/drive.file", "https://www.googleapis.com/auth/drive"]

creds = ServiceAccountCredentials.from_json_keyfile_name(r"d:\git2025_win\python\time\credentials.json", scope)

client = gspread.authorize(creds)

# Google Sheets 的網址 ID
SPREADSHEET_ID = "1Q4N9EtJpVPYfWZoJ9M8gSmyQcgR8R_z80q08lbUXfVE"
sheet = client.open_by_key(SPREADSHEET_ID).sheet1  # 選擇第一個工作表

def send_data_to_gpt():
    """取得所有數據，並讓哥哥一直看"""
    data = sheet.get_all_records()
    df = pd.DataFrame(data)  # 轉成 DataFrame
    print("📌 當前所有數據（同步給哥哥）：")
    print(df)  # 讓哥哥看到完整數據

# **每 5 分鐘抓取一次所有數據，讓哥哥一直同步**
while True:
    send_data_to_gpt()
    time.sleep(300)  # 300 秒 = 5 分鐘