<?php
namespace App\Services;

use App\Models\TireOperation;

class TireOperationService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getTireOperationById(int $id): ?TireOperation
    {
        return TireOperation::find($id);
    }

public function createTireOperation(array $data): TireOperation
{
    $operation = TireOperation::create($data);

    // Si es asignación y tiene vehicle_id, tire_id y position_vehicle
    if (
        isset($data['operation_type'], $data['vehicle_id'], $data['tire_id']) &&
        strtolower($data['operation_type']) === 'asignacion'
    ) {
        $operation->tire?->update([
            'vehicle_id' => $data['vehicle_id'],
            'position_vehicle' => $data['position'], // Asegúrate de que el campo se llame 'position' en el modelo Tire
        ]);
    }

    return $operation;
}



   public function updateTireOperation(TireOperation $taller, array $data): TireOperation
{
    $filteredData = array_intersect_key($data, $taller->getAttributes());
    $taller->update($filteredData);

    // Si se actualizó a tipo Asignación, también actualiza el vehículo en el neumático
    if (
        isset($data['operation_type'], $data['vehicle_id']) &&
        strtolower($data['operation_type']) === 'asignacion'
    ) {
        $taller->tire?->update(['vehicle_id' => $data['vehicle_id']]);
    }

    return $taller;
}

    public function destroyById($id)
    {
        return TireOperation::find($id)?->delete() ?? false;
    }

}
