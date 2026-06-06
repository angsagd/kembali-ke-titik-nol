<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            'Indonesia' => [
                'iso_code' => 'ID',
                'cities' => [
                    ['name' => 'Yogyakarta', 'latitude' => -7.7956000, 'longitude' => 110.3695000],
                    ['name' => 'Jakarta', 'latitude' => -6.2088000, 'longitude' => 106.8456000],
                    ['name' => 'Bandung', 'latitude' => -6.9175000, 'longitude' => 107.6191000],
                    ['name' => 'Surabaya', 'latitude' => -7.2575000, 'longitude' => 112.7521000],
                    ['name' => 'Semarang', 'latitude' => -6.9667000, 'longitude' => 110.4167000],
                    ['name' => 'Makassar', 'latitude' => -5.1477000, 'longitude' => 119.4327000],
                    ['name' => 'Balikpapan', 'latitude' => -1.2379000, 'longitude' => 116.8529000],
                    ['name' => 'Samarinda', 'latitude' => -0.5022000, 'longitude' => 117.1536000],
                    ['name' => 'Medan', 'latitude' => 3.5952000, 'longitude' => 98.6722000],
                    ['name' => 'Denpasar', 'latitude' => -8.6705000, 'longitude' => 115.2126000],
                    ['name' => 'Banjarmasin', 'latitude' => -3.3186000, 'longitude' => 114.5944000],
                    ['name' => 'Palembang', 'latitude' => -2.9761000, 'longitude' => 104.7754000],
                    ['name' => 'Pekanbaru', 'latitude' => 0.5071000, 'longitude' => 101.4478000],
                    ['name' => 'Manado', 'latitude' => 1.4748000, 'longitude' => 124.8421000],
                    ['name' => 'Jayapura', 'latitude' => -2.5916000, 'longitude' => 140.6690000],
                ],
            ],
            'Malaysia' => [
                'iso_code' => 'MY',
                'cities' => [
                    ['name' => 'Kuala Lumpur', 'latitude' => 3.1390000, 'longitude' => 101.6869000],
                ],
            ],
            'Singapore' => [
                'iso_code' => 'SG',
                'cities' => [
                    ['name' => 'Singapore', 'latitude' => 1.3521000, 'longitude' => 103.8198000],
                ],
            ],
            'Australia' => [
                'iso_code' => 'AU',
                'cities' => [
                    ['name' => 'Sydney', 'latitude' => -33.8688000, 'longitude' => 151.2093000],
                    ['name' => 'Melbourne', 'latitude' => -37.8136000, 'longitude' => 144.9631000],
                ],
            ],
            'United States' => [
                'iso_code' => 'US',
                'cities' => [
                    ['name' => 'Houston', 'latitude' => 29.7604000, 'longitude' => -95.3698000],
                    ['name' => 'San Francisco', 'latitude' => 37.7749000, 'longitude' => -122.4194000],
                ],
            ],
        ];

        collect($countries)->each(function (array $data, string $countryName): void {
            $country = Country::query()->updateOrCreate(
                ['name' => $countryName],
                ['iso_code' => $data['iso_code']],
            );

            collect($data['cities'])->each(fn (array $city): City => City::query()->updateOrCreate(
                [
                    'country_id' => $country->id,
                    'name' => $city['name'],
                ],
                [
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                ],
            ));
        });
    }
}
