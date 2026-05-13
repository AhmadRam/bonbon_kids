<?php

namespace Webkul\Installer\Database\Seeders\Core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatesTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('country_states')->delete();
        DB::table('country_state_translations')->delete();

        $jsonFile = __DIR__ . '/Data/locations_export_2026_05_13_103424.json';
        if (! file_exists($jsonFile)) {
            return;
        }

        $data = json_decode(file_get_contents($jsonFile), true);
        if (! $data) {
            return;
        }
        
        $states = [];
        $translations = [];
        $generatedCodes = [];

        foreach ($data as $countryData) {
            $countryCode = $countryData['code'];
            $countryId = $countryData['id'];

            if (empty($countryData['states'])) {
                continue;
            }

            foreach ($countryData['states'] as $stateData) {
                // For Kuwait, we ignore the English default_name from JSON and use our translation resolver
                $defaultName = ($countryCode === 'KW') 
                    ? $this->resolveDefaultNameFromTranslations($stateData['translations'], $countryCode)
                    : ($stateData['default_name'] ?: $this->resolveDefaultNameFromTranslations($stateData['translations'], $countryCode));

                $stateCode = $stateData['code'] ?: $this->resolveStateCode($stateData, $countryCode, $defaultName, $generatedCodes);

                // Set status to 1 for Kuwait (KW), 0 for others
                $status = ($countryCode === 'KW') ? 1 : 0;

                $states[] = [
                    'id'           => $stateData['id'],
                    'country_id'   => $countryId,
                    'country_code' => $countryCode,
                    'code'         => $stateCode,
                    'default_name' => $defaultName,
                    'status'       => $status,
                ];

                foreach ($stateData['translations'] as $translation) {
                    $translations[] = [
                        'country_state_id' => $stateData['id'],
                        'locale'           => $translation['locale'],
                        'default_name'     => $translation['name'],
                    ];
                }
            }
        }

        DB::table('country_states')->insert($states);
        DB::table('country_state_translations')->insert($translations);
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

    private function resolveStateCode(array $data, string $countryCode, string $defaultName, array &$generatedCodes): string
    {
        $baseCode = $countryCode.'-'.$this->makeCodeToken($defaultName, 'STATE');
        $code = $baseCode;

        if (in_array($code, $generatedCodes, true)) {
            $code = $baseCode.'-'.$data['id'];
        }

        $generatedCodes[] = $code;

        return $code;
    }

    private function makeCodeToken(string $value, string $fallback): string
    {
        $slug = \Illuminate\Support\Str::slug($value, '');
        $token = strtoupper(substr($slug, 0, 3));
        
        if (strlen($token) < 3) {
            $token = strtoupper(substr(md5($value), 0, 3));
        }

        return $token ?: $fallback;
    }
}
