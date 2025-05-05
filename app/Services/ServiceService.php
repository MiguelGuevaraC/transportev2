<?php
namespace App\Services;

use App\Models\Service;

class ServiceService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getServiceById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function createService(array $data): Service
    {
        $Service = Service::create($data);
        return $Service;
    }

    public function updateService(Service $Service, array $data): Service
    {
        $filteredData = array_intersect_key($data, $Service->getAttributes());
        $Service->update($filteredData);
        return $Service;
    }

    public function destroyById($id)
    {
        return Service::find($id)?->delete() ?? false;
    }

}
