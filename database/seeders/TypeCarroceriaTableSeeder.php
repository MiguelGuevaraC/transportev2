<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeCarroceriaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('type_carrocerias')->insert([
            [
                'description' => 'Tipo Carroceria 1',
                'typecompany_id' => 1,

            ],
            [
                'description' => 'Tipo Carroceria 2',
                'typecompany_id' => 2,
            ],
        ]);
    }
}
