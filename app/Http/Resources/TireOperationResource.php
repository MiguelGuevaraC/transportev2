<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\PersonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\TireResource;
use Carbon\Carbon;

class TireOperationResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="TireOperation",
     *     title="TireOperation",
     *     description="Información de una operación sobre un neumático",
     *     required={"id", "operation_type", "operation_date"},
     * 
     *     @OA\Property(property="id", type="integer", description="ID de la operación"),
     *     @OA\Property(property="operation_type", type="string", description="Tipo de operación"),
     *     @OA\Property(property="position", type="integer", description="Posición del neumático"),
     *     @OA\Property(property="vehicle_km", type="number", format="float", description="Kilometraje del vehículo"),
     *     @OA\Property(property="operation_date", type="string", format="date-time", description="Fecha de la operación"),
     *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario"),
     *     @OA\Property(property="vehicle", ref="#/components/schemas/Vehicle"),
     *     @OA\Property(property="driver", ref="#/components/schemas/Person"),
     *     @OA\Property(property="user", ref="#/components/schemas/User"),
     *     @OA\Property(property="tire", ref="#/components/schemas/Tire"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de actualización")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'operation_type' => $this->operation_type ?? null,
            'position' => $this->position ?? null,
            'vehicle_km' => $this->vehicle_km ?? null,

            'operation_date' => $this->operation_date
                ? Carbon::parse($this->operation_date)->format('Y-m-d H:i:s')
                : null,

            'comment' => $this->comment ?? null,

            'vehicle_id' => $this->vehicle_id ?? null,
            'vehicle' => $this->vehicle ? $this->vehicle : null,
            'driver_id' => $this->driver_id ?? null,
            'driver' => $this->driver ? new WorkerResource($this->driver) : null,
            'user_id' => $this->user_id ?? null,
            'user_name' => $this?->user?->username ?? null,
            'tire_id' => $this->tire_id ?? null,
            'tire' => $this->tire ?? new TireResource($this->tire),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
        ];
    }
}
