<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    protected $model = Bank::class;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            ['name' => 'Banco de Crédito del Perú (BCP)'],
            ['name' => 'Interbank'],
            ['name' => 'Scotiabank Perú'],
            ['name' => 'BBVA Continental'],
            ['name' => 'Banco Pichincha Perú'],
            ['name' => 'MiBanco'],
            ['name' => 'Banco Financiero del Perú'],
            ['name' => 'Caja Huancayo'],
            ['name' => 'Caja Arequipa'],
            ['name' => 'Caja Sullana'],
            ['name' => 'Caja Piura'],
            ['name' => 'Caja Trujillo'],
        ];

        foreach ($array as $item) {
            $this->model::create($item);
        }
    }
}
