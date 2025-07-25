<?php

namespace Database\Seeders;

use App\Models\ShareValue;
use Illuminate\Database\Seeder;

class ShareValueSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            ['currency' => 'USD', 'amount' => 1.00],
            ['currency' => 'USD', 'amount' => 5.00],
            ['currency' => 'USD', 'amount' => 10.00],
            ['currency' => 'USD', 'amount' => 20.00],
            ['currency' => 'USD', 'amount' => 30.00],
        ];

        foreach ($values as $value) {
            ShareValue::create($value);
        }
    }
}
