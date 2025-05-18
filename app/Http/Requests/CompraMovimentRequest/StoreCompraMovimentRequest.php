<?php
namespace App\Http\Requests\CompraMovimentRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="CompraMovimentRequest",
 *     title="CompraMovimentRequest",
 *     description="Request model for CompraMoviment information",
 *     required={"date_movement", "document_type", "branchOffice_id", "person_id", "proveedor_id", "payment_method", "status", "details"},
 *     @OA\Property(property="date_movement", type="string", format="date", description="Fecha del movimiento"),
 *     @OA\Property(property="document_type", type="string", description="Tipo de documento"),
 *     @OA\Property(property="branchOffice_id", type="integer", description="ID de la sucursal"),
 *     @OA\Property(property="person_id", type="integer", description="ID de la persona"),
 *     @OA\Property(property="proveedor_id", type="integer", description="ID del proveedor"),
 *     @OA\Property(property="compra_order_id", type="integer", nullable=true, description="ID de la orden de compra (opcional)"),
 *     @OA\Property(property="payment_method", type="string", description="Método de pago"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario"),
 *     @OA\Property(property="status", type="string", description="Estado"),
 *     @OA\Property(
 *         property="details",
 *         type="array",
 *         description="Detalles del movimiento de compra",
 *         @OA\Items(
 *             type="object",
 *             required={"repuesto_id", "quantity", "unit_price"},
 *             @OA\Property(property="repuesto_id", type="integer", description="ID del repuesto"),
 *             @OA\Property(property="quantity", type="integer", description="Cantidad"),
 *             @OA\Property(property="unit_price", type="number", format="float", description="Precio unitario"),
 *             @OA\Property(property="comment", type="string", nullable=true, description="Comentario del detalle"),
 *         )
 *     )
 * )
 */
class StoreCompraMovimentRequest extends StoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date_movement'   => 'required|date',
            'document_type'   => 'required|string',
            'branchOffice_id' => 'required|integer|exists:branch_offices,id',
            'person_id'       => 'required|integer|exists:people,id',
            'proveedor_id'    => 'required|integer|exists:people,id',
            'compra_order_id' => 'nullable|integer|exists:compra_orders,id',
            'payment_method'  => 'required|string',
            'comment'         => 'nullable|string',
            'status'          => 'nullable|string',

            // Validación para el array details
            'details'             => 'required|array|min:1',
            'details.*.repuesto_id' => 'required|integer|exists:repuestos,id',
            'details.*.quantity'    => 'required|integer|min:1',
            'details.*.unit_price'  => 'required|numeric|min:0',
            'details.*.comment'     => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'date_movement.required'   => 'La fecha de movimiento es obligatoria.',
            'date_movement.date'       => 'La fecha de movimiento debe ser una fecha válida.',
            'document_type.required'   => 'El tipo de documento es obligatorio.',

            'branchOffice_id.required' => 'La sucursal es obligatoria.',
            'branchOffice_id.integer'  => 'La sucursal debe ser un número entero.',
            'branchOffice_id.exists'   => 'La sucursal seleccionada no existe.',

            'person_id.required'       => 'La persona es obligatoria.',
            'person_id.integer'        => 'La persona debe ser un número entero.',
            'person_id.exists'         => 'La persona seleccionada no existe.',

            'proveedor_id.required'    => 'El proveedor es obligatorio.',
            'proveedor_id.integer'     => 'El proveedor debe ser un número entero.',
            'proveedor_id.exists'      => 'El proveedor seleccionado no existe.',

            'compra_order_id.integer'  => 'La orden de compra debe ser un número entero.',
            'compra_order_id.exists'   => 'La orden de compra seleccionada no existe.',

            'payment_method.required'  => 'El método de pago es obligatorio.',

            'details.required'         => 'Debe enviar al menos un detalle.',
            'details.array'            => 'Los detalles deben enviarse como un arreglo.',
            'details.min'              => 'Debe enviar al menos un detalle.',
            'details.*.repuesto_id.required' => 'El ID del repuesto es obligatorio en cada detalle.',
            'details.*.repuesto_id.integer'  => 'El ID del repuesto debe ser un número entero.',
            'details.*.repuesto_id.exists'   => 'El repuesto seleccionado no existe.',
            'details.*.quantity.required'    => 'La cantidad es obligatoria en cada detalle.',
            'details.*.quantity.integer'     => 'La cantidad debe ser un número entero.',
            'details.*.quantity.min'         => 'La cantidad debe ser al menos 1.',
            'details.*.unit_price.required'  => 'El precio unitario es obligatorio en cada detalle.',
            'details.*.unit_price.numeric'   => 'El precio unitario debe ser un número válido.',
            'details.*.unit_price.min'       => 'El precio unitario no puede ser negativo.',
            'details.*.comment.string'       => 'El comentario debe ser texto.',
        ];
    }

    public function attributes()
    {
        return [
            'date_movement'   => 'fecha de movimiento',
            'document_type'   => 'tipo de documento',
            'branchOffice_id' => 'sucursal',
            'person_id'       => 'persona',
            'proveedor_id'    => 'proveedor',
            'compra_order_id' => 'orden de compra',
            'payment_method'  => 'método de pago',
            'comment'         => 'comentario',
            'status'          => 'estado',
            'details'         => 'detalles',
            'details.*.repuesto_id' => 'ID del repuesto',
            'details.*.quantity'    => 'cantidad',
            'details.*.unit_price'  => 'precio unitario',
            'details.*.comment'     => 'comentario del detalle',
        ];
    }
}
