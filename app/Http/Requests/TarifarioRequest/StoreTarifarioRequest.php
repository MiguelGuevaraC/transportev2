<?php
namespace App\Http\Requests\TarifarioRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
/**
 * @OA\Schema(
 *     schema="TarifarioRequest",
 *     title="TarifarioRequest",
 *     description="Request model for Tarifario information with filters and sorting",
 *     required={"id", "tarifa", "person_id", "destination_id", "origin_id", "unity_id"},
 *     @OA\Property(property="id", type="integer", nullable=true, description="ID of the Tarifario"),
 *     @OA\Property(property="tarifa", type="number", format="float", nullable=true, description="Tarifa amount"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Description of the Tarifario"),
 *     @OA\Property(property="person_id", type="integer", nullable=false, description="ID of the person associated with the Tarifario"),
 *     @OA\Property(property="tarifa_camp", type="number", format="float", nullable=true, description="Tarifa camp amount"),
 *     @OA\Property(property="limitweight_min", type="number", format="float", nullable=true, description="Weight limit min for the Tarifario"),
 *     @OA\Property(property="limitweight_max", type="number", format="float", nullable=true, description="Weight limit max for the Tarifario"),
 *     @OA\Property(property="destination_id", type="integer", nullable=false, description="ID of the destination place"),
 *     @OA\Property(property="origin_id", type="integer", nullable=false, description="ID of the origin place"),
 *     @OA\Property(property="unity_id", type="integer", nullable=false, description="ID of the unity associated with the Tarifario")
 * )
 */

class StoreTarifarioRequest extends StoreRequest
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

    public function rules()
    {
        return [
            'description'     => 'nullable|string',
            'person_id'       => 'required|exists:people,id,deleted_at,NULL', // Asegura que la persona existe
            'tarifa_camp'     => 'nullable|numeric',
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
                        ->where('unity_id', request('unity_id'))
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
            'tarifa.numeric'           => 'La tarifa debe ser un número decimal.',
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


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $data = $this->only([
                'person_id', 'origin_id', 'unity_id', 'destination_id', 'limitweight_min', 'limitweight_max'
            ]);

            // Consulta directa para verificar si ya existe un registro con estos valores
            $exists = DB::table('tarifarios')
                ->where($data)
                ->whereNull('deleted_at') // Ignorar los eliminados
                ->exists();

            if ($exists) {
                $validator->errors()->add('tarifario_existente', 'Ya existe un tarifario con estos datos.');
            }
        });
    }
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
