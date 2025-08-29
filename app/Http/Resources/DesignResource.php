<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DesignResource",
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="state", type="boolean")
 * )
 */
class DesignResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'brand_id' => $this->brand_id ?? null,
            'brand' => $this->brand?->name ?? null,
            //'state' => $this->state ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
