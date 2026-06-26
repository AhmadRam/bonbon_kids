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
        } elseif (is_string($value)) {
            // Check if it's a direct image path
            if (preg_match('/^[\w\-\/]+\.(png|jpg|jpeg)$/i', $value)) {
                $pathsToProcess = [$value];
            } else {
                // Check if it's an HTML string containing paths
                preg_match_all('/(storage\/theme\/[\w\-\/]+\.(?:png|jpg|jpeg))/i', $value, $matches);
                $pathsToProcess = $matches[1];
            }

            foreach ($pathsToProcess as $match) {
                echo "Checking string: $match\n";
                $relativePath = preg_replace('/^storage\//i', '', $match);
                if (Storage::disk('public')->exists($relativePath)) {
                    echo "Found image: $relativePath\n";
                    $path = Storage::disk('public')->path($relativePath);
                    try {
                        $image = image_manager()->read($path)->encodeByExtension('webp');
                        $newRelativePath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $relativePath);
                        Storage::disk('public')->put($newRelativePath, (string) $image);
                        
                        $newMatch = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $match);
                        $value = str_replace($match, $newMatch, $value);
                        $updated = true;
                        
                        echo "Converted to: " . $newMatch . "\n";
                        $convertedCount++;
                    } catch (\Exception $e) {
                        echo "Failed to convert $match: " . $e->getMessage() . "\n";
                    }
                }
            }
            $options[$key] = $value;
        }
    }
    return $options;
}

foreach ($customizations as $customization) {
    if (in_array($customization->id, [2, 15])) {
        echo "Dumping ID {$customization->id} options:\n";
        print_r($customization->options);
    }
    
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
