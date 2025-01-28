<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotiveTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('motives')->insert([
            ['id' => '1','code' => '01', 'name' => 'VENTA'],
            ['id' => '2','code' => '02', 'name' => 'COMPRA'],
            ['id' => '4','code' => '04', 'name' => 'TRASLADO ENTRE ESTABLECIMIENTOS DE LA MISMA EMPRESA'],
            ['id' => '8','code' => '08', 'name' => 'IMPORTACIÓN'],
            ['id' => '9','code' => '09', 'name' => 'EXPORTACIÓN'],
            ['id' => '13','code' => '13', 'name' => 'OTROS'],
            ['id' => '14','code' => '14', 'name' => 'VENTA SUJETA A CONFIRMACIÓN DEL COMPRADOR'],
            ['id' => '18','code' => '18', 'name' => 'TRASLADO EMISOR ITINERANTE CP'],
            ['id' => '19','code' => '19', 'name' => 'TRASLADO A ZONA PRIMARIA'],
        ]);
        

    }
}
