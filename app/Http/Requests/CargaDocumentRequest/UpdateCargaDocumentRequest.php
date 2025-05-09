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
            'quantity'      => 'required|numeric|min:0',
            'unit_price'    => 'nullable|numeric|min:0',
            'total_cost'    => 'nullable|numeric|min:0',
            'weight'        => 'nullable|numeric|min:0',
            'movement_type' => 'required|string|in:ENTRADA,SALIDA',

            'comment'       => 'nullable|string|max:500',
            'product_id'    => 'required|exists:products,id,deleted_at,NULL',
            'person_id'     => 'required|exists:people,id,deleted_at,NULL',
            'distribuidor_id'     => 'required|exists:people,id,deleted_at,NULL',

            'lote_doc'=>'nullable|string',
            'date_expiration'=>'nullable|date',
            'num_anexo'=>'nullable|string',
            'branchOffice_id'=> 'required|exists:branch_offices,id,deleted_at,NULL',


            'details'              => 'required|array|min:1',
            'details.*.id'         => 'nullable|integer',
            'details.*.quantity'   => 'required|numeric|min:1',
            'details.*.product_id' => 'required|exists:products,id,deleted_at,NULL',
            'details.*.almacen_id' => 'required|exists:almacens,id,deleted_at,NULL',
            'details.*.seccion_id' => 'required|exists:seccions,id,deleted_at,NULL',
            'details.*.comment'    => 'nullable|string|max:500',
            'details.*.num_anexo'    => 'nullable|string|max:500',
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

            'quantity.required'      => 'La cantidad es un campo obligatorio.',
            'quantity.numeric'       => 'La cantidad debe ser un número.',
            'quantity.min'           => 'La cantidad no puede ser menor a 0.',

            'unit_price.required'    => 'El precio unitario es un campo obligatorio.',
            'unit_price.numeric'     => 'El precio unitario debe ser un número.',
            'unit_price.min'         => 'El precio unitario no puede ser menor a 0.',

            'total_cost.required'    => 'El costo total es obligatorio.',
            'total_cost.numeric'     => 'El costo total debe ser un número.',
            'total_cost.min'         => 'El costo total no puede ser menor a 0.',

            'weight.numeric'         => 'El peso debe ser un número.',
            'weight.min'             => 'El peso no puede ser menor a 0.',
            'weight.required'        => 'El peso es obligatorio.',

            'movement_type.required' => 'El tipo de movimiento es obligatorio.',
            'movement_type.string'   => 'El tipo de movimiento debe ser un texto válido.',
            'movement_type.in'       => 'El tipo de movimiento reciebe como opciones ENTRADA y SALIDA',

            'comment.string'         => 'El comentario debe ser un texto.',
            'comment.max'            => 'El comentario no puede superar los 500 caracteres.',

            'product_id.required'    => 'El producto es obligatorio.',
            'product_id.exists'      => 'El producto seleccionado no es válido o ha sido eliminado.',

            'person_id.required'     => 'La persona es obligatoria.',
            'person_id.exists'       => 'La persona seleccionada no es válida o ha sido eliminada.',

            'distribuidor_id.required'     => 'El distribuidor es obligatorio.',
            'distribuidor_id.exists'       => 'El distribuidor seleccionada no es válida o ha sido eliminada.',

            'lote_doc.string' => 'El campo Lote de Documento debe ser una cadena de texto.',
            'date_expiration.date' => 'El campo Fecha de Expiración debe ser una fecha válida.',
            'num_anexo.string' => 'El campo Número de Anexo debe ser una cadena de texto.',

            'details.*.num_anexo.nullable' => 'El número de anexo es opcional.',
        'details.*.num_anexo.string'   => 'El número de anexo debe ser una cadena de texto.',
        'details.*.num_anexo.max'      => 'El número de anexo no puede tener más de 500 caracteres.',
        
        'details.*.date_expiration.nullable' => 'La fecha de expiración es opcional.',
        'details.*.date_expiration.date'     => 'La fecha de expiración debe ser una fecha válida.',
        ];
    }

}
