<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailReceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('detail_receptions')->insert([

            'numero' => 'D001-00000001',
            'description' => 'Description of the item',
            'weight' => 1500,
            'paymentAmount' => 1000.00,
            'debtAmount' => 200.00,
            'comissionAmount' => 100.00,
            'costLoad' => 50.00,
            'costDownload' => 30.00,
            'comment' => 'Some comments here',
            'status' => 'Pendiente',
            'comissionAgent_id' => null,
            'reception_id' => 1,
            'programming_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ]);
    }
}
