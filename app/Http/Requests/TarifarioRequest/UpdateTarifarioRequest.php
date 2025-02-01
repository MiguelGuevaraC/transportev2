<?php
namespace App\Http\Requests\TarifarioRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateTarifarioRequest extends UpdateRequest
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
            'tarifa'       => 'nullable|string',
            'quantity'     => 'nullable|string',
            'description'  => 'nullable|string',
            'unity'        => 'nullable|string',
            'person_id'    => 'nullable|string',
            'product_id'   => 'required|exists:products,id,deleted_at,NULL',
            'unity_id'     => 'required|exists:unities,id,deleted_at,NULL',
        ];
    }
    
    public function messages()
    {
        return [
            'tarifa.string'       => 'La tarifa debe ser un texto válido.',
            'quantity.string'     => 'La cantidad debe ser un texto válido.',
            'description.string'  => 'La descripción debe ser un texto válido.',
            'unity.string'        => 'La unidad debe ser un texto válido.',
            'person_id.string'    => 'El ID de la persona debe ser un texto válido.',
            
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists'   => 'El producto seleccionado no es válido o ha sido eliminado.',
            
            'unity_id.required'   => 'La unidad es obligatoria.',
            'unity_id.exists'     => 'La unidad seleccionada no es válida o ha sido eliminada.',
        ];
    }
    

}
