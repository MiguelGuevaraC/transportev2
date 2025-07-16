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
        $tire = Tire::create($data);
        return $tire;
    }

    public function updateTire(Tire $tire, array $data): Tire
    {
        $filteredData = array_intersect_key($data, $tire->getAttributes());
        $tire->update($filteredData);
        return Tire::find($tire->id);
    }
    public function destroyById($id)
    {
        return Tire::find($id)?->delete() ?? false;
    }

}
