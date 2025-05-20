<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraOrderDetailResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="CompraOrderDetail",
     *     title="CompraOrderDetail",
     *     description="Model representing a CompraOrder detail",
     *     required={"id", "compra_order_id", "repuesto_id", "quantity", "unit_price", "subtotal"},
     *     @OA\Property(property="id", type="integer", description="Detail ID"),
     *     @OA\Property(property="compra_order_id", type="integer", description="Compra Order ID"),
     *     @OA\Property(property="compra_order_number", type="string", description="Compra Order Number"),
     *     @OA\Property(property="repuesto_id", type="integer", description="Repuesto ID"),
     *     @OA\Property(property="repuesto_name", type="string", description="Repuesto Name"),
     *     @OA\Property(property="quantity", type="number", format="float", description="Quantity"),
     *     @OA\Property(property="unit_price", type="number", format="float", description="Unit Price"),
     *     @OA\Property(property="subtotal", type="number", format="float", description="Subtotal"),
     *     @OA\Property(property="comment", type="string", nullable=true, description="Comment"),
     *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the detail")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,

            'repuesto_id'         => $this->repuesto_id,
            'repuesto_name'       => $this->repuesto?->name,
            'quantity'            => $this->quantity,
            'unit_price'          => $this->unit_price,
            'subtotal'            => $this->subtotal,

            'created_at'          => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
