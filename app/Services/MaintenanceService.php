<?php
namespace App\Services;

use App\Models\Maintenance;
use App\Models\Vehicle;

class MaintenanceService
{
    protected MaintenanceOperationService $operationService;

    public function __construct(MaintenanceOperationService $operationService)
    {
        $this->operationService = $operationService;
    }


    public function getMaintenanceById(int $id): ?Maintenance
    {
        return Maintenance::find($id);
    }

    public function createMaintenance(array $data): Maintenance
    {

        $maintenance = Maintenance::create($data);

        if ($maintenance && $maintenance->vehicle_id) {
            $vehicle = Vehicle::find($maintenance->vehicle_id);
            if ($vehicle) {
                $vehicle->update(['status' => 'Mantenimiento']);
            }
        }

        // Crear operaciones si existen
        if (!empty($data['operations'])) {
            $this->operationService->createMaintenanceOperation($maintenance->id, $data['operations']);
        }
        return $maintenance;
    }

    public function updateMaintenance(Maintenance $maintenance, array $data): Maintenance
    {
        // 1. Actualizar datos base del mantenimiento
        $filteredData = array_intersect_key($data, $maintenance->getAttributes());
        $maintenance->update($filteredData);

        // 2. Actualizar estado del vehículo si finalizado
        if ($maintenance->status === "Finalizado" && $maintenance->vehicle_id) {
            $vehicle = Vehicle::find($maintenance->vehicle_id);
            if ($vehicle) {
                $vehicle->update(['status' => 'Disponible']);
            }
        }

        // 3. Sincronizar operaciones
        if (!empty($data['operations'])) {
            $this->operationService->syncMaintenanceOperations($maintenance, $data['operations']);
        }

        return $maintenance;
    }


    public function destroyById($id)
    {
        return Maintenance::find($id)?->delete() ?? false;
    }

}
