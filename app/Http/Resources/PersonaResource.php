<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'typeofDocument'           => $this->typeofDocument,
            'documentNumber'           => $this->documentNumber,
            'names'                    => $this->names,
            'fatherSurname'            => $this->fatherSurname,
            'motherSurname'            => $this->motherSurname,
            'birthDate'                => $this->birthDate,
            'address'                  => $this->address,
            'places'                   => $this->places,
            'telephone'                => $this->telephone,
            'email'                    => $this->email,
            'daysCredit'               => $this->daysCredit,
            'daysCredit_proveedor'               => $this->daysCredit_proveedor,
            'type'                     => $this->type,

            'businessName'             => $this->businessName,
            'comercialName'            => $this->comercialName,
            'fiscalAddress'            => $this->fiscalAddress,
            'representativePersonDni'  => $this->representativePersonDni,
            'representativePersonName' => $this->representativePersonName,
            'branchOffice_id'          => $this->branchOffice_id,
            'products'                 => $this->products ? ProductResource::collection($this->products) : collect([]),
            'state'                    => $this->state,
        ];
    }
}
