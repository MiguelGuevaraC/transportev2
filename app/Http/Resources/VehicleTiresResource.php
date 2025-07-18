<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Tire;
use Illuminate\Support\Collection;

class VehicleTiresResource extends JsonResource
{

    public function toArray($request): array
    {
        $nroPorEje=4;
        $totalTires = ($this->ejes ?? 0) * $nroPorEje;

        $assignedTires = Tire::where('vehicle_id', $this->id)
            ->get()
            ->keyBy('position_vehicle');

        $tires = collect(range(1, $totalTires))->map(function ($position) use ($assignedTires) {
            $tire = $assignedTires->get($position);

            return [
                'position' => $position,
                'assigned' => (bool) $tire,
                'tire' => $tire ? new TireResource($tire) : null,
            ];
        })->values(); // `values()` para asegurar que los índices estén ordenados 0,1,2,...

        return [
            'id' => $this->id ?? null,
            'currentPlate' => $this->currentPlate ?? null,
            'ejes' => $this->ejes ?? null,
            'positions' => $this->ejes*$nroPorEje ?? null,
            'tires' => $tires,
        ];
    }
}
