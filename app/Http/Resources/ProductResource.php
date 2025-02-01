<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
/**
 * @OA\Schema(
 *     schema="Product",
 *     title="Product",
 *     description="Product model",
 *     required={"id", "description", "stock", "unity"},
 *     @OA\Property(property="id", type="integer", description="Product ID"),
 *     @OA\Property(property="description", type="string", description="Product description"),
 *     @OA\Property(property="stock", type="integer", description="Product stock"),
 *     @OA\Property(property="weight", type="number", format="float", description="Product weight"),
 *     @OA\Property(property="category", type="string", nullable=true, description="Product category"),
 *     @OA\Property(property="unity", type="string", description="Unit of measurement"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date")
 * )
 */

    public function toArray($request): array
    {
        return [
            'id'          => $this->id ?? null,
            'description' => $this->description ?? null,
            'stock'       => $this->stock ?? null,
            'weight'      => $this->weight ?? null,
            'category'    => $this->category ?? null,
            'unity'       => $this->unity ?? null,
            'created_at'  => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
