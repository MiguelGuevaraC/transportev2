<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TireMeasureResource",
 *     @OA\Property(property="name", type="string")
 *     @OA\Property(property="status", type="boolean")
 * )
 */
class TireMeasureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            // 'status' => $this->status ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s')
        ];
    }
}