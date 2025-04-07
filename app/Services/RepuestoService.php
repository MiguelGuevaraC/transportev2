<?php
namespace App\Services;

use App\Models\Repuesto;

class RepuestoService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getRepuestoById(int $id): ?Repuesto
    {
        return Repuesto::find($id);
    }

    public function createRepuesto(array $data): Repuesto
    {
        $taller = Repuesto::create($data);
        return $taller;
    }

    public function updateRepuesto(Repuesto $taller, array $data): Repuesto
    {
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return Repuesto::find($id)?->delete() ?? false;
    }

}
