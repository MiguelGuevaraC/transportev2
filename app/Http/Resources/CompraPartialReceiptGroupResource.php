<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraPartialReceiptGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                             => $this->id,
            'correlativo'                    => 'CPRG-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT),
            'branch_office_id'               => $this->branch_office_id,
            'branch_office_name'             => $this->branchOffice->name ?? null,
            'proveedor_id'                   => $this->proveedor_id,
            'proveedor_name'                 => trim(($this->proveedor->names ?? '') . ' ' . ($this->proveedor->businessName ?? '')),
            'invoice_compra_moviment_id'     => $this->invoice_compra_moviment_id,
            'invoice_compra_moviment_number' => $this->invoiceCompraMoviment->number ?? null,
            'observation'                    => $this->observation,
            'partial_moviments'              => $this->whenLoaded('partialMoviments', function () {
                return $this->partialMoviments->map(fn ($mov) => [
                    'id'               => $mov->id,
                    'correlativo'      => 'CM-' . str_pad((string) $mov->id, 6, '0', STR_PAD_LEFT),
                    'number'           => $mov->number,
                    'document_type'    => $mov->document_type,
                    'date_movement'    => $mov->date_movement,
                    'compra_order_id'  => $mov->compra_order_id,
                    'status'           => $mov->status,
                ]);
            }),
            'created_at'                     => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
