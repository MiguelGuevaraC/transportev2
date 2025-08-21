<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use Exception;

class PermissionService
{
    /**
     * Crea o actualiza un permiso según name + action.
     *
     * @param  array  $data
     * @return Permission|null
     */
    // App\Services\PermissionService.php
    public function storeOrUpdate(array $data): array
    {
        try {
            if (!isset($data['name']) || !isset($data['action'])) {
                return ['permission' => null, 'created' => false];
            }

            $permission = Permission::updateOrCreate(
                [
                    'name' => $data['name'],
                    'action' => $data['action'],
                ],
                [
                    'route' => isset($data['route']) ? $data['route'] : null,
                    'groupMenu_id' => isset($data['groupMenu_id']) ? $data['groupMenu_id'] : 1,
                    'icon' => isset($data['icon']) ? $data['icon'] : null,
                    'guard_name' => 'web',
                    'state' => true,
                ]
            );

            return [
                'permission' => $permission,
                'created' => $permission->wasRecentlyCreated
            ];
        } catch (\Exception $e) {
            Log::error('Error en storeOrUpdate', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            return ['permission' => null, 'created' => false];
        }
    }

}
