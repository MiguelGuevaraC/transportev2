<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraOrderResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="CompraOrder",  // <-- Verifica si este schema corresponde a este recurso
     *     title="CompraOrder",
     *     description="Model representing a CompraOrder",
     *     required={"id", "number", "date_movement", "branchOffice_id", "person_id", "proveedor_id", "status"},
     *     @OA\Property(property="id", type="integer", description="Compra Order ID"),
     *     @OA\Property(property="number", type="string", description="Order number"),
     *     @OA\Property(property="date_movement", type="string", format="date", description="Date of movement"),
     *     @OA\Property(property="branchOffice_id", type="integer", description="Branch office ID"),
     *     @OA\Property(property="person_id", type="integer", description="Person ID"),
     *     @OA\Property(property="proveedor_id", type="integer", description="Proveedor ID"),
     *     @OA\Property(property="comment", type="string", description="Comment"),
     *     @OA\Property(property="status", type="string", description="Status of the order"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the order")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->id ?? null,
            'number'            => $this->number ?? null,
            'date_movement'     => $this->date_movement ?? null,
            'branchOffice_id'   => $this->branchOffice_id ?? null,
            'branchOffice_name' => $this?->branchOffice?->name ?? null,
            'person_id'         => $this->person_id ?? null,
            'person_names'      => trim("{$this->person?->names} {$this->person?->fatherSurname} {$this->person?->motherSurname}"),
            'proveedor_id'      => $this->proveedor_id ?? null,
            'proveedor_name'    => trim("{$this->proveedor?->names} {$this->proveedor?->fatherSurname} {$this->proveedor?->motherSurname} {$this->proveedor?->businessName}"),
            'total'             => $this->details?->sum('subtotal') ?? null,
            'details'           => $this->details ?? null,
            'comment'           => $this->comment ?? null,
            'status'            => $this->status ?? null,
            'created_at'        => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
