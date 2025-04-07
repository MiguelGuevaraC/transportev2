<?php
namespace App\Services;

use App\Models\Taller;

class TallerService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getTallerById(int $id): ?Taller
    {
        return Taller::find($id);
    }

    public function createTaller(array $data): Taller
    {
        $taller = Taller::create($data);
        return $taller;
    }

    public function updateTaller(Taller $taller, array $data): Taller
    {
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return Taller::find($id)?->delete() ?? false;
    }

}
