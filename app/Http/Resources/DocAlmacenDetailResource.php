<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DocAlmacenDetailResource",
 *     @OA\Property(property="doc_almacen_id", type="integer")
 *     @OA\Property(property="tire_id", type="integer")
 *     @OA\Property(property="quantity", type="integer")
 *     @OA\Property(property="previous_quantity", type="integer")
 *     @OA\Property(property="new_quantity", type="integer")
 *     @OA\Property(property="unit_price", type="decimal")
 *     @OA\Property(property="total_value", type="decimal")
 * )
 */
class DocAlmacenDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'correlativo' => str_pad($this->id ?? 0, 8, '0', STR_PAD_LEFT),

            'doc_almacen_id' => $this->doc_almacen_id ?? null,
            'doc_almacen_correlativo' => str_pad($this->doc_almacen_id ?? 0, 8, '0', STR_PAD_LEFT),
            'tire_id' => $this->tire_id ?? null,
             'tire_nombre' => "🏷️".$this->tire?->code ?? null,
            'quantity' => $this->quantity ?? null,
            // 'previous_quantity' => $this->previous_quantity ?? null,
            // 'new_quantity' => $this->new_quantity ?? null,
            'unit_price' => $this->unit_price ?? null,
            'total_value' => $this->total_value ?? null,
            'note' => $this->note ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s')
        ];
    }
}