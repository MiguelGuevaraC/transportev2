<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('brands')->insert([
            [
                'name' => 'Example name',
            'state' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}