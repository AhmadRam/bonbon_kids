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
