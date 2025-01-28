<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $places = DB::table('places')->pluck('name', 'id');

        // Inserción de rutas con nombres de lugares
        DB::table('routes')->insert([
            [
                'placeStart_id' => 1,
                'placeEnd_id' => 2,
                'placeStart' => $places[1],
                'placeEnd' => $places[2],
                'routeFather_id' => null,
                'state' => true,
            ],
            [
                'placeStart_id' => 2,
                'placeEnd_id' => 3,
                'placeStart' => $places[2],
                'placeEnd' => $places[3],
                'routeFather_id' => 1,
                'state' => true,
            ],
            [
                'placeStart_id' => 3,
                'placeEnd_id' => 4,
                'placeStart' => $places[3],
                'placeEnd' => $places[4],
                'routeFather_id' => 1,
                'state' => true,
            ],
        ]);

    }
}
