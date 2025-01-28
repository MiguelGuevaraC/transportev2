<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $array = [
            ['id' => '1', 'name' => 'Sucursales', 'route' => 'sucursal', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '2', 'name' => 'Puntos de viaje', 'route' => 'puntos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '3', 'name' => 'Cajas', 'route' => 'cajas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],

            ['id' => '4', 'name' => 'Recepción de Carga', 'route' => 'recepcion', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 2],
            ['id' => '5', 'name' => 'Programación de Viaje', 'route' => 'viajes', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 2],

            ['id' => '6', 'name' => 'Concepto', 'route' => 'concepto', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],
            ['id' => '7', 'name' => 'Caja Chica', 'route' => 'cajachica', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],
            ['id' => '8', 'name' => 'Nota de Crédito', 'route' => 'notacredito', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],

            ['id' => '9', 'name' => 'Personas', 'route' => 'persona', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],
            ['id' => '10', 'name' => 'Trabajadores', 'route' => 'trabajador', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],
            ['id' => '11', 'name' => 'Usuarios', 'route' => 'usuarios', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],
            ['id' => '12', 'name' => 'Gestionar Accesos', 'route' => 'accesos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],

            ['id' => '13', 'name' => 'Logout', 'route' => 'logout', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 5],

            ['id' => '14', 'name' => 'Ventas', 'route' => 'ventas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],
            ['id' => '15', 'name' => 'Conductores', 'route' => 'conductores', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],

            ['id' => '16', 'name' => 'Guias Transporte', 'route' => 'guias', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 2],
            ['id' => '17', 'name' => 'Vehiculos', 'route' => 'vehiculos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],

            ['id' => '18', 'name' => 'Modelos', 'route' => 'modelos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            // ['id' => '19', 'name' => 'Flotas', 'route' => 'flotas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '20', 'name' => 'Rutas', 'route' => 'rutas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '21', 'name' => 'Areas', 'route' => 'areas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '22', 'name' => 'Cuentas por Cobrar', 'route' => 'cuentas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],

            ['id' => '23', 'name' => 'Tipo Carroceria ', 'route' => 'tipocarroceria ', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '24', 'name' => 'Empresa vehiculo', 'route' => 'tipoempresavehiculo', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '25', 'name' => 'Documentos', 'route' => 'documentos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '26', 'name' => 'Cambiar Sucursal', 'route' => 'seleccionar-sucursal', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 5],
       
        ];

        foreach ($array as $object) {
            $typeOfuser1 = Permission::find($object['id']);
            if ($typeOfuser1) {
                $typeOfuser1->update($object);
            } else {
                Permission::create($object);
            }
        }
        Role::find(1)->syncPermissions([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25,26]);
        Role::find(2)->syncPermissions([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25,26]);
        
    }
}
