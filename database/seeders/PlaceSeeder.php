<?php

namespace Database\Seeders;

use App\Models\Place;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $places = [

            ['id' => '1', 'name' => 'Chiclayo', 'ubigeo' => '140101', 'address' => 'direccion1' , 'district_id'=>'1243'],
            ['id' => '2', 'name' => 'Lima', 'ubigeo' => '150101', 'address' => 'direccion2' , 'district_id'=>'1281'],
            ['id' => '3', 'name' => 'Trujillo', 'ubigeo' => '130101', 'address' => 'direccion3' , 'district_id'=>'1160'],
            ['id' => '4', 'name' => 'Piura', 'ubigeo' => '200101', 'address' => 'direccion4' , 'district_id'=>'1565'],
        ];

        foreach ($places as $place) {
            // Buscar el registro por su ID
            $place1 = Place::find($place['id']);

            if ($place1) {
                $place1->update($place);
            } else {
                Place::create($place);
            }
        }
    }
}
