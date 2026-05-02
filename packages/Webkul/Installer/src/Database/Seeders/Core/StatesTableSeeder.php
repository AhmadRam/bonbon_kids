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

        $csvFile = __DIR__ . '/Data/states.csv';
        if (! file_exists($csvFile)) {
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);
        
        $states = [];
        $translations = [];
        $generatedCodes = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $defaultName = $this->resolveDefaultName($data['default_name'], $data['translations']);
            $countryCode = $data['country_code'];
            $stateCode = $this->resolveStateCode($data, $countryCode, $defaultName, $generatedCodes);

            // Set status to 1 for Kuwait (KW), 0 for others
            $status = ($countryCode === 'KW') ? 1 : 0;

            $states[] = [
                'id' => $data['state_id'],
                'country_id' => $data['country_id'],
                'country_code' => $countryCode,
                'code' => $stateCode,
                'default_name' => $defaultName,
                'status' => $status,
            ];

            // Parse translations e.g. "ar:الكويت | en:Kuwait"
            $transParts = explode(' | ', $data['translations']);
            foreach ($transParts as $part) {
                if (empty(trim($part))) continue;
                list($locale, $name) = explode(':', trim($part));
                $translations[] = [
                    'country_state_id' => $data['state_id'],
                    'locale' => trim($locale),
                    'default_name' => trim($name),
                ];
            }
        }
        fclose($handle);

        DB::table('country_states')->insert($states);
        DB::table('country_state_translations')->insert($translations);
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

    private function resolveStateCode(array $data, string $countryCode, string $defaultName, array &$generatedCodes): string
    {
        if (! empty($data['state_code'])) {
            return $data['state_code'];
        }

        $countryPrefix = $data['state_country_code'] ?: $countryCode ?: 'STATE';
        $baseCode = $countryPrefix.'-'.$this->makeCodeToken($defaultName, 'STATE');
        $code = $baseCode;

        if (in_array($code, $generatedCodes, true)) {
            $code = $baseCode.'-'.$data['state_id'];
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
