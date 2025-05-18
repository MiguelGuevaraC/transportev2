<?php
namespace App\Http\Requests\CompraOrderDetailRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCompraOrderDetailRequest extends UpdateRequest
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
            'compra_order_id' => 'nullable|integer|exists:compra_orders,id',
            'repuesto_id'     => 'nullable|integer|exists:repuestos,id',
            'quantity'        => 'nullable|numeric|min:1',
            'unit_price'      => 'nullable|numeric|min:0',
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
