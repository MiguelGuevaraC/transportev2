<?php
namespace App\Services;

use App\Models\Maintenance;
use App\Models\MaintenanceOperation;

class MaintenanceOperationService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getMaintenanceOperationById(int $id): ?MaintenanceOperation
    {
        return MaintenanceOperation::find($id);
    }

    public function createMaintenanceOperation(int $maintenanceId, array $operations): void
    {
        collect($operations)
            ->map(fn($op) => array_merge($op, ['maintenance_id' => $maintenanceId]))
            ->each(fn($opData) => MaintenanceOperation::create($opData));
    }

    public function updateMaintenanceOperation(MaintenanceOperation $MaintenanceOperation, array $data): MaintenanceOperation
    {
        $filteredData = array_intersect_key($data, $MaintenanceOperation->getAttributes());
        $MaintenanceOperation->update($filteredData);
        return $MaintenanceOperation;
    }

    public function syncMaintenanceOperations(Maintenance $maintenance, array $operations): void
    {
        // Obtener los IDs enviados
        $idsFromRequest = collect($operations)
            ->filter(fn($op) => !empty($op['id']))
            ->pluck('id')
            ->toArray();

        // Eliminar operaciones que no están en la nueva lista
        $maintenance->maintenance_operations()
            ->whereNotIn('id', $idsFromRequest)
            ->delete();

        // Crear o actualizar
        collect($operations)->each(function ($op) use ($maintenance) {
            if (!empty($op['id'])) {
                // Actualizar existente
                MaintenanceOperation::where('id', $op['id'])
                    ->where('maintenance_id', $maintenance->id)
                    ->update([
                        'type_moviment' => $op['type_moviment'],
                        'name' => $op['name'],
                        'quantity' => $op['quantity'],
                        'unity' => $op['unity'],
                    ]);
            } else {
                // Crear nuevo
                MaintenanceOperation::create([
                    'maintenance_id' => $maintenance->id,
                    'type_moviment' => $op['type_moviment'],
                    'name' => $op['name'],
                    'quantity' => $op['quantity'],
                    'unity' => $op['unity'],
                ]);
            }
        });
    }


    public function destroyById($id)
    {
        return MaintenanceOperation::find($id)?->delete() ?? false;
    }

}
