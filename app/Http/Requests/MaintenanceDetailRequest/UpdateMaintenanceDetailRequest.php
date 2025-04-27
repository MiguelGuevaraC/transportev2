<?php
namespace App\Http\Requests\MaintenanceDetailRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateMaintenanceDetailRequest extends UpdateRequest
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
             'name'           => 'nullable|string|max:255',
             'type'           => 'nullable|string|in:PRODUCTO,SERVICIO', // Aquí el IN corregido
             'price'          => 'nullable|numeric',
             'quantity'       => 'nullable|integer|min:0',
             'maintenance_id' => 'nullable|integer|exists:maintenances,id', // Corrijo exist y typo
             'repuesto_id'    => 'nullable|integer|exists:repuestos,id',    // Corrijo exist y typo
         ];
     }
 
     /**
      * Get the custom validation messages.
      *
      * @return array
      */
     public function messages(): array
     {
         return [
             'name.required'            => 'El nombre es obligatorio.',
             'name.string'              => 'El nombre debe ser una cadena de texto.',
             'type.required'             => 'El tipo es obligatorio.',
             'type.string'               => 'El tipo debe ser una cadena de texto.',
             'type.in'                   => 'El tipo debe ser "PRODUCTO" o "SERVICIO".',
             'price.required'            => 'El precio es obligatorio.',
             'price.numeric'             => 'El precio debe ser un valor numérico.',
             'quantity.required'         => 'La cantidad es obligatoria.',
             'quantity.integer'          => 'La cantidad debe ser un número entero.',
             'quantity.min'              => 'La cantidad no puede ser negativa.',
             'maintenance_id.required'   => 'El ID del mantenimiento es obligatorio.',
             'maintenance_id.integer'    => 'El ID del mantenimiento debe ser un número entero.',
             'maintenance_id.exists'     => 'El mantenimiento seleccionado no existe.',
             'repuesto_id.required'      => 'El ID del repuesto es obligatorio.',
             'repuesto_id.integer'       => 'El ID del repuesto debe ser un número entero.',
             'repuesto_id.exists'        => 'El repuesto seleccionado no existe.',
         ];
     }

}
