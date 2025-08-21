<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Tire;

class VehicleTiresResource extends JsonResource
{
    public function toArray($request): array
    {
        $ejes   = $this->ejes ?? 0;
        $wheels = $this->wheels ?? 0;

        // Llantas asignadas desde BD
        $assignedTires = Tire::where('vehicle_id', $this->id)
            ->get()
            ->keyBy('position_vehicle');

        $positions   = [];
        $remaining   = $wheels;
        $posCounter  = 1;

        for ($eje = 1; $eje <= $ejes; $eje++) {
            // cada eje puede tener hasta 4 llantas (2L + 2R)
            $maxPerEje = min(4, $remaining);

            // siempre balancear izquierda/derecha
            $perSide = intdiv($maxPerEje, 2);
            $extra   = $maxPerEje % 2;  // si sobra 1, se asigna a la izquierda

            $ejePositions = [];

            // Izquierda
            for ($i = 1; $i <= $perSide + $extra; $i++) {
                $tire = $assignedTires->get($posCounter);
                $ejePositions[] = [
                    'position' => $posCounter,
                    'assigned' => (bool) $tire,
                    'tire'     => $tire ? new TireResource($tire) : null,
                ];
                $posCounter++;
                $remaining--;
            }

            // Derecha
            for ($i = 1; $i <= $perSide; $i++) {
                if ($remaining <= 0) break;
                $tire = $assignedTires->get($posCounter);
                $ejePositions[] = [
                    'position' => $posCounter,
                    'assigned' => (bool) $tire,
                    'tire'     => $tire ? new TireResource($tire) : null,
                ];
                $posCounter++;
                $remaining--;
            }

            $positions[] = [
                'eje'        => $eje,
                'llantas'    => count($ejePositions),
                'positions'  => $ejePositions,
            ];
        }

        return [
            'id'           => $this->id ?? null,
            'currentPlate' => $this->currentPlate ?? null,
            'ejes'         => $ejes,
            'totalLlantas' => $wheels,
            'distribution' => $positions, // 👈 distribución por eje
        ];
    }
}
