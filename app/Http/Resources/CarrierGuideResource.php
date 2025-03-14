<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarrierGuideResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id ?? null,
            'status'                => $this->status ?? null,
            'document'              => $this->document ?? null,
            'numero'                => $this->numero ?? null,
            'observation'           => $this->observation ?? null,
            'type'                  => $this->type ?? null,
            'modalidad'             => $this->modalidad ?? null,
            'number'                => $this->number ?? null,
            'serie'                 => $this->serie ?? null,
            'motivo'                => $this->motivo ?? null,
            'status_facturado'      => $this->status_facturado ?? null,
            'codemotivo'            => $this->codemotivo ?? null,
            'placa'                 => $this->placa ?? null,

            'transferStartDate'     => $this->transferStartDate ?? null,
            'transferDateEstimated' => $this->transferDateEstimated ?? null,

            'tract_id'              => $this->tract_id ?? null,
            'tract'              => $this->tract ?? null,
            'platform_id'           => $this->platform_id ?? null,
            'platform'           => $this->platform ?? null,
            'origin_id'             => $this->origin_id ?? null,
            'origin'             => $this->origin ?? null,
            'destination_id'        => $this->destination_id ?? null,
            'destination'        => $this->destination ?? null,

            'sender_id'             => $this->sender_id ?? null,
            'sender'             => $this->sender ?? null,
            'recipient_id'          => $this->recipient_id ?? null,
            'recipient'          => $this->recipient ?? null,
            'programming_id'        => $this->programming_id ?? null,

            'districtStart_id'      => $this->districtStart_id ?? null,
            'districtStart'      => $this->districtStart ?? null,
            'districtEnd_id'        => $this->districtEnd_id ?? null,
'districtEnd'        => $this->districtEnd ?? null,
            'reception_id'          => $this->reception_id ?? null,
            'payResponsible_id'     => $this->payResponsible_id ?? null,
            'driver_id'             => $this->driver_id ?? null,
            'copilot_id'            => $this->copilot_id ?? null,
            'subcontract_id'        => $this->subcontract_id ?? null,
            'costsubcontract'       => $this->costsubcontract ?? null,
            'datasubcontract'       => $this->datasubcontract ?? null,

            'branchOffice_id'       => $this->branchOffice_id ?? null,

            'ubigeoStart'           => $this->ubigeoStart ?? null,
            'ubigeoEnd'             => $this->ubigeoEnd ?? null,
            'addressStart'          => $this->addressStart ?? null,
            'addressEnd'            => $this->addressEnd ?? null,
            'motive_id'             => $this->motive_id ?? null,

            'programming'              => new ProgrammingResource ($this->programming) ?? null,

            'user_edited_id'        => $this->user_edited_id ?? null,
            'user_deleted_id'       => $this->user_deleted_id ?? null,
            'user_created_id'       => $this->user_created_id ?? null,
            'user_factured_id'      => $this->user_factured_id ?? null,

            'created_at'            => $this->created_at ?? null,
        ];
    }
}
