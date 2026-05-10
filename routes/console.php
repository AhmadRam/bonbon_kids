<?php

use Illuminate\Support\Facades\Artisan;
use Webkul\CMS\Models\PageTranslation;

Artisan::command('dump-cms-pages', function () {
    $pages = PageTranslation::all(['url_key', 'page_title', 'html_content'])->toArray();
    file_put_contents(base_path('cms_pages_dump.json'), json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $this->info('Dumped to cms_pages_dump.json');
});
