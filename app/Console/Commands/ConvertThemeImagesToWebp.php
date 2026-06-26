<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Theme\Models\ThemeCustomization;
use Illuminate\Support\Facades\Storage;
use Webkul\ImageCache\Facades\ImageCache; // Not required since we can use image_manager()

class ConvertThemeImagesToWebp extends Command
{
    protected $signature = 'theme:convert-webp';
    protected $description = 'Convert all theme customization images to WEBP and update the database';

    public function handle()
    {
        $customizations = ThemeCustomization::all();
        $convertedCount = 0;

        foreach ($customizations as $customization) {
            $options = $customization->options;
            $updated = false;

            if (is_array($options)) {
                $options = $this->processOptions($options, $updated);

                if ($updated) {
                    $customization->options = $options;
                    $customization->save();
                    $this->info("Updated theme customization ID: {$customization->id}");
                }
            }
        }

        $this->info("Converted $convertedCount images to WebP.");
    }

    private function processOptions($options, &$updated)
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $options[$key] = $this->processOptions($value, $updated);
            } elseif (is_string($value)) {
                if (preg_match('/\.(png|jpg|jpeg)$/i', $value)) {
                    $this->info("Checking string: " . $value);
                    if (Storage::disk('public')->exists($value)) {
                        $this->info("Found image: " . $value);
                        $path = Storage::disk('public')->path($value);
                    
                    try {
                        // Use Bagisto's ImageManager (Intervention) to encode
                        $image = image_manager()->read($path)->encodeByExtension('webp');
                        
                        $newPath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $value);
                        Storage::disk('public')->put($newPath, (string) $image);
                        
                        $options[$key] = $newPath;
                        $updated = true;
                        
                        $this->info("Converted to: " . $newPath);
                    } catch (\Exception $e) {
                        $this->error("Failed to convert $value: " . $e->getMessage());
                    }
                }
            }
        }
        return $options;
    }
}
