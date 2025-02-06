<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $permissions = [
            [
                'id' => 31,
                'name' => 'Unidades',
                'route' => 'unidades',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' =>1,
            ],
            [
                'id' => 32,
                'name' => 'Tarifas',
                'route' => 'tarifas',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 1,
            ],
            [
                'id' => 33,
                'name' => 'Productos',
                'route' => 'productos',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 1,
            ],
            [
                'id' => 34,
                'name' => 'Almacén de Cargas',
                'route' => 'almacencargas',
                'icon' => 'dot',
                'guard_name' => 'web',
                'groupMenu_id' => 1,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['id' => $permission['id']], $permission);
        }
    }
}
