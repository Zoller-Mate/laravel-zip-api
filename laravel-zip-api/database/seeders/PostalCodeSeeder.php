<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\Place;
use App\Models\PostalCode;
use Illuminate\Database\Seeder;

class PostalCodeSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/postal_codes.csv');

        $file = fopen($path, 'r');

        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            [$postal, $placeName, $countyName] = $row;

            
            $county = County::firstOrCreate(['name' => $countyName]);

            $place = Place::firstOrCreate([
                'name' => $placeName,
                'county_id' => $county->id
            ]);

            PostalCode::create([
                'postal_code' => $postal,
                'place_id' => $place->id
            ]);
        }

        fclose($file);
    }
}
