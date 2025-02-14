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
        $id = $this->route('id'); // Obtén el ID de la ruta, que se asume que es el ID del usuario

            return [
                'description'    => 'nullable|string',
                'person_id'      => 'required|exists:people,id,deleted_at,NULL', // Asegura que la persona existe
                'tarifa_camp'    => 'nullable|numeric|min:0.01', 
                'limitweight'    => 'required|numeric|gt:0',    
                'destination_id' => [
                    'required',
                    'exists:places,id,deleted_at,NULL',
                ],
                'origin_id'      => 'required|exists:places,id,deleted_at,NULL',
                'unity_id'       => [
                    'required',
                    'exists:unities,id,deleted_at,NULL',
                ],
                // Validar que la persona no tenga la misma combinación de origen y destino
                'tarifa' => [
                    'nullable',
                    'numeric',
                    'min:0.01',
                    Rule::unique('tarifarios')
                        ->where('person_id', $this->person_id)
                        ->where('origin_id', $this->origin_id)
                        ->where('destination_id', $this->destination_id)
                        ->whereNull('deleted_at')
                        ->ignore($this->route('id')),
                ],
            ];
        }
        

    public function messages()
    {
        return [
            'tarifa.numeric'       => 'La tarifa debe ser decimal.',
            'description.string' => 'La descripción debe ser un texto válido.',
            'person_id.string'   => 'El ID de la persona debe ser un texto válido.',
            'tarifa_camp.numeric'     => 'La tarifa de campo debe ser un número decimal.',
            'limitweight.numeric'     => 'El peso límite debe ser un número decimal.',
            'destination_id.required' => 'El destino es obligatorio.',
            'destination_id.exists'   => 'El destino seleccionado no es válido o no está activo.',
            'origin_id.required'      => 'El origen es obligatorio.',
            'origin_id.exists'        => 'El origen seleccionado no es válido o no está activo.',
            'unity_id.required'  => 'La unidad es obligatoria.',
            'unity_id.exists'    => 'La unidad seleccionada no es válida o no está activa.',
            'unity_id.unique'    => 'La persona ya tiene una tarifa asociada a esta unidad.',
            'tarifa.min' => 'La tarifa debe ser mayor a 0.',
            'tarifa_camp.min' => 'La tarifa de campaña debe ser mayor a 0.',
            'limitweight.required' => 'El peso límite es obligatorio.',
            'limitweight.gt' => 'El peso límite debe ser mayor a 0.',
            'tarifa.unique' => 'Esta tarifa ya está registrada para esta persona con la misma ruta de origen y destino.',
        ];
    }

}
