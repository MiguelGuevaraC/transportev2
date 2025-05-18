<?php

namespace App\Http\Requests\CompraOrderDetailRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreCompraOrderDetailRequest",
 *     title="StoreCompraOrderDetailRequest",
 *     description="Request model for storing a Compra Order detail",
 *     required={"compra_order_id", "repuesto_id", "quantity", "unit_price", "subtotal"},
 *     @OA\Property(property="compra_order_id", type="integer", description="Compra order ID"),
 *     @OA\Property(property="repuesto_id", type="integer", description="Repuesto ID"),
 *     @OA\Property(property="quantity", type="number", format="float", description="Cantidad"),
 *     @OA\Property(property="unit_price", type="number", format="float", description="Precio unitario"),
 *     @OA\Property(property="subtotal", type="number", format="float", description="Subtotal"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario opcional")
 * )
 */
class StoreCompraOrderDetailRequest extends StoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'compra_order_id' => 'required|integer|exists:compra_orders,id',
            'repuesto_id'     => 'required|integer|exists:repuestos,id',
            'quantity'        => 'required|numeric|min:1',
            'unit_price'      => 'required|numeric|min:0',
            'comment'         => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'compra_order_id.required' => 'El ID de la orden de compra es obligatorio.',
            'compra_order_id.exists'   => 'La orden de compra no existe.',
            'repuesto_id.required'     => 'El repuesto es obligatorio.',
            'repuesto_id.exists'       => 'El repuesto no existe.',
            'quantity.required'        => 'La cantidad es obligatoria.',
            'quantity.numeric'         => 'La cantidad debe ser numérica.',
            'unit_price.required'      => 'El precio unitario es obligatorio.',
            'unit_price.numeric'       => 'El precio unitario debe ser numérico.',
            'unit_price.min'           => 'El precio unitario no puede ser negativo.',
            'subtotal.required'        => 'El subtotal es obligatorio.',
            'subtotal.numeric'         => 'El subtotal debe ser numérico.',
            'subtotal.min'             => 'El subtotal no puede ser negativo.',
        ];
    }

    public function attributes()
    {
        return [
            'compra_order_id' => 'orden de compra',
            'repuesto_id'     => 'repuesto',
            'quantity'        => 'cantidad',
            'unit_price'      => 'precio unitario',
            'subtotal'        => 'subtotal',
            'comment'         => 'comentario',
        ];
    }
}
