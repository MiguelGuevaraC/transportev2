<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AlmacenResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Almacen",
     *     title="Almacen",
     *     description="Model representing an Almacen (warehouse)",
     *     required={"id", "name", "address", "status"},
     *     @OA\Property(property="id", type="integer", description="Almacen ID"),
     *     @OA\Property(property="name", type="string", description="Name of the almacen"),
     *     @OA\Property(property="address", type="string", description="Address of the almacen"),
     *     @OA\Property(property="status", type="string", description="Status of the almacen (e.g., active/inactive)"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the almacen")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'      => $this->id ?? null,

            'name'    => $this->name ?? null,
            'address' => $this->address ?? null,
            'status'  => $this->status ?? null,
           'seccions' => isset($this->seccions)
    ? SeccionResource::collection($this->seccions)
    : null,


            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
