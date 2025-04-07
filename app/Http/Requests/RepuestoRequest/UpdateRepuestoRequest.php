<?php
namespace App\Http\Requests\RepuestoRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateRepuestoRequest extends UpdateRequest
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
             'name'         => 'required|string|max:255',
             'price_compra' => 'required|numeric|min:0',
             'category_id'  => 'required|exists:categories,id,deleted_at,NULL',
             'status'       => 'nullable|string|in:ACTIVO,INACTIVO',
         ];
     }
 
     public function messages(): array
     {
         return [
             'name.string'          => 'El nombre debe ser una cadena de texto.',
             'name.max'             => 'El nombre no debe superar los 255 caracteres.',
             'price_compra.numeric' => 'El precio de compra debe ser un valor numérico.',
             'price_compra.min'     => 'El precio de compra no puede ser negativo.',
             'category_id.exists'   => 'La categoría seleccionada no existe o está eliminada.',
             'status.in'   => 'El estado solo acepta ACTIVO, INACTIVO.',
         ];
     }

}
