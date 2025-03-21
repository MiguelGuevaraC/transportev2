<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id ?? null,
            'code'              => $this->code ?? null,
            'department'        => $this->department ?? null,
            'province'          => $this->province ?? null,
            'district'          => $this->district ?? null,
    
            'maritalStatus'     => $this->maritalStatus ?? null,
            'levelInstitution'  => $this->levelInstitution ?? null,
            'occupation'        => $this->occupation ?? null,
            'licencia'          => $this->licencia ?? null,
            'licencia_date'     => $this->licencia_date ?? null,
            'pathPhoto'         => $this->pathPhoto ?? null,
    
            'status'            => $this->status ?? null,
            'startDate'         => $this->startDate ?? null,
            'endDate'           => $this->endDate ?? null,
            'state'             => $this->state ?? null,
    
            'district_id'       => $this->district_id ?? null,
            'person_id'         => $this->person_id ?? null,
            'person'            => $this->person ?? null,
            'area_id'           => $this->area_id ?? null,
            'branchOffice_id'   => $this->branchOffice_id ?? null,
        ];
    }

}
