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
            'tarifa'      => 'nullable|string',
            'description' => 'nullable|string',
            'person_id'   => 'nullable|string',
            'unity_id'    => [
                'required',
                'exists:unities,id,deleted_at,NULL', // Verifica que la unidad exista y no esté eliminada
                Rule::unique('tarifarios')
                    ->where('person_id', $this->person_id)
                    ->where('unity_id', $this->unity_id)
                    ->whereNull('deleted_at')     // Verifica que no haya tarifas activas para esa persona y unidad
                    ->ignore($id), // Ignora el registro actual si es una actualización
            ],
        ];
    }

    public function messages()
    {
        return [
            'tarifa.string'      => 'La tarifa debe ser un texto válido.',
            'description.string' => 'La descripción debe ser un texto válido.',
            'person_id.string'   => 'El ID de la persona debe ser un texto válido.',
            'unity_id.required'  => 'La unidad es obligatoria.',
            'unity_id.exists'    => 'La unidad seleccionada no es válida o no está activa.',
            'unity_id.unique'    => 'La persona ya tiene una tarifa asociada a esta unidad.',
        ];
    }

}
