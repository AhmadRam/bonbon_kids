<?php

$locales = ['ar', 'bn', 'ca', 'de', 'en', 'es', 'fa', 'fr', 'he', 'hi_IN', 'id', 'it', 'ja', 'nl', 'pl', 'pt_BR', 'ru', 'sin', 'tr', 'uk', 'zh_CN'];

foreach ($locales as $locale) {
    $path = __DIR__ . '/packages/Webkul/Shop/src/Resources/lang/' . $locale . '/app.php';
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $title = 'Free Gift Wrapping!';
        $desc = 'To choose a wrapping color (blue or pink), please contact us via WhatsApp.';
        $btn = 'Contact via WhatsApp';
        $msg = 'Hello, I would like to choose the wrapping color for my order. My preferred color is:';

        if ($locale == 'ar') {
            $title = 'يوجد تغليف مجاني!';
            $desc = 'لاختيار لون التغليف (أزرق أو زهري)، يرجى التواصل معنا عبر الواتساب.';
            $btn = 'تواصل عبر واتساب';
            $msg = 'مرحباً، أود اختيار لون التغليف لطلبي. اللون المفضل هو:';
        }
        
        $replace = "'free-gift-wrapping'           => '{$title}',\n                'gift-wrapping-desc'           => '{$desc}',\n                'contact-whatsapp'             => '{$btn}',\n                'whatsapp-msg'                 => '{$msg}',\n                'place-order'";
        
        // Find 'place-order' in the content
        if (strpos($content, "'free-gift-wrapping'") === false) {
            // Replace the exact place order key under summary
            $content = preg_replace("/'place-order'\s*=>/", $replace . " =>", $content);
            file_put_contents($path, $content);
            echo "Updated {$locale}\n";
        } else {
            echo "Already updated {$locale}\n";
        }
    }
}
