<?php
namespace App\Http\Requests\SeccionRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateSeccionRequest extends UpdateRequest
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
             'name'       => ['required', 'string'],
             'almacen_id' => ['required', 'integer', 'exists:almacens,id'],
             'status'     => ['nullable', 'string', 'in:Activo,Inactivo'],
         ];
     }
 
     /**
      * Get custom error messages for validator.
      *
      * @return array
      */
     public function messages()
     {
         return [
             'name.required'       => 'El nombre del almacén es obligatorio.',
             'name.string'         => 'El nombre del almacén debe ser una cadena de texto.',
             'almacen_id.required' => 'El ID del almacén es obligatorio.',
             'almacen_id.integer'   => 'El ID del almacén debe ser un número.',
             'almacen_id.exists'   => 'El almacén seleccionado no existe en el sistema.',
 
             'status.string'       => 'El estado del almacén debe ser una cadena de texto.',
             'status.in'           => 'El estado del almacén debe ser "Activo" o "Inactivo".',
         ];
     }

}
