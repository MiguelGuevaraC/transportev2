<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverExpenseResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id'                 => $this->id ?? null,
            'place'              => $this->place ?? null,
            'date_expense'       => $this->date_expense ?? null,
            'operationNumber'    => $this->operationNumber ?? null,
            'igv'                => $this->igv ?? null,
            'gravado'            => $this->gravado ?? null,
            'exonerado'          => $this->exonerado ?? null,
            'selectTypePay'      => $this->selectTypePay ?? null,
            'type_payment'      => $this->type_payment ?? null,
            'total'              => $this->total ?? null,
            'routeFact'          => $this->routeFact ?? null,
            'gallons'            => $this->gallons ?? null,
            'amount'             => $this->amount ?? null,
            'quantity'           => $this->quantity ?? null,
            'km'                 => $this->km ?? null,
            'isMovimentCaja'     => $this->isMovimentCaja ?? null,
            'comment'            => $this->comment ?? null,
            'programming_id'     => $this->programming_id ?? null,
            'bank_id'            => $this->bank_id ?? null,
            'worker_id'          => $this->worker_id ?? null,
            'expensesConcept_id' => $this->expensesConcept_id ?? null,
            'expenses_concept'   => $this->expensesConcept ? $this->expensesConcept : null,
            'proveedor_id'       => $this->proveedor_id ?? null,
            'created_at'         => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
