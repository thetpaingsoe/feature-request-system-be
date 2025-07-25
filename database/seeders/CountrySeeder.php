<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US'],
            ['name' => 'United Kingdom', 'code' => 'GB'],
            ['name' => 'Thailand', 'code' => 'TH'],
            ['name' => 'Singapore', 'code' => 'SG'],
            ['name' => 'Germany', 'code' => 'DE'],
            ['name' => 'France', 'code' => 'FR'],
            ['name' => 'Japan', 'code' => 'JP'],
            ['name' => 'China', 'code' => 'CN'],
            ['name' => 'Australia', 'code' => 'AU'],
            ['name' => 'Canada', 'code' => 'CA'],
            ['name' => 'India', 'code' => 'IN'],
            ['name' => 'Brazil', 'code' => 'BR'],
            ['name' => 'South Africa', 'code' => 'ZA'],
            ['name' => 'Malaysia', 'code' => 'MY'],
            ['name' => 'Indonesia', 'code' => 'ID'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
