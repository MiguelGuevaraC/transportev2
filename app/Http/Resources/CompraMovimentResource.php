<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraMovimentResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="CompraMoviment",
     *     title="CompraMoviment",
     *     description="Model representing a CompraMoviment",
     *     required={
     *         "id", "date_movement", "document_type", "branchOffice_id",
     *         "person_id", "proveedor_id", "status", "payment_method"
     *     },
     *     @OA\Property(property="id", type="integer", description="Compra Moviment ID"),
     *     @OA\Property(property="number", type="string", description="Movement number"),
     *     @OA\Property(property="serie_doc", type="string", description="Series of the document"),
     *     @OA\Property(property="correlative_doc", type="string", description="Correlative number of the document"),
     *     @OA\Property(property="monto_igv", type="number", format="float", description="IGV amount"),
     *     @OA\Property(property="is_igv_incluide", type="boolean", description="Whether IGV is included"),
     *     @OA\Property(property="date_movement", type="string", format="date", description="Date of movement"),
     *     @OA\Property(property="document_type", type="string", description="Type of document"),
     *     @OA\Property(property="branchOffice_id", type="integer", description="Branch office ID"),
     *     @OA\Property(property="proveedor_id", type="integer", description="Proveedor ID"),
     *     @OA\Property(property="compra_order_id", type="integer", nullable=true, description="Compra Order ID"),
     *     @OA\Property(property="compra_order_code", type="string", nullable=true, description="Compra Order Number"),
     *     @OA\Property(property="payment_method", type="string", description="Payment method"),
     *     @OA\Property(property="comment", type="string", nullable=true, description="Comment"),
     *     @OA\Property(property="status", type="string", description="Status of the order"),
     *     @OA\Property(property="total", type="number", format="float", description="Total amount of the movement"),
     *     @OA\Property(property="details", type="array", @OA\Items(ref="#/components/schemas/CompraMovimentDetail")),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the order")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->id ?? null,
            'number'            => $this->number ?? null,
            'serie_doc'         => $this->serie_doc ?? null,
            'correlative_doc'   => $this->correlative_doc ?? null,
            'is_igv_incluide'   => $this->is_igv_incluide ?? null,
            'payment_condition' => $this->payment_condition ?? null,

            'date_movement'     => $this->date_movement ?? null,
            'document_type'     => $this->document_type ?? null,
            'branchOffice_id'   => $this->branchOffice_id ?? null,
            'branchOffice_name' => $this?->branchOffice?->name ?? null,

            'proveedor_id'      => $this->proveedor_id ?? null,
            'proveedor_name'    => trim("{$this->proveedor?->names} {$this->proveedor?->fatherSurname} {$this->proveedor?->motherSurname} {$this->proveedor?->businessName}"),
            
            'compra_order_id'   => $this->compra_order_id ?? null,
            'compra_order_code' => $this->compra_order->number ?? null,
            'payment_method'    => $this->payment_method ?? null,
            'comment'           => $this->comment ?? null,
            'status'            => $this->status ?? null,
            'total'             => $this->details?->sum('subtotal') ?? null,
            'details'           => CompraMovimentDetailResource::collection($this->details ?? collect()),
            'payables'          => collect($this->payables ?? collect())->map(function ($payable) {
                return [
                    'id'         => $payable->id ?? null,
                    'number'     => $payable->number ?? null,
                    'days'       => $payable->days ?? null,
                    'date'       => $payable->date ?? null,
                    'total'      => $payable->total ?? null,
                    'total_debt' => $payable->totalDebt ?? null,
                    'status'     => $payable->status ?? null,
                ];
            }),

            'created_at'        => $this->created_at ?? null,
        ];
    }
}
