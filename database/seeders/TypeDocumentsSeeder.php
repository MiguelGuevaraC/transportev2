<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $documents = [
            ['id' => 1, 'name' => 'BOLETA DE VENTA'],
            ['id' => 2, 'name' => 'BOLETO DE VIAJE EMITIDO POR LAS EMPRESAS DE TRANSPORTE PÚBLICO INTERPROVINCIAL DE PASAJEROS'],
            ['id' => 3, 'name' => 'DOCUMENTOS QUE EMITAN LOS CONCESIONARIOS DEL SERVICIO DE REVISIONES TÉCNICAS VEHICULARES, P'],
            ['id' => 4, 'name' => 'FACTURA'],
            ['id' => 5, 'name' => 'ORDEN DE TRABAJO'],
            ['id' => 6, 'name' => 'RECIBO POR HONORARIOS'],
            ['id' => 7, 'name' => 'RECIBO POR SERVICIOS PÚBLICOS DE SUMINISTRO DE ENERGÍA ELÉCTRICA, AGUA, TELEFONO, TELEX, Y TELEG'],
            ['id' => 8, 'name' => 'SUSTENTO CONTABLE. TICKET O CINTA EMITIDO POR MAQUINA REGISTRADORA'],
            ['id' => 9, 'name' => 'VALE'],
        ];

        foreach ($documents as $doc) {
            DB::table('type_documents')->insert([
                'id'         => $doc['id'],
                'name'       => $doc['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
