<?php

use Illuminate\Contracts\Console\Kernel;
use Webkul\Marketing\Models\URLRewrite;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$urls = URLRewrite::where('locale', 'ar')->pluck('target_path')->toArray();
$urls[] = ''; // home page

echo 'Found '.count($urls)." URLs to warmup.\n";

$mh = curl_multi_init();
$handles = [];
$active = null;

// process in batches of 20
$batches = array_chunk($urls, 20);

foreach ($batches as $batch) {
    foreach ($batch as $url_key) {
        $ch = curl_init();
        $url = 'https://bonbonkw.com/'.urlencode($url_key);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_multi_add_handle($mh, $ch);
        $handles[] = $ch;
    }

    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) == -1) {
            usleep(100);
        }
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }

    foreach ($handles as $ch) {
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    $handles = [];
    echo "Warmed up a batch of 20 URLs.\n";
}
curl_multi_close($mh);
echo "Done!\n";
