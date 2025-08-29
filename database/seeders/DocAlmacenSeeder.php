<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocAlmacenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('doc_almacens')->insert([
            [
                'concept_id' => 1,
            'type' => 'Example type',
            'movement_date' => 'Sample',
            'reference_id' => 1,
            'reference_type' => 'Example reference_type',
            'user_id' => 1,
            'note' => 'Sample',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}