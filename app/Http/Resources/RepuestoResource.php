<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RepuestoResource extends JsonResource
{

/**
 * @OA\Schema(
 *     schema="Repuesto",
 *     title="Repuesto",
 *     description="Model representing a Repuesto",
 *     @OA\Property(property="id", type="integer", description="Repuesto ID"),
 *     @OA\Property(property="name", type="string", description="Name of the repuesto"),
 *     @OA\Property(property="code", type="string", description="Code of the repuesto"),
 *     @OA\Property(property="price_compra", type="number", format="float", description="Purchase price"),
 *     @OA\Property(property="stock", type="integer", description="Stock quantity"),
 *     @OA\Property(property="status", type="string", description="Status of the repuesto"),
 *     @OA\Property(property="category_id", type="integer", description="Category ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the repuesto")
 * )
 */
public function toArray($request): array
{
    return [
        'id'           => $this->id ?? null,
        'name'         => $this->name ?? null,
        'code'         => $this->code ?? null,
        'price_compra' => $this->price_compra ?? null,
        'stock'        => $this->stock ?? null,
        'status'       => $this->status ?? null,
        'category_id'  => $this->category_id ?? null,
        'category_name'  => $this?->category?->name ?? null,
        'created_at'   => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
    ];
}


}
