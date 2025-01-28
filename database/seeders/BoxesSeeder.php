<?php

namespace Database\Seeders;

use App\Models\Box;
use Illuminate\Database\Seeder;

class BoxesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [

            ['name' => 'Caja 1', 'serie' => '001', 'branchOffice_id' => '1'],
            ['name' => 'Caja 2', 'serie' => '002', 'branchOffice_id' => '1'],
            ['name' => 'Caja 3', 'serie' => '003', 'branchOffice_id' => '2'],
            ['name' => 'Caja 4', 'serie' => '004', 'branchOffice_id' => '2'],
            
        ];

        foreach ($array as $item) {
            Box::create($item);
        }
    }
}
