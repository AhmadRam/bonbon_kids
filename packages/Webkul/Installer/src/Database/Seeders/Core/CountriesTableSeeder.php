<?php

namespace Webkul\Installer\Database\Seeders\Core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountriesTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('countries')->delete();
        DB::table('country_translations')->delete();

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
            
            // Build data for the model with translations
            $countryInfo = [
                'id'     => $countryData['id'],
                'code'   => $countryCode,
                'name'   => $this->resolveDefaultNameFromTranslations($countryData['translations']),
                'status' => ($countryCode === 'KW') ? 1 : 0,
            ];

            foreach ($countryData['translations'] as $translation) {
                $countryInfo[$translation['locale']] = [
                    'name' => $translation['name'],
                ];
            }

            // Use the model to handle translations correctly
            \Webkul\Core\Models\Country::create($countryInfo);
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
