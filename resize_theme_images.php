<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Intervention\Image\ImageManagerStatic as Image;

// The path to the theme images on the server
$themeDir = __DIR__.'/storage/app/public/theme';

if (!is_dir($themeDir)) {
    echo "Theme directory not found at: $themeDir\n";
    exit;
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($themeDir)
);

$count = 0;
$savedBytes = 0;

foreach ($files as $file) {
    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
        $filePath = $file->getRealPath();
        
        try {
            $image = image_manager()->read($filePath);
            $width = $image->width();
            $height = $image->height();
            $fileSizeBefore = filesize($filePath);
            
            $maxWidth = 600; // Limit max width to 600px to save space but keep quality
            
            if ($width > $maxWidth) {
                // Resize proportionally
                $image->scale(width: $maxWidth);
                
                // Save it back with high compression
                $encoded = (string) $image->encodeByExtension($file->getExtension(), quality: 80);
                file_put_contents($filePath, $encoded);
                
                $fileSizeAfter = filesize($filePath);
                $savedBytes += ($fileSizeBefore - $fileSizeAfter);
                $count++;
                
                echo "Resized: {$file->getFilename()} (from {$width}x{$height} to {$image->width()}x{$image->height()})\n";
            }
        } catch (\Exception $e) {
            echo "Failed to process {$file->getFilename()}: " . $e->getMessage() . "\n";
        }
    }
}

$savedMb = number_format($savedBytes / 1048576, 2);
echo "Successfully resized $count images.\n";
echo "Total space saved: $savedMb MB\n";
