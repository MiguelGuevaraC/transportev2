<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TireMeasureSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tire_measures')->insert([
            [
                'name' => 'Example name',
            'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}