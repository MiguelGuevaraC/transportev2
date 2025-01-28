<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConeptPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentConcepts = [
            [
                'name' => 'Apertura Caja',
                'type' => 'Ingreso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cierre Caja',
                'type' => 'Egreso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Venta',
                'type' => 'Ingreso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Monto Viaje',
                'type' => 'Egreso',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Pago Luz',
                'type' => 'Egreso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fondo de Caja',
                'type' => 'Ingreso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('payment_concepts')->insert($paymentConcepts);
    }
}
