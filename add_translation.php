<?php

$locales = ['ar', 'bn', 'ca', 'de', 'en', 'es', 'fa', 'fr', 'he', 'hi_IN', 'id', 'it', 'ja', 'nl', 'pl', 'pt_BR', 'ru', 'sin', 'tr', 'uk', 'zh_CN'];

foreach ($locales as $locale) {
    $path = __DIR__ . "/packages/Webkul/Admin/src/Resources/lang/{$locale}/app.php";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $value = 'Report Export';
        if ($locale == 'ar') {
            $value = 'تصدير التقرير';
        }
        
        // Find 'create-btn' =>
        $search = "'create-btn' =>";
        
        if (strpos($content, $search) !== false) {
            $replace = "'report-export' => '{$value}',\n                'create-btn' =>";
            $content = str_replace($search, $replace, $content);
            file_put_contents($path, $content);
            echo "Updated {$locale}\n";
        } else {
            echo "Could not find create-btn in {$locale}\n";
        }
    }
}
