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

        foreach ($data as $countryData) {
            $countryCode = $countryData['code'];
            $countryId = $countryData['id'];

            if (empty($countryData['states'])) {
                continue;
            }

            foreach ($countryData['states'] as $stateData) {
                // Build data for the model with translations
                $stateInfo = [
                    'id'           => $stateData['id'],
                    'country_id'   => $countryId,
                    'country_code' => $countryCode,
                    'code'         => $stateData['code'],
                    'status'       => ($countryCode === 'KW') ? 1 : 0,
                ];

                foreach ($stateData['translations'] as $translation) {
                    $stateInfo[$translation['locale']] = [
                        'default_name' => $translation['name'],
                    ];
                }

                // Use the model to handle translations correctly
                \Webkul\Core\Models\CountryState::create($stateInfo);
            }
        }
    }
}
