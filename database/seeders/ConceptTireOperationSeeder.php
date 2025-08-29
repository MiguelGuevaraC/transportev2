<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptTireOperationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('concept_tire_operations')->insert([
            [
                'name' => 'Example name',
            'type' => 'Example type',
            'status' => 'Example status',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}