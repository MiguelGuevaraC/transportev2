<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompraMovimentDetailResource extends JsonResource
{
/**
 * @OA\Schema(
 *     schema="CompraMovimentDetail",
 *     title="CompraMovimentDetail",
 *     description="Model representing a CompraMoviment detail",
 *     required={"id", "compra_order_id", "repuesto_id", "quantity", "unit_price", "subtotal"},
 *
 *     @OA\Property(property="id", type="integer", description="Detail ID"),
 *     @OA\Property(property="compra_order_id", type="integer", description="Compra Moviment ID"),
 *     @OA\Property(property="compra_order_number", type="string", description="Compra Moviment Number"),
 *     @OA\Property(property="repuesto_id", type="integer", description="Repuesto ID"),
 *     @OA\Property(property="repuesto_name", type="string", description="Repuesto Name"),
 *     @OA\Property(property="quantity", type="number", format="float", description="Quantity"),
 *     @OA\Property(property="unit_price", type="number", format="float", description="Unit Price"),
 *     @OA\Property(property="subtotal", type="number", format="float", description="Subtotal"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date of the detail")
 * )
 */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id ?? null,
            'repuesto_id'   => $this->repuesto_id ?? null,
            'repuesto_name' => $this->repuesto?->name ?? null,

            'almacen_id'    => $this->almacen_id ?? null,
            'almacen_name'       => $this?->almacen?->name ?? null,
            'seccion_id'    => $this->seccion_id ?? null,
            'seccion_name'       => $this?->seccion?->name ?? null,
            'payment_condition'       => $this?->payment_condition ?? null,

            'quantity'      => $this->quantity ?? null,
            'unit_price'    => $this->unit_price ?? null,
            'subtotal'      => $this->subtotal ?? null,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s') ?? null,
        ];
    }
}
