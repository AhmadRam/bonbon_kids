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
                    $defaultName = $cityData['default_name'] ?: $this->resolveDefaultNameFromTranslations($cityData['translations']);
                    
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

                    // Build data for the model with translations
                    $cityInfo = [
                        'id'               => $cityData['id'],
                        'country_id'       => $countryId,
                        'country_code'     => $countryCode,
                        'country_state_id' => $stateId,
                        'state_code'       => $stateCode,
                        'code'             => $fullCode,
                        'status'           => ($countryCode === 'KW') ? 1 : 0,
                    ];

                    foreach ($cityData['translations'] as $translation) {
                        $cityInfo[$translation['locale']] = [
                            'name' => $translation['name'],
                        ];
                    }

                    // Use the model to handle translations correctly
                    \Webkul\Core\Models\CountryCity::create($cityInfo);
                }
            }
        }
    }

    private function resolveDefaultNameFromTranslations(array $translations): string
    {
        foreach ($translations as $translation) {
            if ($translation['locale'] === 'en') {
                return $translation['name'];
            }
        }

        return $translations[0]['name'] ?? '';
    }
}
