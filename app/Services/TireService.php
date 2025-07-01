<?php
namespace App\Services;

use App\Models\Tire;

class TireService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getTireById(int $id): ?Tire
    {
        return Tire::find($id);
    }

    public function createTire(array $data): Tire
    {
        $taller = Tire::create($data);
        return $taller;
    }

    public function updateTire(Tire $taller, array $data): Tire
    {
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return Tire::find($id)?->delete() ?? false;
    }

}
