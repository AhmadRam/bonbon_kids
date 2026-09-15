<?php

$content = file_get_contents('/var/www/html/packages/Webkul/Shop/src/Http/Controllers/ProductsCategoriesProxyController.php');
$content = str_replace('abort(404);', 'echo "ABORTED 404: " . __LINE__; abort(404);', $content);
file_put_contents('/var/www/html/packages/Webkul/Shop/src/Http/Controllers/ProductsCategoriesProxyController.php', $content);
