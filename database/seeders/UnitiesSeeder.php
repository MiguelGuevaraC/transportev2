<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unities = [
            ['name' => 'Kg', 'code' => 'KG'],
            ['name' => 'Bidon', 'code' => 'BD'],
            ['name' => 'Bolsa', 'code' => 'BS'],
            ['name' => 'Cilindro', 'code' => 'CL'],
            ['name' => 'Mochila', 'code' => 'MC'],
            ['name' => 'Caja', 'code' => 'CJ'],
            ['name' => 'Tambor', 'code' => 'TB'],
        ];

        foreach ($unities as $unity) {
            if (!DB::table('unities')->where('name', $unity['name'])->exists()) {
                DB::table('unities')->insert($unity);
            }
        }
    }
}
