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

        $stateCodeMap = $this->getStateCodeMap();

        $csvFile = __DIR__ . '/Data/cities.csv';
        if (! file_exists($csvFile)) {
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);
        
        $cities = [];
        $translations = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $defaultName = $this->resolveDefaultName($data['default_name'], $data['translations']);
            $countryCode = $data['country_code'];
            $resolvedStateCode = $this->resolveCityStateCode($data, $countryCode, $stateCodeMap, $defaultName);

            // Set status to 1 for Kuwait (KW), 0 for others
            $status = ($countryCode === 'KW') ? 1 : 0;
            
            $slug = \Illuminate\Support\Str::slug($defaultName, '');
            $cityCodePart = strtoupper(substr($slug, 0, 3));
            if (strlen($cityCodePart) < 3) {
                $cityCodePart = strtoupper(substr(md5($defaultName), 0, 3));
            }
            
            if (str_starts_with($resolvedStateCode, $countryCode . '-')) {
                $fullCode = $resolvedStateCode . '-' . $cityCodePart;
            } else {
                $fullCode = $countryCode . '-' . $resolvedStateCode . '-' . $cityCodePart;
            }
            
            $cities[] = [
                'id' => $data['city_id'],
                'country_id' => $data['country_id'] !== '' ? $data['country_id'] : null,
                'country_code' => $countryCode,
                'country_state_id' => $data['country_state_id'] !== '' ? $data['country_state_id'] : null,
                'state_code' => $resolvedStateCode,
                'code' => $fullCode,
                'default_name' => $defaultName,
                'status' => $status,
            ];

            // Parse translations e.g. "ar:المنامة | en:Al Manama"
            $transParts = explode(' | ', $data['translations']);
            foreach ($transParts as $part) {
                if (empty(trim($part))) continue;
                list($locale, $name) = explode(':', trim($part));
                $translations[] = [
                    'country_state_city_id' => $data['city_id'],
                    'locale' => trim($locale),
                    'name' => trim($name),
                ];
            }
        }
        fclose($handle);

        DB::table('country_state_cities')->insert($cities);
        DB::table('country_city_translations')->insert($translations);
    }

    private function getStateCodeMap(): array
    {
        $csvFile = __DIR__.'/Data/states.csv';

        if (! file_exists($csvFile)) {
            return [];
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);
        $stateCodeMap = [];
        $generatedCodes = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $defaultName = $this->resolveDefaultName($data['default_name'], $data['translations']);

            $stateCodeMap[$data['state_id']] = $this->resolveStateCode($data, $data['country_code'], $defaultName, $generatedCodes);
        }

        fclose($handle);

        return $stateCodeMap;
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

    private function resolveCityStateCode(array $data, string $countryCode, array $stateCodeMap, string $defaultName): string
    {
        if (! empty($data['state_code'])) {
            return $data['state_code'];
        }

        if (! empty($data['city_state_code'])) {
            return $data['city_state_code'];
        }

        if (! empty($data['country_state_id']) && isset($stateCodeMap[$data['country_state_id']])) {
            return $stateCodeMap[$data['country_state_id']];
        }

        return ($countryCode ?: 'CITY').'-'.$this->makeCodeToken($defaultName ?: $data['city_id'], 'CITY');
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

    private function makeCodeToken(string|int $value, string $fallback): string
    {
        $slug = \Illuminate\Support\Str::slug((string) $value, '');
        $token = strtoupper(substr($slug, 0, 3));
        
        if (strlen($token) < 3) {
            $token = strtoupper(substr(md5((string) $value), 0, 3));
        }

        return $token ?: $fallback;
    }
}
