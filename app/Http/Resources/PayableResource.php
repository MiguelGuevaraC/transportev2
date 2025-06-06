<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayableResource extends JsonResource
{

    /**
     * @OA\Schema(
     *     schema="Payable",
     *     title="Payable",
     *     description="Model representing a Payable",
     *     required={"id", "name", "number", "days", "date", "total", "totalDebt", "driver_expense_id", "created_at"},
     *     @OA\Property(property="id", type="integer", description="Payable ID"),
     *     @OA\Property(property="name", type="string", description="Name of the unity"),
     *     @OA\Property(property="number", type="string", description="Payable number"),
     *     @OA\Property(property="days", type="integer", description="Number of days"),
     *     @OA\Property(property="date", type="string", format="date", description="Payable date"),
     *     @OA\Property(property="total", type="number", format="float", description="Total amount"),
     *     @OA\Property(property="totalDebt", type="number", format="float", description="Total debt amount"),
     *     @OA\Property(property="driver_expense_id", type="integer", description="Driver expense ID"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the unity")
     * )
     */

    public function toArray($request): array
    {
        return [
            'id'                 => $this->id ?? null,
            'number'             => $this->number ?? null,
            'days'               => $this->days ?? null,
            'date'               => $this->date ?? null,
            'total'              => $this->total ?? null,
            'totalDebt'          => $this->totalDebt ?? null,
            'status'             => $this->status ?? null,

            'person_id'          => $this->person_id ?? null,
            'person'             => $this->person ?? null,
            'anticipos_proveedor_con_saldo'             => $this->person->anticipos_proveedor_con_saldo ?? [],
            'correlativo_ref'    => $this->correlativo_ref ?? null,
            'type_document_id'   => $this->type_document_id ?? null,
            'type_document_name' => $this?->type_document?->name ?? null,
            'type_payable'       => $this->type_payable ?? null,

            'pay_payables'       => PayPayableResource::collection(collect($this->payPayables)),
            'driver_expense_id'  => $this->driver_expense_id ?? null,
            'driver_expense'     => $this->driver_expense ? array_merge(
                $this->driver_expense->toArray(),
                [
                    'programming' => $this->driver_expense->programming ? $this->driver_expense->programming->toArray() : null,
                ]
            ) : null,
               'compra_moviment_id'  => $this->compra_moviment_id ?? null,
               'compra_moviment_number'  => $this?->compra_moviment?->number ?? null,
            'created_at'         => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
