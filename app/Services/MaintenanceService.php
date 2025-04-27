<?php
namespace App\Services;

use App\Models\Maintenance;

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
        $Maintenance = Maintenance::create($data);
        return $Maintenance;
    }

    public function updateMaintenance(Maintenance $Maintenance, array $data): Maintenance
    {
        $filteredData = array_intersect_key($data, $Maintenance->getAttributes());
        $Maintenance->update($filteredData);
        return $Maintenance;
    }

    public function destroyById($id)
    {
        return Maintenance::find($id)?->delete() ?? false;
    }

}
