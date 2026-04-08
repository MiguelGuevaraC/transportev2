<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraPartialReceiptGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                             => $this->id,
            'branch_office_id'               => $this->branch_office_id,
            'proveedor_id'                   => $this->proveedor_id,
            'invoice_compra_moviment_id'     => $this->invoice_compra_moviment_id,
            'observation'                    => $this->observation,
            'partial_moviments'              => $this->whenLoaded('partialMoviments', function () {
                return $this->partialMoviments->map(fn ($mov) => [
                    'id'     => $mov->id,
                    'number' => $mov->number,
                ]);
            }),
            'created_at'                     => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
