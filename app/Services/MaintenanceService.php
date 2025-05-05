<?php
namespace App\Services;

use App\Models\Maintenance;
use App\Models\Vehicle;

class MaintenanceService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
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

        return $maintenance;
    }

    public function updateMaintenance(Maintenance $Maintenance, array $data): Maintenance
    {
        $filteredData = array_intersect_key($data, $Maintenance->getAttributes());
        $Maintenance->update($filteredData);
        if ($Maintenance->status == "Finalizado") {
            $vehicle = Vehicle::find($Maintenance->vehicle_id);
            $vehicle->update(['status' => 'Disponible']);
        }
        return $Maintenance;
    }

    public function destroyById($id)
    {
        return Maintenance::find($id)?->delete() ?? false;
    }

}
