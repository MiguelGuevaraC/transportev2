<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceDetailResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="MaintenanceDetail",
     *     title="Maintenance Detail",
     *     description="Maintenance Detail model",
     *     required={"id", "name", "type", "price", "quantity", "maintenance_id", "repuesto_id"},
     *
     *     @OA\Property(property="id", type="integer", description="Maintenance Detail ID"),
     *     @OA\Property(property="name", type="string", description="Name of the spare part"),
     *     @OA\Property(property="type", type="string", description="Type of maintenance (PROPIO, EXTERNO)"),
     *     @OA\Property(property="price", type="number", format="float", description="Price of the spare part"),
     *     @OA\Property(property="quantity", type="integer", description="Quantity used"),
     *     @OA\Property(property="maintenance_id", type="integer", description="ID of the maintenance"),
     *     @OA\Property(property="repuesto_id", type="integer", description="ID of the spare part"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date in YYYY-MM-DD HH:MM:SS format")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'             => $this->id ?? null,
            'name'           => $this->name ?? null,
            'type'           => $this->type ?? null,
            'price'          => $this->price ?? null,
            'price_total'          => $this->price_total ?? null,
            'quantity'       => $this->quantity ?? null,
            'maintenance_id' => $this->maintenance_id ?? null,
            'repuesto_id'    => $this->repuesto_id ?? null,
            'repuesto_name'    => $this->repuesto->name ?? null,
            'created_at'     => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
