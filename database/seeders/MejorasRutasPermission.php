<?php
namespace Database\Seeders;

use App\Models\DriverExpense;
use App\Models\ExpensesConcept;
use App\Models\GroupMenu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MejorasRutasPermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $conceptosDriverExpenses = [
            ['id' => '22', 'type' => 'Transferir', 'typeConcept' => 'Ingreso','name'=>'TRANFERENCIA-INGRESO SALDO'],
            ['id' => '23', 'type' => 'Transferir', 'typeConcept' => 'Egreso','name'=>'TRANFERENCIA-EGRESO SALDO'],
        ];

        foreach ($conceptosDriverExpenses as $driverexpense) {
            ExpensesConcept::updateOrCreate(['id' => $driverexpense['id']], $driverexpense);
        }


        $grupos = [
            ['id' => '6', 'name' => 'Caja Grande', 'icon' => 'bank'],
            ['id' => '7', 'name' => 'Almacén', 'icon' => 'almacen'],
            ['id' => '8', 'name' => 'Taller', 'icon' => 'taller'],
        ];

        foreach ($grupos as $grupo) {
            GroupMenu::updateOrCreate(['id' => $grupo['id']], $grupo);
        }

        $permissions = [
            [
                'id'           => 31,
                'name'         => 'Unidades',
                'route'        => 'unidades',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id'           => 32,
                'name'         => 'Tarifas',
                'route'        => 'tarifas',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id'           => 33,
                'name'         => 'Productos',
                'route'        => 'productos',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id'           => 34,
                'name'         => 'Almacén de Cargas',
                'route'        => 'almacencargas',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id'           => 35,
                'name'         => 'Bancos',
                'route'        => 'bancos',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id'           => 36,
                'name'         => 'Conceptos Transacción',
                'route'        => 'conceptostransaccion',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id'           => 37,
                'name'         => 'Cuentas Bancarias',
                'route'        => 'cuentasbancarias',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id'           => 38,
                'name'         => 'Caja Bancos',
                'route'        => 'cajabancos',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id'           => 39,
                'name'         => 'Cuentas por Pagar',
                'route'        => 'cuentasporpagar',
                'icon'         => 'dot',
                'guard_name'   => 'web',
                'groupMenu_id' => 3,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['id' => $permission['id']], $permission);
        }
    }
}
