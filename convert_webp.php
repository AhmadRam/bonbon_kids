<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Webkul\Theme\Models\ThemeCustomization;
use Illuminate\Support\Facades\Storage;

$customizations = ThemeCustomization::all();
$convertedCount = 0;

function processOptions($options, &$updated) {
    global $convertedCount;
    foreach ($options as $key => $value) {
        if (is_array($value)) {
            $options[$key] = processOptions($value, $updated);
        } elseif (is_string($value) && preg_match('/\.(png|jpg|jpeg)$/i', $value)) {
            echo "Checking string: $value\n";
            $relativePath = preg_replace('/^storage\//i', '', $value);
            if (Storage::disk('public')->exists($relativePath)) {
                echo "Found image: $relativePath\n";
                $path = Storage::disk('public')->path($relativePath);
                try {
                    $image = image_manager()->read($path)->encodeByExtension('webp');
                    $newRelativePath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $relativePath);
                    Storage::disk('public')->put($newRelativePath, (string) $image);
                    $options[$key] = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $value);
                    $updated = true;
                    echo "Converted to: " . $options[$key] . "\n";
                    $convertedCount++;
                } catch (\Exception $e) {
                    echo "Failed to convert $value: " . $e->getMessage() . "\n";
                }
            } else {
                echo "File not found in storage: $relativePath\n";
            }
        }
    }
    return $options;
}

foreach ($customizations as $customization) {
    $options = $customization->options;
    $updated = false;
    if (is_array($options)) {
        $options = processOptions($options, $updated);
        if ($updated) {
            $customization->options = $options;
            $customization->save();
            echo "Updated theme customization ID: {$customization->id}\n";
        }
    }
}
echo "Total converted: $convertedCount\n";
