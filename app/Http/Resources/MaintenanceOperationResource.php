<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceOperationResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="MaintenanceOperation",
     *     title="Maintenance Operation",
     *     description="Maintenance Operation model",
     *     required={"id", "name", "type_moviment", "quantity", "unity", "maintenance_id"},
     *
     *     @OA\Property(property="id", type="integer", description="Maintenance Operation ID"),
     *     @OA\Property(property="type_moviment", type="string", description="Type of movement (e.g., preventivo, correctivo)"),
     *     @OA\Property(property="name", type="string", description="Name of the item used"),
     *     @OA\Property(property="quantity", type="number", format="float", description="Quantity used"),
     *     @OA\Property(property="unity", type="string", description="Unit of measure (e.g., liters, units)"),
     *     @OA\Property(property="maintenance_id", type="integer", description="Related maintenance ID"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date in YYYY-MM-DD HH:MM:SS format")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id'             => $this->id ?? null,
            'type_moviment'  => $this->type_moviment ?? null,
            'name'           => $this->name ?? null,
            'quantity'       => $this->quantity ?? null,
            'unity'          => $this->unity ?? null,
            'maintenance_id' => $this->maintenance_id ?? null,
            'created_at'     => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
