<?php
namespace App\Services;

use App\Models\Tarifario;

class TarifarioService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getTarifarioById(int $id): ?Tarifario
    {
        return Tarifario::find($id);
    }

    public function createTarifario(array $data): Tarifario
    {
        $proyect = Tarifario::create($data);
        return $proyect;
    }

    public function updateTarifario(Tarifario $proyect, array $data): Tarifario
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return Tarifario::find($id)?->delete() ?? false;
    }

}
