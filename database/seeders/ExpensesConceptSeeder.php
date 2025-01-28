<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpensesConceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('expenses_concepts')->insert([
            ['name' => 'BOLSA DE VIAJE', 'type' => 'DATOS', 'typeConcept' => 'Ingreso'],
            ['name' => 'PEAJES', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'ESTIBA', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'DESESTIVA', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'VIATICOS - AUMENTACION', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'VIATICOS - VICIOS', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'BALANZA', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'REP. LLANTA', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'HOSPEDAJE', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'MOVILIDAD', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'COCHERA', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'LAVADO', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'GUARDIANIA', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'PETROLEO', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'COMISION', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'OTROS', 'type' => 'G-RUTA', 'typeConcept' => 'Egreso'],
            ['name' => 'RELLENO DE RUTA', 'type' => 'DATOS', 'typeConcept' => 'Egreso'],
            ['name' => 'ÚLTIMO TANQUEO', 'type' => 'DATOS', 'typeConcept' => 'Egreso'],
        ]);
    }

}
