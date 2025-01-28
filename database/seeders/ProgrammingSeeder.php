<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgrammingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('programmings')->insert([
            'id' => 1,
            'numero' => 'V001-00000001',
            'departureDate' => Carbon::create('2023', '06', '01'),
            'estimatedArrivalDate' => Carbon::create('2023', '06', '05'),
            'actualArrivalDate' => Carbon::create('2023', '06', '04'),
            'state' => 'Generada',
            'isload' => true,
            'totalWeight' => 5000,
            'carrierQuantity' => 10,
            'detailQuantity' => 50,
            'totalAmount' => 10000.50,
            'origin_id' => 1,
            'destination_id' => 2,
            'tract_id' => 1,
            'platForm_id' => 2,
            'branchOffice_id' => 1,

        ]);
    }
}
