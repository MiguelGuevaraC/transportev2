<?php
namespace App\Services;

use App\Models\MaintenanceDetail;

class MaintenanceDetailService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getMaintenanceDetailById(int $id): ?MaintenanceDetail
    {
        return MaintenanceDetail::find($id);
    }

    public function createMaintenanceDetail(array $data): MaintenanceDetail
    {
        $data['price_total'] = ($data['quantity'] ?? 0) * ($data['price'] ?? 0);
        $MaintenanceDetail    = MaintenanceDetail::create($data);
        return $MaintenanceDetail;
    }

    public function updateMaintenanceDetail(MaintenanceDetail $MaintenanceDetail, array $data): MaintenanceDetail
    {
        $data['price_total'] = ($data['quantity'] ?? 0) * ($data['price'] ?? 0);
        $filteredData = array_intersect_key($data, $MaintenanceDetail->getAttributes());
        $MaintenanceDetail->update($filteredData);
        return $MaintenanceDetail;
    }

    public function destroyById($id)
    {
        return MaintenanceDetail::find($id)?->delete() ?? false;
    }

}
