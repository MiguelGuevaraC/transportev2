<?php
namespace App\Http\Requests\CompraMovimentRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCompraMovimentRequest extends UpdateRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

     public function rules()
    {
        return [
            'date_movement'   => 'nullable|date',
            'document_type'   => 'nullable|string',
            'branchOffice_id' => 'nullable|integer|exists:branch_offices,id',
            'person_id'       => 'nullable|integer|exists:people,id',
            'proveedor_id'    => 'nullable|integer|exists:people,id',
            'compra_order_id' => 'nullable|integer|exists:compra_orders,id',
            'payment_method'  => 'nullable|string',
            'comment'         => 'nullable|string',
            'status'          => 'nullable|string',

            // Validación para el array details
            'details'             => 'nullable|array|min:1',
            'details.*.repuesto_id' => 'nullable|integer|exists:repuestos,id',
            'details.*.quantity'    => 'nullable|integer|min:1',
            'details.*.unit_price'  => 'nullable|numeric|min:0',
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
