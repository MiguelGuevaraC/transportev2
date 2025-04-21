<?php
namespace App\Services;

use App\Models\Seccion;

class SeccionService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getSeccionById(int $id): ?Seccion
    {
        return Seccion::find($id);
    }

    public function createSeccion(array $data): Seccion
    {
        $data['status']='Activo';
        $taller = Seccion::create($data);
        return $taller;
    }

    public function updateSeccion(Seccion $taller, array $data): Seccion
    {
        
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return Seccion::find($id)?->delete() ?? false;
    }

}
