<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PersonResource;
use App\Models\Brand;
use App\Models\Design;
use App\Models\Material;

class TireResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="Tire",
     *     title="Tire",
     *     description="Model representing a Tire",
     *     required={"id", "code", "entry_date"},
     * 
     *     @OA\Property(property="id", type="integer", description="Tire ID"),
     *     @OA\Property(property="code", type="string", description="Código único del neumático"),
     *     @OA\Property(property="condition", type="string", description="Condición: Nuevo, Usado, Reencauchado"),
     *     @OA\Property(property="retread_number", type="integer", description="Número de reencauche"),
     *     @OA\Property(property="entry_date", type="string", format="date", description="Fecha de ingreso"),
     *     @OA\Property(property="supplier", ref="#/components/schemas/Person", description="Proveedor asociado"),
     *     @OA\Property(property="material", type="string", description="Material del neumático"),
     *     @OA\Property(property="brand", type="string", description="Marca del neumático"),
     *     @OA\Property(property="design", type="string", description="Diseño del neumático"),
     *     @OA\Property(property="type", type="string", description="Tipo de neumático"),
     *     @OA\Property(property="size", type="string", description="Medida del neumático"),
     *     @OA\Property(property="dot", type="string", description="Fecha de fabricación (DOT)"),
     *     @OA\Property(property="tread_type", type="string", description="Tipo de banda"),
     *     @OA\Property(property="current_tread", type="number", format="float", description="Cocada actual"),
     *     @OA\Property(property="minimum_tread", type="number", format="float", description="Cocada mínima permitida"),
     *     @OA\Property(property="tread", type="number", format="float", description="Cocada inicial o general"),
     *     @OA\Property(property="shoulder1", type="number", format="float", description="Ribete 1"),
     *     @OA\Property(property="shoulder2", type="number", format="float", description="Ribete 2"),
     *     @OA\Property(property="shoulder3", type="number", format="float", description="Ribete 3"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de actualización")
     * )
     */



public function toArray($request): array
{
    return [
        'id' => $this->id ?? null,
        'code' => $this->code ?? null,
        'stock' => $this->stock ?? 0,
        'condition' => $this->condition ?? null,
        'retread_number' => $this->retread_number ?? null,
        'entry_date' => $this->entry_date ?? null,

        'supplier_id' => $this->supplier_id ?? null,
        'supplier' => $this->supplier ?? new PersonaResource($this->supplier),
'material_id' => $this->material_id ?? null,
'material' => $this->material ? new MaterialResource($this->material) : null,

'design_id' => $this->design_id ?? null,
'design' => $this->design ? new DesignResource($this->design) : null,

'brand_id' => $this->brand_id ?? null,
'brand' => $this->brand ? new BrandResource($this->brand) : null,

        'vehicle_id' => $this->vehicle_id ?? null,
        'vehicle' => $this->vehicle ? $this->vehicle : null,

        'type' => $this->type ?? null,
        'size' => $this->size ?? null,
        'dot' => $this->dot ?? null,
        'tread_type' => $this->tread_type ?? null,
        'current_tread' => $this->current_tread ?? null,
        'minimum_tread' => $this->minimum_tread ?? null,
        'tread' => $this->tread ?? null,
        'shoulder1' => $this->shoulder1 ?? null,
        'shoulder2' => $this->shoulder2 ?? null,
        'shoulder3' => $this->shoulder3 ?? null,

        'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
        'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
    ];
}

}
