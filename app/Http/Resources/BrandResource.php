<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BrandResource",
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="state", type="boolean")
 * )
 */
class BrandResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'state' => $this->state ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}