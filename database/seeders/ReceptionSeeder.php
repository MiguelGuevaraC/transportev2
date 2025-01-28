<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('receptions')->insert([
            'codeReception' => 'R001-00000002',
            'netWeight' => 0,
            'paymentAmount' => 2500.00,
            'debtAmount' => 2500.00,
            'conditionPay' => 'Contado',
            'typeService' => 'Type of service',
            'type_responsiblePay' => 'Remitente',

            'numberDays' => 30,
            'creditAmount' => 0.00,

            'typeDelivery' => 'Type of delivery',
            'receptionDate' => Carbon::create('2023', '06', '01'),
            'transferLimitDate' => Carbon::create('2023', '06', '10'),
            'transferStartDate' => Carbon::create('2023', '06', '02'),
            'estimatedDeliveryDate' => Carbon::create('2023', '06', '15'),
            'actualDeliveryDate' => Carbon::create('2023', '06', '14'),

            'user_id' => 1,
            'origin_id' => 1,
            'sender_id' => 1,
            'destination_id' => 2,
            'recipient_id' => 2,
            'pickupResponsible_id' => 2,
            'payResponsible_id' => 1,
            'seller_id' => 1,

            'pointSender_id' => 2,
            'pointDestination_id' => 1,
            'branchOffice_id' => 1,

            'comment' => 'Urgent delivery',
            'tokenResponsible' => 'TOKEN12345',

            'address' => '123 Main St, Cityville',

            'state' => 'Generada',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ]);
        DB::table('carrier_guides')->insert([
            'status' => 'Pendiente',
            'document' => 'G001-00000001',
            'numero' => '000001',

            'transferStartDate' => Carbon::create('2024', '08', '01'),
            'transferDateEstimated' => Carbon::create('2024', '08', '05'),

            'tract_id' => 1,
            'platform_id' => 1,
            'origin_id' => 1,
            'destination_id' => 2,
            'sender_id' => 1,
            'recipient_id' => 2,

            'districtStart_id' => 1,
            'districtEnd_id' => 2,

            'reception_id' => 1,
            'payResponsible_id' => 1,
            'driver_id' => 1,
            'copilot_id' => 2,
            'subcontract_id' => null,

            'branchOffice_id' => 1,

            'ubigeoStart' => '150101',
            'ubigeoEnd' => '150102',
            'addressStart' => 'Av. Siempre Viva 123, Springfield',
            'addressEnd' => 'Calle Falsa 456, Shelbyville',

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ]);
    }
}
