import openpyxl
import json

try:
    with open('D:\\laragon\\www\\bagisto-2.4\\excel_data_with_images.json', 'r', encoding='utf-8') as f:
        data = json.load(f)

    if not data:
        print("No data")
        exit()

    wb = openpyxl.Workbook()
    ws = wb.active
    
    headers = list(data[0].keys())
    ws.append(headers)
    
    for row in data:
        ws.append([row.get(h) for h in headers])

    output_path = 'D:\\laragon\\www\\bagisto-2.4\\عروض_مع_الصور.xlsx'
    wb.save(output_path)
    print("Success")
except Exception as e:
    print("Error:", str(e))
