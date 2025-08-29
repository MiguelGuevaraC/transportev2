<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Tire;

class VehicleTiresResource extends JsonResource
{
    public function toArray($request): array
    {
        $ejes   = $this->ejes ?? 0;    // 🔹 número real de ejes
        $wheels = $this->wheels ?? 0;  // 🔹 número real de llantas

        // Llantas asignadas desde BD indexadas por position_vehicle
        $assignedTires = Tire::where('vehicle_id', $this->id)
            ->get()
            ->keyBy('position_vehicle');

        $posCounter = 1;
        $remaining  = $wheels;

        $positions = collect();

        /**
         * 🔹 Eje 1 → siempre 2 llantas direccionales
         */
        if ($ejes > 0 && $remaining >= 2) {
            $firstEje = collect(range(1, 2))->map(function () use ($assignedTires, &$posCounter, &$remaining) {
                $tire = $assignedTires->get($posCounter);
                $data = [
                    'position'       => $posCounter,
                    'is_directional' => true,
                    'assigned'       => (bool) $tire,
                    'tire'           => $tire ? new TireResource($tire) : null,
                ];
                $posCounter++;
                $remaining--;
                return $data;
            });

            $positions->push([
                'eje'       => 1,
                'llantas'   => $firstEje->count(),
                'positions' => $firstEje->values(),
            ]);
        }

        /**
         * 🔹 Resto de ejes → cada uno con hasta 4 llantas balanceadas
         */
        $resto = collect(range(2, $ejes))->map(function ($eje) use (&$posCounter, &$remaining, $assignedTires) {
            $maxPerEje = min(4, $remaining);
            $perSide   = intdiv($maxPerEje, 2);
            $extra     = $maxPerEje % 2;

            $izq = collect(range(1, $perSide + $extra))->map(function () use ($assignedTires, &$posCounter, &$remaining) {
                if ($remaining <= 0) return null;
                $tire = $assignedTires->get($posCounter);
                $data = [
                    'position'       => $posCounter,
                    'is_directional' => false,
                    'assigned'       => (bool) $tire,
                    'tire'           => $tire ? new TireResource($tire) : null,
                ];
                $posCounter++;
                $remaining--;
                return $data;
            })->filter();

            $der = collect(range(1, $perSide))->map(function () use ($assignedTires, &$posCounter, &$remaining) {
                if ($remaining <= 0) return null;
                $tire = $assignedTires->get($posCounter);
                $data = [
                    'position'       => $posCounter,
                    'is_directional' => false,
                    'assigned'       => (bool) $tire,
                    'tire'           => $tire ? new TireResource($tire) : null,
                ];
                $posCounter++;
                $remaining--;
                return $data;
            })->filter();

            $ejePositions = $izq->merge($der)->values();

            return [
                'eje'       => $eje,
                'llantas'   => $ejePositions->count(),
                'positions' => $ejePositions,
            ];
        });

        $positions = $positions->merge($resto);

        return [
            'id'           => $this->id ?? null,
            'currentPlate' => $this->currentPlate ?? null,
            'ejes'         => $ejes,   //
            'totalLlantas' => $wheels, //
            'distribution' => $positions->values(),
        ];
    }
}
