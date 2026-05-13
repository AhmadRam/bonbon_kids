<?php

namespace Webkul\Installer\Database\Seeders\Core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CitiesTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('country_state_cities')->delete();
        DB::table('country_city_translations')->delete();

        $jsonFile = __DIR__ . '/Data/locations_export_2026_05_13_103424.json';
        if (! file_exists($jsonFile)) {
            return;
        }

        $data = json_decode(file_get_contents($jsonFile), true);
        if (! $data) {
            return;
        }
        
        $cities = [];
        $translations = [];

        foreach ($data as $countryData) {
            $countryCode = $countryData['code'];
            $countryId = $countryData['id'];

            if (empty($countryData['states'])) {
                continue;
            }

            foreach ($countryData['states'] as $stateData) {
                $stateCode = $stateData['code'];
                $stateId = $stateData['id'];

                if (empty($stateData['cities'])) {
                    continue;
                }

                foreach ($stateData['cities'] as $cityData) {
                    // For Kuwait, we ignore the English default_name from JSON and use our translation resolver
                    $defaultName = ($countryCode === 'KW') 
                        ? $this->resolveDefaultNameFromTranslations($cityData['translations'], $countryCode)
                        : ($cityData['default_name'] ?: $this->resolveDefaultNameFromTranslations($cityData['translations'], $countryCode));
                    
                    // Fallback code generation logic if code is missing
                    if (! empty($cityData['code'])) {
                        $fullCode = $cityData['code'];
                    } else {
                        $slug = \Illuminate\Support\Str::slug($defaultName, '');
                        $cityCodePart = strtoupper(substr($slug, 0, 3));
                        if (strlen($cityCodePart) < 3) {
                            $cityCodePart = strtoupper(substr(md5($defaultName), 0, 3));
                        }
                        
                        if (str_starts_with($stateCode, $countryCode . '-')) {
                            $fullCode = $stateCode . '-' . $cityCodePart;
                        } else {
                            $fullCode = $countryCode . '-' . $stateCode . '-' . $cityCodePart;
                        }
                    }

                    // Set status to 1 for Kuwait (KW), 0 for others
                    $status = ($countryCode === 'KW') ? 1 : 0;
                    
                    $cities[] = [
                        'id'               => $cityData['id'],
                        'country_id'       => $countryId,
                        'country_code'     => $countryCode,
                        'country_state_id' => $stateId,
                        'state_code'       => $stateCode,
                        'code'             => $fullCode,
                        'default_name'     => $defaultName,
                        'status'           => $status,
                    ];

                    foreach ($cityData['translations'] as $translation) {
                        $translations[] = [
                            'country_state_city_id' => $cityData['id'],
                            'locale'                => $translation['locale'],
                            'name'                  => $translation['name'],
                        ];
                    }
                }
            }
        }

        DB::table('country_state_cities')->insert($cities);
        DB::table('country_city_translations')->insert($translations);
    }

    private function resolveDefaultNameFromTranslations(array $translations, string $countryCode = ''): string
    {
        // For Kuwait, we prioritize Arabic to ensure it shows correctly in fallbacks
        if ($countryCode === 'KW') {
            foreach ($translations as $translation) {
                if ($translation['locale'] === 'ar') {
                    return $translation['name'];
                }
            }
        }

        foreach ($translations as $translation) {
            if ($translation['locale'] === 'en') {
                return $translation['name'];
            }
        }

        return $translations[0]['name'] ?? '';
    }
}
