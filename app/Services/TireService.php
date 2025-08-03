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

    private function generateTireCode(array $data): string
    {
        // Solo genera código si la condición es NUEVO o el campo 'code' está vacío
        if (
            (isset($data['condition']) && strtoupper($data['condition']) === 'NUEVO') ||
            empty($data['code'])
        ) {
            return 'TIRE-' . now()->format('Ymd-Hi');
        }

        // Devuelve el código original si no cumple las condiciones anteriores
        return $data['code'];
    }


    public function createTire(array $data): Tire
    {
        $data['code'] = $this->generateTireCode($data);
        $tire = Tire::create($data);
        return $tire;
    }

    public function updateTire(Tire $tire, array $data): Tire
    {
        $data['code'] = $this->generateTireCode($data);
        $filteredData = array_intersect_key($data, $tire->getAttributes());
        $tire->update($filteredData);
        return Tire::find($tire->id);
    }
    public function destroyById($id)
    {
        return Tire::find($id)?->delete() ?? false;
    }

}
