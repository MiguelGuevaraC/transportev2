<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetAccesAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::all();
        // Obtener los IDs de los permisos
        $permissionIds = $permissions->pluck('id')->toArray();
        Role::find(1)->syncPermissions($permissionIds);
        Role::find(2)->syncPermissions($permissionIds);
    }
}
