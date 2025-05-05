<?php
namespace App\Http\Requests\ServiceRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends UpdateRequest
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

     public function rules(): array
     {
         return [
             'name'        => 'nullable|string|max:255',
             'description' => 'nullable|string',
             'status'      => 'nullable|string|in:ACTIVO,INACTIVO',
         ];
     }
 
     public function messages(): array
     {
         return [
             'name.required'        => 'El campo nombre es obligatorio.',
             'name.string'          => 'El campo nombre debe ser una cadena de texto.',
             'name.max'             => 'El campo nombre no debe exceder los 255 caracteres.',
 
             'description.required' => 'El campo descripción es obligatorio.',
             'description.string'   => 'El campo descripción debe ser una cadena de texto.',
 
             'status.required'      => 'El campo estado es obligatorio.',
             'status.string'        => 'El campo estado debe ser una cadena de texto.',
             'status.in'            => 'El estado debe ser "ACTIVO" o "INACTIVO".',
         ];
     }

}
