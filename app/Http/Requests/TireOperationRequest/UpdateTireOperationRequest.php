<?php
namespace App\Http\Requests\TireOperationRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTireOperationRequest extends UpdateRequest
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
            'operation_type' => ['required', 'string'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'position' => ['required', 'integer', 'between:1,12'],
            'vehicle_km' => ['nullable', 'numeric'],
            'operation_date' => ['required', 'date'],
            'comment' => ['nullable', 'string'],
            'driver_id' => ['nullable', 'integer', 'exists:workers,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tire_id' => ['required', 'integer', 'exists:tires,id'],
            'presion_aire' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'operation_type.required' => 'El tipo de operación es obligatorio.',
            'operation_type.string' => 'El tipo de operación debe ser texto.',

            'position.required' => 'La posición del neumático es obligatoria.',
            'position.integer' => 'La posición debe ser un número entero.',
            'position.between' => 'La posición debe estar entre 1 y 12.',

            'operation_date.required' => 'La fecha de operación es obligatoria.',
            'operation_date.date' => 'La fecha de operación no es válida.',

            'tire_id.required' => 'El neumático relacionado es obligatorio.',
            'tire_id.exists' => 'El neumático no existe.',

            'vehicle_id.exists' => 'El vehículo no existe.',
            'driver_id.exists' => 'El conductor no existe.',
            'user_id.exists' => 'El usuario no existe.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if (
                strtolower($this->operation_type) === 'asignacion' &&
                $this->tire_id &&
                $this->vehicle_id
            ) {
                $tire = \App\Models\Tire::withTrashed()->find($this->tire_id);
                $currentOperation = $this->route('tire_operation');

                if (
                    $tire &&
                    $tire->vehicle_id &&
                    $tire->vehicle_id != $this->vehicle_id
                ) {
                    $otherVehicle = \App\Models\Vehicle::withTrashed()->find($tire->vehicle_id);

                    if (!$otherVehicle || $otherVehicle->deleted_at === null) {
                        $plate = $otherVehicle->plate ?? 'desconocida';
                        $validator->errors()->add('tire_id', "Este neumático ya está asignado al vehículo con placa {$plate}.");
                    }
                }
            }
        });
    }


}
