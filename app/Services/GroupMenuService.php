<?php

namespace App\Services;

use App\Models\GroupMenu;
use Illuminate\Support\Facades\Log;

class GroupMenuService
{
    public function getGroupMenuById(int $id): ?GroupMenu
    {
        try {
            return GroupMenu::find($id);
        } catch (\Throwable $e) {
            Log::error('Error al obtener GroupMenu por ID', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function storeOrUpdateByName(array $data): ?GroupMenu
    {
        try {
            // Buscamos si existe por "name"
            $instance = GroupMenu::where('name', $data['name'] ?? null)->first();

            if ($instance) {
                // 🔑 Update con fillable
                $allowed = array_intersect_key($data, array_flip($instance->getFillable()));
                $instance->update($allowed);
                return $instance;
            } else {
                // 🔑 Store
                return GroupMenu::create($data);
            }
        } catch (\Throwable $e) {
            Log::error('Error en storeOrUpdate GroupMenu por name', [
                'name' => $data['name'] ?? null,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function updateGroupMenu(GroupMenu $instance, array $data): ?GroupMenu
    {
        try {
            // 🔑 Solo atributos definidos como fillable
            $allowed = array_intersect_key($data, array_flip($instance->getFillable()));

            $instance->update($allowed);

            return $instance;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar GroupMenu', [
                'id' => $instance->id ?? null,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function destroyById($id): bool
    {
        try {
            $group = GroupMenu::find($id);

            if (!$group) {
                return false;
            }

            return $group->delete();
        } catch (\Throwable $e) {
            Log::error('Error al eliminar GroupMenu', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
