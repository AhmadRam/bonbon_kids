<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('fix-footer-links', function () {
    $translations = DB::table('theme_customization_translations')
        ->where('theme_customization_id', 6)
        ->get();

    foreach ($translations as $translation) {
        $options = json_decode($translation->options, true);
        
        if (isset($options['column_1'])) {
            foreach ($options['column_1'] as &$link) {
                // Replace everything before /page/ or /contact-us
                if (preg_match('#^(https?://[^/]+)?(/.*)$#', $link['url'], $matches)) {
                    $link['url'] = $matches[2]; // keep only the relative path
                }
            }
        }
        
        if (isset($options['column_2'])) {
            foreach ($options['column_2'] as &$link) {
                if (preg_match('#^(https?://[^/]+)?(/.*)$#', $link['url'], $matches)) {
                    $link['url'] = $matches[2];
                }
            }
        }

        DB::table('theme_customization_translations')
            ->where('id', $translation->id)
            ->update(['options' => json_encode($options, JSON_UNESCAPED_UNICODE)]);
    }

    $this->info('Footer links have been updated to use relative paths.');
});

Artisan::command('bagisto:sitemap:generate', function () {
    \Webkul\Shop\Http\Controllers\SitemapController::clearCache();

    $controller = app(\Webkul\Shop\Http\Controllers\SitemapController::class);

    // Warm up the caches
    $controller->index();
    $controller->categories();
    $controller->products();
    $controller->pages();

    $this->info('Sitemap generated and cached successfully.');
})->describe('Generate and cache XML sitemaps');

Artisan::command('fix-theme-banners-responsive', function () {
    $customizations = DB::table('theme_customization_translations')
        ->whereIn('theme_customization_id', [2, 15])
        ->get();

    foreach ($customizations as $c) {
        $options = json_decode($c->options, true);
        if (isset($options['html'])) {
            $options['html'] = preg_replace_callback(
                '/<img\s+src="([^"]+)"([^>]*)>/i',
                function ($matches) {
                    $src = $matches[1];
                    $rest = $matches[2];
                    if (str_contains($rest, 'srcset=')) {
                        return $matches[0];
                    }
                    $medium = str_replace('storage/', 'cache/medium/', $src);

                    return '<img src="'.$src.'" srcset="'.$medium.' 300w, '.$src.' 600w" sizes="(max-width: 768px) 274px, 600px" loading="lazy"'.$rest.'>';
                },
                $options['html']
            );

            DB::table('theme_customization_translations')
                ->where('id', $c->id)
                ->update(['options' => json_encode($options, JSON_UNESCAPED_UNICODE)]);
        }
    }

    $this->info('Theme banner images have been updated with responsive srcset and sizes.');
})->describe('Update theme banner images with responsive srcset and sizes');

