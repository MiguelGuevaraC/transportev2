<?php
namespace App\Http\Requests\CompraOrderRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="CompraOrderRequest",
 *     title="CompraOrderRequest",
 *     description="Request model for CompraOrder information with filters and sorting",
 *     required={"date_movement", "branchOffice_id", "person_id", "proveedor_id", "details"},
 *     @OA\Property(property="date_movement", type="string", format="date", description="Fecha del movimiento"),
 *     @OA\Property(property="branchOffice_id", type="integer", description="ID de la sucursal"),
 *     @OA\Property(property="person_id", type="integer", description="ID de la persona"),
 *     @OA\Property(property="proveedor_id", type="integer", description="ID del proveedor"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario opcional"),
 *     @OA\Property(
 *         property="details",
 *         type="array",
 *         description="Lista de detalles de la compra",
 *         @OA\Items(
 *             type="object",
 *             required={"repuesto_id", "quantity", "unit_price"},
 *             @OA\Property(property="repuesto_id", type="integer", description="ID del repuesto"),
 *             @OA\Property(property="quantity", type="number", minimum=1, description="Cantidad"),
 *             @OA\Property(property="unit_price", type="number", format="float", minimum=0, description="Precio unitario"),
 *             @OA\Property(property="comment", type="string", nullable=true, description="Comentario del detalle")
 *         )
 *     )
 * )
 */

class StoreCompraOrderRequest extends StoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date_movement'        => 'required|date',
            'branchOffice_id'      => 'required|integer|exists:branch_offices,id',
            'person_id'            => 'required|integer|exists:people,id',
            'proveedor_id'         => 'required|integer|exists:people,id',
            'comment'              => 'nullable|string',
            'details'              => 'required|array|min:1',
            'details.*.repuesto_id'=> 'required|integer|exists:repuestos,id',
            'details.*.quantity'   => 'required|numeric|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.comment'    => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'date_movement.required'        => 'La fecha de movimiento es obligatoria.',
            'date_movement.date'            => 'La fecha de movimiento debe ser una fecha válida.',
            'branchOffice_id.required'      => 'La sucursal es obligatoria.',
            'branchOffice_id.integer'       => 'La sucursal debe ser un número entero.',
            'branchOffice_id.exists'        => 'La sucursal seleccionada no existe.',
            'person_id.required'            => 'La persona es obligatoria.',
            'person_id.integer'             => 'La persona debe ser un número entero.',
            'person_id.exists'              => 'La persona seleccionada no existe.',
            'proveedor_id.required'         => 'El proveedor es obligatorio.',
            'proveedor_id.integer'          => 'El proveedor debe ser un número entero.',
            'proveedor_id.exists'           => 'El proveedor seleccionado no existe.',

            'details.required'              => 'Debe incluir al menos un detalle.',
            'details.array'                 => 'Los detalles deben ser un arreglo.',
            'details.min'                   => 'Debe incluir al menos un detalle.',

            'details.*.repuesto_id.required'=> 'El repuesto es obligatorio en cada detalle.',
            'details.*.repuesto_id.integer' => 'El ID del repuesto debe ser un número entero.',
            'details.*.repuesto_id.exists'  => 'El repuesto seleccionado no existe.',
            'details.*.quantity.required'   => 'La cantidad es obligatoria en cada detalle.',
            'details.*.quantity.numeric'    => 'La cantidad debe ser un número.',
            'details.*.quantity.min'        => 'La cantidad debe ser al menos 1.',
            'details.*.unit_price.required' => 'El precio unitario es obligatorio en cada detalle.',
            'details.*.unit_price.numeric'  => 'El precio unitario debe ser un número.',
            'details.*.unit_price.min'      => 'El precio unitario no puede ser negativo.',
            'details.*.comment.string'      => 'El comentario del detalle debe ser un texto.',
        ];
    }

    public function attributes()
    {
        return [
            'date_movement'          => 'fecha de movimiento',
            'branchOffice_id'        => 'sucursal',
            'person_id'              => 'persona',
            'proveedor_id'           => 'proveedor',
            'comment'                => 'comentario',
            'details'                => 'detalles',
            'details.*.repuesto_id'  => 'repuesto',
            'details.*.quantity'     => 'cantidad',
            'details.*.unit_price'   => 'precio unitario',
            'details.*.comment'      => 'comentario del detalle',
        ];
    }
}
