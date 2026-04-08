<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductRequirementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'check_list_id'     => $this->check_list_id,
            'branch_office_id'  => $this->branch_office_id,
            'status'            => $this->status,
            'observation'       => $this->observation,
            'lines'             => $this->whenLoaded('lines', function () {
                return $this->lines->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'check_list_detail_id' => $l->check_list_detail_id,
                        'repuesto_id' => $l->repuesto_id,
                        'repuesto_name' => $l->repuesto?->name,
                        'quantity_requested' => $l->quantity_requested,
                        'observation' => $l->observation,
                    ];
                });
            }),
            'created_at'        => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
