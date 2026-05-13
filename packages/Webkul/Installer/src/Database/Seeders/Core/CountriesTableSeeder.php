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

        $countries = [];
        $translations = [];

        foreach ($data as $countryData) {
            $countryCode = $countryData['code'];
            $defaultName = $this->resolveDefaultNameFromTranslations($countryData['translations']);
            
            $countries[] = [
                'id'     => $countryData['id'],
                'code'   => $countryCode,
                'name'   => $defaultName,
                'status' => ($countryCode === 'KW') ? 1 : 0,
            ];

            foreach ($countryData['translations'] as $translation) {
                $translations[] = [
                    'country_id' => $countryData['id'],
                    'locale'     => $translation['locale'],
                    'name'       => $translation['name'],
                ];
            }
        }

        DB::table('countries')->insert($countries);
        DB::table('country_translations')->insert($translations);
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
