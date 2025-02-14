<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddresbyBranchOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $addresses = [
            ['name' => 'Av. ANGELICA GAMARRA 1346 LOS OLIVOS - LIMA - LIMA', 'reference' => 'AGENCIA LOS OLIVOS', 'client_id' => 1],
            ['name' => 'Jr FRANCIA 926 LA VICTORIA - LIMA - LIMA', 'reference' => 'AGENCIA LA VICTORIA', 'client_id' => 1],
            ['name' => 'MZ.38 LOTE 4A PJ CHOSICA DEL NORTE - LA VICTORIA - CHICLAYO', 'reference' => 'AGENCIA CHOSICA DEL NORTE CHICLAYO', 'client_id' => 1],
            ['name' => 'AV. TAHUANTISUYO 812 - LA ESPERANZA - TRUJILLO - LA LIBERTAD', 'reference' => 'AGENCIA LA ESPERANZA TRUJILLO', 'client_id' => 1],
            ['name' => '-', 'reference' => 'AGENCIA PIURA', 'client_id' => 1],
        ];

        DB::table('addresses')->insert($addresses);
    }
}
