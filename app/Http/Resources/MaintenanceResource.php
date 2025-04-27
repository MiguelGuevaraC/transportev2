<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="MaintenanceDocument",
     *     title="MaintenanceDocument",
     *     description="Maintenance model",
     *     required={"id", "movement_date", "movement_type", "km", "vehicle_id", "taller_id"},
     *
     *     @OA\Property(property="id", type="integer", description="Maintenance ID"),
     *     @OA\Property(property="type", type="string", description="Type of movement (e.g., IN, OUT)"),
     *     @OA\Property(property="mode", type="string", description="Type of movement (same as 'type')"),
     *     @OA\Property(property="km", type="number", format="float", description="Kilometers of the vehicle"),
     *     @OA\Property(property="date_maintenance", type="string", format="date", description="Date of maintenance"),
     *     @OA\Property(property="vehicle_id", type="integer", description="ID of the vehicle"),
     *     @OA\Property(property="taller_id", type="integer", description="ID of the workshop"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date in YYYY-MM-DD HH:MM:SS format")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'                => $this->id ?? null,
            'type'              => $this->type ?? null,
            'mode'              => $this->mode ?? null, // Assuming "mode" is similar to "type"
            'km'                => $this->km ?? null,
            'date_maintenance'  => $this->date_maintenance ?? null,
            'vehicle_id'        => $this->vehicle_id ?? null,
            'taller_id'         => $this->taller_id ?? null,
            'details'         => $this->details() ?? null,
            'created_at'        => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
