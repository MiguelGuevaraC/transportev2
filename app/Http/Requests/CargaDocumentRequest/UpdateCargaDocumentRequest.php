<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCargaDocumentRequest extends UpdateRequest
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
            'movement_date' => 'required|date',
            'branchOffice_id'           => 'required|exists:branch_offices,id,deleted_at,NULL',
            'person_id'                 => 'required|exists:people,id,deleted_at,NULL',
            'distribuidor_id'           => 'required|exists:people,id,deleted_at,NULL',
            'movement_type' => 'required|string|in:ENTRADA,SALIDA',
            'comment'       => 'nullable|string|max:500',

            'details'              => 'required|array|min:1',
            'details.*.id'         => 'nullable|integer',
            'details.*.quantity'   => 'required|numeric|min:1',
            'details.*.product_id' => 'required|exists:products,id,deleted_at,NULL',
            'details.*.almacen_id' => 'required|exists:almacens,id,deleted_at,NULL',
            'details.*.seccion_id' => 'required|exists:seccions,id,deleted_at,NULL',
            'details.*.comment'    => 'nullable|string|max:500',
            'details.*.num_anexo'    => 'nullable|string|max:500',
            'details.*.num_lot'    => 'nullable|string|max:500',
            'details.*.date_expiration'    => 'nullable|date',
        ];
    }

    /**
     * Obtener los mensajes de error personalizados para la validación.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'movement_date.required' => 'La fecha del movimiento es obligatoria.',
            'movement_date.date'     => 'La fecha del movimiento debe ser una fecha válida.',

           'branchOffice_id.required'           => 'La Sucursal es obligatoria.',
            'branchOffice_id.exists'             => 'La Sucursal seleccionada no es válida o ha sido eliminada.',

            'person_id.required'                 => 'La persona es obligatoria.',
            'person_id.exists'                   => 'La persona seleccionada no es válida o ha sido eliminada.',

            'distribuidor_id.required'           => 'El distribuidor es obligatorio.',
            'distribuidor_id.exists'             => 'El distribuidor seleccionado no es válido o ha sido eliminado.',

            'movement_type.required'             => 'El tipo de movimiento es obligatorio.',
            'movement_type.string'               => 'El tipo de movimiento debe ser un texto válido.',
            'movement_type.in'                   => 'El tipo de movimiento debe ser ENTRADA o SALIDA.',

            'comment.string'                     => 'El comentario debe ser un texto.',
            'comment.max'                        => 'El comentario no puede superar los 500 caracteres.',

            'details.required'                   => 'Debe ingresar al menos un detalle.',
            'details.array'                      => 'El campo detalles debe ser un arreglo.',

            'details.*.quantity.required'        => 'La cantidad es obligatoria.',
            'details.*.quantity.numeric'         => 'La cantidad debe ser numérica.',
            'details.*.quantity.min'             => 'La cantidad debe ser al menos 1.',

            'details.*.product_id.required'      => 'El producto es obligatorio.',
            'details.*.product_id.exists'        => 'El producto seleccionado no es válido o ha sido eliminado.',

            'details.*.almacen_id.required'      => 'El almacén es obligatorio.',
            'details.*.almacen_id.exists'        => 'El almacén seleccionado no es válido o ha sido eliminado.',

            'details.*.seccion_id.required'      => 'La sección es obligatorio.',

            'details.*.seccion_id.exists'        => 'La sección seleccionada no es válida o ha sido eliminada.',

            'details.*.comment.string'           => 'El comentario del detalle debe ser un texto.',
            'details.*.comment.max'              => 'El comentario del detalle no puede superar los 500 caracteres.',

            'details.*.num_anexo.nullable'       => 'El número de anexo es opcional.',
            'details.*.num_anexo.string'         => 'El número de anexo debe ser una cadena de texto.',
            'details.*.num_anexo.max'            => 'El número de anexo no puede tener más de 500 caracteres.',

            'details.*.num_lot.nullable'       => 'El número de lote es opcional.',
            'details.*.num_lot.string'         => 'El número de lote debe ser una cadena de texto.',
            'details.*.num_lot.max'            => 'El número de lote no puede tener más de 500 caracteres.',

            'details.*.date_expiration.nullable' => 'La fecha de expiración es opcional.',
            'details.*.date_expiration.date'     => 'La fecha de expiración debe ser una fecha válida.',
        ];
    }

}
