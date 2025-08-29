<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocAlmacenDetailSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('doc_almacen_details')->insert([
            [
                'doc_almacen_id' => 1,
            'tire_id' => 1,
            'quantity' => 1,
            'unit_price' => 'Sample',
            'total_value' => 'Sample',
            'note' => 'Sample',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}