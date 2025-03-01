import os
import tkinter as tk
from tkinter import filedialog, messagebox
from PIL import Image, ImageOps

def resize_and_compress(image_path, max_size_kb=299):
    try:
        # 讀取圖片
        img = Image.open(image_path).convert("RGB")  # 確保可以轉成 JPG
        
        # 取得原始尺寸
        width, height = img.size

        # 計算正方形尺寸（取較大邊）
        new_size = max(width, height)

        # 建立白色背景的正方形
        square_img = Image.new("RGB", (new_size, new_size), (255, 255, 255))
        square_img.paste(img, ((new_size - width) // 2, (new_size - height) // 2))

        # 縮放至最大 1000x1000，但不會放大
        if new_size > 1000:
            square_img = square_img.resize((1000, 1000), Image.LANCZOS)

        # 取得原始圖片所在資料夾
        img_dir = os.path.dirname(image_path)

        # 建立 "S" 資料夾
        output_folder = os.path.join(img_dir, "S")
        os.makedirs(output_folder, exist_ok=True)

        # 設定輸出路徑
        output_path = os.path.join(output_folder, os.path.splitext(os.path.basename(image_path))[0] + ".jpg")

        # 壓縮圖片，確保不超過 299KB
        quality = 95
        while True:
            square_img.save(output_path, "JPEG", quality=quality)
            if os.path.getsize(output_path) <= max_size_kb * 1024 or quality <= 10:
                break
            quality -= 5  # 遞減品質直到符合條件
        
        return output_path
    except Exception as e:
        return f"❌ 發生錯誤：{str(e)}"

def select_images():
    file_paths = filedialog.askopenfilenames(title="選擇圖片", filetypes=[("Image files", "*.jpg;*.jpeg;*.png")])
    if not file_paths:
        return
    
    success_count = 0
    for path in file_paths:
        result = resize_and_compress(path)
        if os.path.exists(result):
            success_count += 1
    
    messagebox.showinfo("處理完成", f"✅ 成功轉換 {success_count} 張圖片！\n圖片存放在原始路徑的 S 資料夾！")

# 建立 GUI 介面
root = tk.Tk()
root.title("圖片轉換器")
root.geometry("400x200")

btn_select = tk.Button(root, text="選擇圖片並轉換", command=select_images, font=("Arial", 14))
btn_select.pack(expand=True)

root.mainloop()
