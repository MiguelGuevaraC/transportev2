<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeccionResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Seccion",
     *     title="Seccion",
     *     description="Model representing a Seccion (section of a warehouse)",
     *     required={"id", "name", "almacen_id", "status"},
     *     @OA\Property(property="id", type="integer", description="Seccion ID"),
     *     @OA\Property(property="name", type="string", description="Name of the seccion"),
     *     @OA\Property(property="almacen_id", type="integer", description="ID of the associated almacen"),
     *     @OA\Property(property="almacen_name", type="integer", description="Name of the associated almacen"),
     *     @OA\Property(property="status", type="string", description="Status of the seccion (e.g., active/inactive)"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the seccion")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'         => $this->id ?? null,
            'name'       => $this->name ?? null,
            'almacen_id' => $this->almacen_id ?? null,
            'almacen_name'    => $this?->almacen?->name ?? null,
            'status'     => $this->status ?? null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
