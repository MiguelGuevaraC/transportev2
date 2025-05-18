<?php
namespace App\Http\Requests\CompraOrderRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\Repuesto;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCompraOrderRequest extends UpdateRequest
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
            'date_movement'         => 'nullable|date',
            'branchOffice_id'       => 'nullable|integer|exists:branch_offices,id',
            'person_id'             => 'nullable|integer|exists:people,id',
            'proveedor_id'          => 'nullable|integer|exists:people,id',
            'comment'               => 'nullable|string',
            'details'               => 'nullable|array|min:1',

            'details.*.repuesto_id' => [
                'nullable',
                'integer',
                'exists:repuestos,id',
                function ($attribute, $value, $fail) {
                    $allRepuestoIds = collect($this->input('details'))
                        ->pluck('repuesto_id')
                        ->filter()
                        ->toArray();

                    if (count(array_keys($allRepuestoIds, $value)) > 1) {
                        // Obtener el nombre del repuesto para un mensaje claro
                        $repuesto = Repuesto::find($value);
                        $nombre   = $repuesto ? $repuesto->name : "ID {$value}";

                        $fail("El repuesto '{$nombre}' está repetido en los detalles.");
                    }
                },
            ],

            'details.*.quantity'    => 'nullable|numeric|min:1',
            'details.*.unit_price'  => 'nullable|numeric|min:0',
            'details.*.comment'     => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'date_movement.required'         => 'La fecha de movimiento es obligatoria.',
            'date_movement.date'             => 'La fecha de movimiento debe ser una fecha válida.',
            'branchOffice_id.required'       => 'La sucursal es obligatoria.',
            'branchOffice_id.integer'        => 'La sucursal debe ser un número entero.',
            'branchOffice_id.exists'         => 'La sucursal seleccionada no existe.',
            'person_id.required'             => 'La persona es obligatoria.',
            'person_id.integer'              => 'La persona debe ser un número entero.',
            'person_id.exists'               => 'La persona seleccionada no existe.',
            'proveedor_id.required'          => 'El proveedor es obligatorio.',
            'proveedor_id.integer'           => 'El proveedor debe ser un número entero.',
            'proveedor_id.exists'            => 'El proveedor seleccionado no existe.',

            'details.required'               => 'Debe incluir al menos un detalle.',
            'details.array'                  => 'Los detalles deben ser un arreglo.',
            'details.min'                    => 'Debe incluir al menos un detalle.',

            'details.*.repuesto_id.required' => 'El repuesto es obligatorio en cada detalle.',
            'details.*.repuesto_id.integer'  => 'El ID del repuesto debe ser un número entero.',
            'details.*.repuesto_id.exists'   => 'El repuesto seleccionado no existe.',
            'details.*.quantity.required'    => 'La cantidad es obligatoria en cada detalle.',
            'details.*.quantity.numeric'     => 'La cantidad debe ser un número.',
            'details.*.quantity.min'         => 'La cantidad debe ser al menos 1.',
            'details.*.unit_price.required'  => 'El precio unitario es obligatorio en cada detalle.',
            'details.*.unit_price.numeric'   => 'El precio unitario debe ser un número.',
            'details.*.unit_price.min'       => 'El precio unitario no puede ser negativo.',
            'details.*.comment.string'       => 'El comentario del detalle debe ser un texto.',
        ];
    }

    public function attributes()
    {
        return [
            'date_movement'         => 'fecha de movimiento',
            'branchOffice_id'       => 'sucursal',
            'person_id'             => 'persona',
            'proveedor_id'          => 'proveedor',
            'comment'               => 'comentario',
            'details'               => 'detalles',
            'details.*.repuesto_id' => 'repuesto',
            'details.*.quantity'    => 'cantidad',
            'details.*.unit_price'  => 'precio unitario',
            'details.*.comment'     => 'comentario del detalle',
        ];
    }

}
