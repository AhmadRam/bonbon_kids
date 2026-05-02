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

        $csvFile = __DIR__ . '/Data/countries.csv';
        if (! file_exists($csvFile)) {
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);
        
        $countries = [];
        $translations = [];
        $generatedCodes = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            $defaultName = $this->resolveDefaultName($data['default_name'], $data['translations']);
            $countryCode = $this->resolveCountryCode($data, $defaultName, $generatedCodes);

            // Set status to 1 for Kuwait (KW), 0 for others
            $status = ($countryCode === 'KW') ? 1 : 0;
            
            $countries[] = [
                'id' => $data['country_id'],
                'code' => $countryCode,
                'name' => $defaultName,
                'status' => $status,
            ];

            // Parse translations e.g. "ar:الكويت | en:Kuwait"
            $transParts = explode(' | ', $data['translations']);
            foreach ($transParts as $part) {
                if (empty(trim($part))) continue;
                list($locale, $name) = explode(':', trim($part));
                $translations[] = [
                    'country_id' => $data['country_id'],
                    'locale' => trim($locale),
                    'name' => trim($name),
                ];
            }
        }
        fclose($handle);

        DB::table('countries')->insert($countries);
        DB::table('country_translations')->insert($translations);
    }

    private function resolveDefaultName(?string $defaultName, ?string $translations): string
    {
        if (! empty($defaultName)) {
            return $defaultName;
        }

        if (! empty($translations)) {
            foreach (explode(' | ', $translations) as $part) {
                $part = trim($part);

                if (str_starts_with($part, 'en:')) {
                    return trim(substr($part, 3));
                }
            }
        }

        return '';
    }

    private function resolveCountryCode(array $data, string $defaultName, array &$generatedCodes): string
    {
        if (! empty($data['country_code'])) {
            return $data['country_code'];
        }

        $baseCode = $this->makeCodeToken($defaultName, 'CTRY');
        $code = $baseCode;

        if (in_array($code, $generatedCodes, true)) {
            $code = $baseCode.'-'.$data['country_id'];
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
