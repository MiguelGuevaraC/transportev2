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
     *     @OA\Property(property="date_movement", type="string", format="date", description="Date of movement"),
     *     @OA\Property(property="document_type", type="string", description="Type of document"),
     *     @OA\Property(property="branchOffice_id", type="integer", description="Branch office ID"),
     *     @OA\Property(property="person_id", type="integer", description="Person ID"),
     *     @OA\Property(property="proveedor_id", type="integer", description="Proveedor ID"),
     *     @OA\Property(property="compra_order_id", type="integer", nullable=true, description="Compra Order ID"),
     *     @OA\Property(property="payment_method", type="string", description="Payment method"),
     *     @OA\Property(property="comment", type="string", nullable=true, description="Comment"),
     *     @OA\Property(property="status", type="string", description="Status of the order"),
     *     @OA\Property(property="total", type="number", format="float", description="Total amount of the movement"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the order")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->id ?? null,
              'number'   => $this->number ?? null,
            'date_movement'   => $this->date_movement ?? null,
            'document_type'   => $this->document_type ?? null,
            'branchOffice_id' => $this->branchOffice_id ?? null,
            'person_id'       => $this->person_id ?? null,
            'proveedor_id'    => $this->proveedor_id ?? null,
            'compra_order_id' => $this->compra_order_id ?? null,
            'compra_order_code' => $this->compra_order->number ?? null,
            'payment_method'  => $this->payment_method ?? null,
            'comment'         => $this->comment ?? null,
            'status'          => $this->status ?? null,
            'total'           => $this->details?->sum('subtotal') ?? null,
            'details'           => $this->details ?? null,
            'created_at'      => $this->created_at ?? null,
        ];
    }
}
