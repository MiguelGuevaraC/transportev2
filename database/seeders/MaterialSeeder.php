<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('materials')->insert([
            [
                'name' => 'Example name',
            'state' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}