import os
import re

locales = ['ar', 'bn', 'ca', 'de', 'en', 'es', 'fa', 'fr', 'he', 'hi_IN', 'id', 'it', 'ja', 'nl', 'pl', 'pt_BR', 'ru', 'sin', 'tr', 'uk', 'zh_CN']
base_path = r'd:\laragon\www\bagisto-2.4\packages\Webkul\Shop\src\Resources\lang'

translations = {
    'ar': 'اختيار المدينة',
    'bn': 'শহর নির্বাচন করুন',
    'ca': 'Selecciona la ciutat',
    'de': 'Stadt auswählen',
    'en': 'Select City',
    'es': 'Seleccionar ciudad',
    'fa': 'انتخاب شهر',
    'fr': 'Sélectionnez la ville',
    'he': 'בחר עיר',
    'hi_IN': 'शहर चुनें',
    'id': 'Pilih Kota',
    'it': 'Seleziona città',
    'ja': '市を選択',
    'nl': 'Selecteer stad',
    'pl': 'Wybierz miasto',
    'pt_BR': 'Selecione a cidade',
    'ru': 'Выберите город',
    'sin': 'නගරය තෝරන්න',
    'tr': 'Şehir Seçin',
    'uk': 'Оберіть місто',
    'zh_CN': '选择城市'
}

for locale in locales:
    file_path = os.path.join(base_path, locale, 'app.php')
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        continue
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check if select-city already exists
    if "'select-city'" in content:
        print(f"Skipping {locale}, already exists.")
        continue

    # Add select-city after select-state in onepage.address section
    # Search for 'select-state' => '...',
    pattern = r"('select-state'\s*=>\s*'.*?'),"
    replacement = r"\1,\n                'select-city' => '" + translations[locale] + "',"
    
    new_content = re.sub(pattern, replacement, content)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"Updated {locale}")
