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
 *     required={"id", "description", "stock", "unity_id", "person_id"},
 *
 *     @OA\Property(property="id", type="integer", description="Product ID"),
 *     @OA\Property(property="description", type="string", description="Product description"),
 *     @OA\Property(property="stock", type="integer", description="Product stock"),
 *     @OA\Property(property="weight", type="number", format="float", description="Product weight"),
 *     @OA\Property(property="category", type="string", nullable=true, description="Product category"),
 *     @OA\Property(property="addressproduct", type="string", nullable=true, description="Product address"),
 *     @OA\Property(property="codeproduct", type="string", nullable=true, description="Product code"),
 * 
 *     @OA\Property(property="unity_id", type="integer", description="Unit of measurement ID"),
 *     @OA\Property(property="unity", ref="#/components/schemas/Unity"),
 *     @OA\Property(property="person_id", type="integer", description="Person ID associated with the product"),

 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date in YYYY-MM-DD HH:MM:SS format")
 * )
 */

    public function toArray($request): array
    {
        return [
            'id'             => $this->id ?? null,
            'description'    => $this->description ?? null,
            'stock'          => $this->stock ?? null,
            'weight'         => $this->weight ?? null,
            'category'       => $this->category ?? null,
            'addressproduct' => $this->addressproduct ?? null,
            'codeproduct'    => $this->codeproduct ?? null,

            'unity_id'       => $this->unity_id ?? null,
            'unity'          => $this->unity ? new UnityResource($this->unity) : null,
            'person_id'      => $this->person_id ?? null,

            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
