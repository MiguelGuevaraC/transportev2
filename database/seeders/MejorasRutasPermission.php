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
            ['id' => '22', 'type' => 'Transferir', 'typeConcept' => 'Ingreso', 'name' => 'TRANFERENCIA-INGRESO SALDO'],
            ['id' => '23', 'type' => 'Transferir', 'typeConcept' => 'Egreso', 'name' => 'TRANFERENCIA-EGRESO SALDO'],
        ];

        foreach ($conceptosDriverExpenses as $driverexpense) {
            ExpensesConcept::updateOrCreate(['id' => $driverexpense['id']], $driverexpense);
        }


        $grupos = [
            ['id' => '6', 'name' => 'Caja Grande', 'icon' => 'bank'],
            ['id' => '7', 'name' => 'Almacén', 'icon' => 'almacen'],
            ['id' => '8', 'name' => 'Taller', 'icon' => 'taller'],
            ['id' => '9', 'name' => 'Compras', 'icon' => 'compra'],
        ];

        foreach ($grupos as $grupo) {
            GroupMenu::updateOrCreate(['id' => $grupo['id']], $grupo);
        }

        $permissions = [
            [
                'id' => 31,
                'name' => 'Unidades',
                'route' => 'unidades',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id' => 32,
                'name' => 'Tarifas',
                'route' => 'tarifas',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id' => 33,
                'name' => 'Productos',
                'route' => 'productos',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id' => 34,
                'name' => 'Almacén de Cargas',
                'route' => 'almacencargas',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id' => 35,
                'name' => 'Bancos',
                'route' => 'bancos',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id' => 36,
                'name' => 'Conceptos Transacción',
                'route' => 'conceptostransaccion',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id' => 37,
                'name' => 'Cuentas Bancarias',
                'route' => 'cuentasbancarias',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id' => 38,
                'name' => 'Caja Bancos',
                'route' => 'cajabancos',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 6,
            ],
            [
                'id' => 39,
                'name' => 'Cuentas por Pagar',
                'route' => 'cuentasporpagar',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 3,
            ],


            [
                'id' => 40,
                'name' => 'Taller',
                'route' => 'taller',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 41,
                'name' => 'Categorias Repuesto',
                'route' => 'category',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 42,
                'name' => 'Repuestos',
                'route' => 'repuestos',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 43,
                'name' => 'Items Checklist',
                'route' => 'items',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 44,
                'name' => 'Checklist',
                'route' => 'checklist',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 45,
                'name' => 'Mantenimiento',
                'route' => 'mantenimiento',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 46,
                'name' => 'Orden Compra',
                'route' => 'ordencompra',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 9,
            ],
            [
                'id' => 47,
                'name' => 'Compras',
                'route' => 'compras',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 9,
            ],

            [
                'id' => 48,
                'name' => 'Almacenes',
                'route' => 'almacenes',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id' => 49,
                'name' => 'Secciones Almacén',
                'route' => 'secciones',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 7,
            ],
            [
                'id' => 50,
                'name' => 'Neumáticos',
                'route' => 'tire',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],
            [
                'id' => 51,
                'name' => 'Operaciones e Inspecciones',
                'route' => 'tire_operation',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 8,
            ],


        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['id' => $permission['id']], $permission);
        }

        $this->call(SetAccesAdmin::class);
    }
}
