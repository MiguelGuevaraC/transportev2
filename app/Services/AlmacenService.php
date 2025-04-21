<?php
namespace App\Services;

use App\Models\Almacen;

class AlmacenService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getAlmacenById(int $id): ?Almacen
    {
        return Almacen::find($id);
    }

    public function createAlmacen(array $data): Almacen
    {
        $data['status']='Activo';
        $taller = Almacen::create($data);
        return $taller;
    }

    public function updateAlmacen(Almacen $taller, array $data): Almacen
    {
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return Almacen::find($id)?->delete() ?? false;
    }

}
