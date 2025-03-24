<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionConceptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $concepts = [
            ['id'=>'1','name' => 'ANTICIPOS - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'2','name' => 'ANTICIPOS - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'3','name' => 'CHEQUES - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'4','name' => 'CHEQUES - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'5','name' => 'DEPOSITO EN CUENTA - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'6','name' => 'DEPOSITO EN CUENTA - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'7','name' => 'EFECTIVO - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'8','name' => 'EFECTIVO - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'9','name' => 'GIRO - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'10','name' => 'GIRO - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'11','name' => 'TRANSFERENCIA DE FONDOS - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'12','name' => 'TRANSFERENCIA DE FONDOS - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'13','name' => 'TRANSFERENCIAS COMERCIO EXTERIOR - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'14','name' => 'TRANSFERENCIAS COMERCIO EXTERIOR - EGRESO', 'type' => 'EGRESO'],
            ['id'=>'15','name' => 'RETENCIONES CLIENTES - INGRESO', 'type' => 'INGRESO'],
            ['id'=>'16','name' => 'RETENCIONES CLIENTES - EGRESO', 'type' => 'EGRESO'],
        ];

  

        foreach ($concepts as $concept) {
            $existing = DB::table('transaction_concepts')->where('id', $concept['id'])->first();

            if ($existing) {
                // Si ya existe, actualiza
                DB::table('transaction_concepts')->where('id', $existing->id)->update([
                    'id'       => $concept['id'],
                    'type'       => $concept['type'],
                    'name'       => $concept['name'],
                    'updated_at' => now(),
                ]);
            } else {
                // Si no existe, inserta con el ID
                DB::table('transaction_concepts')->insert([
                    'id'       => $concept['id'],
                    'name'       => $concept['name'],
                    'type'       => $concept['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
