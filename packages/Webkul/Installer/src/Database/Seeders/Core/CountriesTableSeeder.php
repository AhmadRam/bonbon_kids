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
        $generatedCodes = [];

        foreach ($data as $countryData) {
            $countryCode = $countryData['code'];
            $defaultName = $this->resolveDefaultNameFromTranslations($countryData['translations'], $countryCode);
            $countryCode = $countryCode ?: $this->resolveCountryCode($countryData, $defaultName, $generatedCodes);

            // Set status to 1 for Kuwait (KW), 0 for others
            $status = ($countryCode === 'KW') ? 1 : 0;
            
            $countries[] = [
                'id'     => $countryData['id'],
                'code'   => $countryCode,
                'name'   => $defaultName,
                'status' => $status,
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

    private function resolveDefaultNameFromTranslations(array $translations, string $code = ''): string
    {
        // For Kuwait, we prioritize Arabic to ensure it shows correctly in fallbacks
        if ($code === 'KW') {
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

    private function resolveCountryCode(array $data, string $defaultName, array &$generatedCodes): string
    {
        $baseCode = $this->makeCodeToken($defaultName, 'CTRY');
        $code = $baseCode;

        if (in_array($code, $generatedCodes, true)) {
            $code = $baseCode.'-'.$data['id'];
        }

        $generatedCodes[] = $code;

        return $code;
    }

    private function makeCodeToken(string $value, string $fallback): string
    {
        $token = Str::upper(Str::slug(Str::ascii($value), '-'));

        return $token ?: $fallback;
    }
}
