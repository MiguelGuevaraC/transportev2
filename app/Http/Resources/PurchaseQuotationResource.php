<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseQuotationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                      => $this->id,
            'product_requirement_id'  => $this->product_requirement_id,
            'proveedor_id'            => $this->proveedor_id,
            'proveedor_name'          => trim("{$this->proveedor?->names} {$this->proveedor?->businessName}"),
            'is_winner'               => (bool) $this->is_winner,
            'status'                  => $this->status,
            'comment'                 => $this->comment,
            'lines'                   => $this->whenLoaded('lines', function () {
                return $this->lines->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'repuesto_id' => $l->repuesto_id,
                        'repuesto_name' => $l->repuesto?->name,
                        'quantity' => $l->quantity,
                        'unit_price' => $l->unit_price,
                        'subtotal' => $l->subtotal,
                    ];
                });
            }),
            'created_at'              => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
