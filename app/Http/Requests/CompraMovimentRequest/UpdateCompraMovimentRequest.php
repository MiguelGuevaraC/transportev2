<?php
namespace App\Http\Requests\CompraMovimentRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'date_movement'            => 'nullable|date',
            'document_type'            => 'nullable|string|in:boleta,factura,ticket',
            'branchOffice_id'          => 'nullable|integer|exists:branch_offices,id',
            'payment_condition'        => 'nullable|in:CONTADO,CREDITO',
            'serie_doc'                => 'nullable|string|max:4',
            'correlative_doc'          => 'nullable|string|max:8',
            'is_igv_incluide'          => 'nullable|boolean',

            'proveedor_id'             => 'nullable|integer|exists:people,id',
            'compra_order_id'          => 'nullable|integer|exists:compra_orders,id',
            'payment_method'           => 'nullable|string',
            'comment'                  => 'nullable|string',
            'status'                   => 'nullable|string',

            // Validación para el array details
            'details'                  => 'nullable|array|min:1',
            'details.*.repuesto_id'    => 'nullable|integer|exists:repuestos,id',
            'details.*.quantity'       => 'required|integer|min:1',
            'details.*.unit_price'     => 'required|numeric|min:0',

            'details.*.almacen_id'     => 'sometimes|nullable|integer|exists:almacens,id',
            'details.*.seccion_id'     => 'sometimes|nullable|integer|exists:seccions,id',

            'payables'                 => 'nullable|array',
            'payables.*.id'            => 'nullable|integer|exists:payables,id',

            'payables.*.monto'=> 'required|numeric|min:0.01',
            'payables.*.days' => 'nullable|integer|min:0',



        ];
    }

    public function messages()
    {
        return [
            'date_movement.required'         => 'La fecha de movimiento es obligatoria.',
            'date_movement.date'             => 'La fecha de movimiento debe ser una fecha válida.',
            'document_type.required'         => 'El tipo de documento es obligatorio.',
            'document_type.in'               => 'El tipo de documento debe ser boleta, facturac o ticket.',
            'payment_condition.in'           => 'La condición Pago puede ser CONTADO o CREDITO.',

            'branchOffice_id.required'       => 'La sucursal es obligatoria.',
            'branchOffice_id.integer'        => 'La sucursal debe ser un número entero.',
            'branchOffice_id.exists'         => 'La sucursal seleccionada no existe.',

            'serie_doc.max'                  => 'La serie del documento no debe tener más de 4 caracteres.',
            'correlative_doc.max'            => 'El correlativo del documento no debe tener más de 8 caracteres.',
            'is_igv_incluide.required'       => 'Debe especificar si el IGV está incluido o no.',
            'is_igv_incluide.boolean'        => 'El campo IGV incluido debe ser verdadero (true) o falso (false).',

            'person_id.required'             => 'La persona es obligatoria.',
            'person_id.integer'              => 'La persona debe ser un número entero.',
            'person_id.exists'               => 'La persona seleccionada no existe.',

            'proveedor_id.required'          => 'El proveedor es obligatorio.',
            'proveedor_id.integer'           => 'El proveedor debe ser un número entero.',
            'proveedor_id.exists'            => 'El proveedor seleccionado no existe.',

            'compra_order_id.integer'        => 'La orden de compra debe ser un número entero.',
            'compra_order_id.exists'         => 'La orden de compra seleccionada no existe.',

            'payment_method.required'        => 'El método de pago es obligatorio.',

            'details.required'               => 'Debe enviar al menos un detalle.',
            'details.array'                  => 'Los detalles deben enviarse como un arreglo.',
            'details.min'                    => 'Debe enviar al menos un detalle.',
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

            'details.*.almacen_id.integer'   => 'El ID del almacén debe ser un número entero.',
            'details.*.almacen_id.exists'    => 'El almacén seleccionado no existe.',

            'details.*.seccion_id.integer'   => 'El ID de la sección debe ser un número entero.',
            'details.*.seccion_id.exists'    => 'La sección seleccionada no existe.',

            'payables.*.id.required'         => 'Cada cuenta por pagar debe tener un ID.',
            'payables.*.id.integer'          => 'El ID de la cuenta por pagar debe ser un número entero.',
            'payables.*.id.exists'           => 'La cuenta por pagar no existe.',

            'payables.*.monto.required'      => 'Debe ingresar el monto para cada cuenta por pagar.',
            'payables.*.monto.numeric'       => 'El monto debe ser un número.',
            'payables.*.monto.min'           => 'El monto no puede ser negativo.',

             'payables.*.days.required' => 'Debe ingresar los días para cada cuenta por pagar.',
            'payables.*.days.integer'  => 'Los días deben ser un número entero.',
            'payables.*.days.min'      => 'Los días no pueden ser negativos.',

        ];
    }

    public function attributes()
    {
        return [
            'date_movement'         => 'fecha de movimiento',
            'document_type'         => 'tipo de documento',
            'branchOffice_id'       => 'sucursal',
            'person_id'             => 'persona',
            'proveedor_id'          => 'proveedor',
            'compra_order_id'       => 'orden de compra',
            'payment_method'        => 'método de pago',
            'comment'               => 'comentario',
            'status'                => 'estado',
            'details'               => 'detalles',
            'details.*.repuesto_id' => 'ID del repuesto',
            'details.*.quantity'    => 'cantidad',
            'details.*.unit_price'  => 'precio unitario',
            'details.*.comment'     => 'comentario del detalle',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $v) {
            $input = $this->all();

            if ($input['payment_condition'] === 'CREDITO') {
                if (! isset($input['payables']) || ! is_array($input['payables']) || count($input['payables']) === 0) {
                    $v->errors()->add('payables', 'Debe proporcionar al menos una cuenta por pagar cuando la condición de pago es CREDITO.');
                }

                // Validar suma de montos
                $suma = collect($input['payables'] ?? [])->sum('monto');
                if (isset($input['total']) && $suma != $input['total']) {
                    $v->errors()->add('payables', 'La suma de los montos en las cuentas por pagar debe ser igual al total.');
                }
            }
        });
    }

}
