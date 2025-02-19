<?php
namespace App\Http\Requests\TarifarioRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
                'description'     => 'nullable|string',
                'person_id'       => 'required|exists:people,id,deleted_at,NULL', // Asegura que la persona existe
                'tarifa_camp'     => 'nullable|numeric|min:0.01',
                'limitweight_min' => 'required|numeric|gt:0',
                'limitweight_max' => 'required|numeric|gt:0|gte:limitweight_min',
                'destination_id'  => 'required|exists:places,id,deleted_at,NULL',
                'origin_id'       => 'required|exists:places,id,deleted_at,NULL',
                'unity_id'        => 'required|exists:unities,id,deleted_at,NULL',
                'tarifa'          => [
                    'nullable',
                    'numeric',
                    'min:0.01',
                    function ($attribute, $value, $fail) {
                        $exists = DB::table('tarifarios')
                            ->where('person_id', request('person_id'))
                            ->where('origin_id', request('origin_id'))
                            ->where('destination_id', request('destination_id'))
                            ->where(function ($query) {
                                $query->whereBetween('limitweight_min', [request('limitweight_min'), request('limitweight_max')])
                                    ->orWhereBetween('limitweight_max', [request('limitweight_min'), request('limitweight_max')])
                                    ->orWhere(function ($subQuery) {
                                        $subQuery->where('limitweight_min', '<=', request('limitweight_min'))
                                            ->where('limitweight_max', '>=', request('limitweight_max'));
                                    });
                            })
                            ->whereNull('deleted_at')
                            ->when(request()->route('id'), function ($query, $id) {
                                return $query->where('id', '!=', $id); // Ignorar la tarifa actual en la validación
                            })
                            ->exists();
        
                        if ($exists) {
                            $fail('Ya existe una tarifa dentro de este rango de peso.');
                        }
                    },
                ],
            ];
        }
    public function messages()
    {
        return [
            'tarifa.numeric'           => 'La tarifa debe ser decimal.',
            'description.string'       => 'La descripción debe ser un texto válido.',
            'person_id.string'         => 'El ID de la persona debe ser un texto válido.',
            'tarifa_camp.numeric'      => 'La tarifa de campo debe ser un número decimal.',

            'destination_id.required'  => 'El destino es obligatorio.',
            'destination_id.exists'    => 'El destino seleccionado no es válido o no está activo.',
            'origin_id.required'       => 'El origen es obligatorio.',
            'origin_id.exists'         => 'El origen seleccionado no es válido o no está activo.',
            'unity_id.required'        => 'La unidad es obligatoria.',
            'unity_id.exists'          => 'La unidad seleccionada no es válida o no está activa.',
            'unity_id.unique'          => 'La persona ya tiene una tarifa asociada a esta unidad.',
            'tarifa.min'               => 'La tarifa debe ser mayor a 0.',
            'tarifa_camp.min'          => 'La tarifa de campaña debe ser mayor a 0.',
            'limitweight_min.required' => 'El peso mínimo es obligatorio.',
            'limitweight_min.numeric'  => 'El peso mínimo debe ser un número.',
            'limitweight_min.gt'       => 'El peso mínimo debe ser mayor a 0.',
            'limitweight_max.required' => 'El peso máximo es obligatorio.',
            'limitweight_max.numeric'  => 'El peso máximo debe ser un número.',
            'limitweight_max.gt'       => 'El peso máximo debe ser mayor a 0.',
            'limitweight_max.gte'      => 'El peso máximo debe ser mayor o igual al peso mínimo.',

            'tarifa.unique'            => 'Esta tarifa ya está registrada para esta persona con la misma ruta de origen y destino.',
        ];
    }

}
