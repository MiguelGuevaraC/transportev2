<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgrammingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id ?? null,
            'numero'                => $this->numero ?? null,
            'departureDate'         => $this->departureDate ?? null,
            'estimatedArrivalDate'  => $this->estimatedArrivalDate ?? null,
            'actualArrivalDate'     => $this->actualArrivalDate ?? null,

            'state'                 => $this->state ?? null,
            'isload'                => $this->isload ?? null,

            'totalWeight'           => $this->totalWeight ?? null,
            'carrierQuantity'       => $this->carrierQuantity ?? null,
            'detailQuantity'        => $this->detailQuantity ?? null,
            'totalAmount'           => $this->totalAmount ?? null,

            'kmStart'               => $this->kmStart ?? null,
            'kmEnd'                 => $this->kmEnd ?? null,
            'totalViaje'            => $this->totalViaje ?? null,
            'totalExpenses'         => $this->totalExpenses ?? null,
            'totalReturned'         => $this->totalReturned ?? null,

            'statusLiquidacion'     => $this->statusLiquidacion ?? null,
            'dateLiquidacion'       => $this->dateLiquidacion ?? null,

            'origin_id'             => $this->origin_id ?? null,
            'origin'             => $this->origin ?? null,
            'destination_id'        => $this->destination_id ?? null,
            'destination'        => $this->destination ?? null,
            'tract_id'              => $this->tract_id ?? null,
            'tract'              => $this->tract ?? null,
            'platForm_id'           => $this->platForm_id ?? null,
            'platForm'           => $this->platForm ?? null,
            'branchOffice_id'       => $this->branchOffice_id ?? null,

            'programming_id'        => $this->programming_id ?? null,
            'status'                => $this->status ?? null,

            'user_edited_id'        => $this->user_edited_id ?? null,
            'user_deleted_id'       => $this->user_deleted_id ?? null,
            'user_created_id'       => $this->user_created_id ?? null,

            'created_at'            => $this->created_at ?? null,
        ];
    }
}
