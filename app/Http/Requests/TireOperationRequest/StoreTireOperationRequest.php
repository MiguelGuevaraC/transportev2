<?php

namespace App\Http\Requests\TireOperationRequest;

use Illuminate\Validation\Validator;
use App\Http\Requests\StoreRequest;
use App\Models\Vehicle;

/**
 * @OA\Schema(
 *     schema="TireOperationRequest",
 *     title="TireOperationRequest",
 *     description="Datos necesarios para registrar una operación de neumático",
 *     required={"operation_type", "position", "operation_date", "tire_id"},
 * 
 *     @OA\Property(property="operation_type", type="string", description="Tipo de operación (Asignación, Cambio, Reparación, etc.)"),
 *     @OA\Property(property="vehicle_id", type="integer", nullable=true, description="ID del vehículo asignado"),
 *     @OA\Property(property="position", type="integer", description="Posición del neumático (1 a 12)"),
 *     @OA\Property(property="vehicle_km", type="number", format="float", nullable=true, description="Kilometraje del vehículo"),
 *     @OA\Property(property="operation_date", type="string", format="date-time", description="Fecha de la operación"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario"),
 *     @OA\Property(property="driver_id", type="integer", nullable=true, description="ID del conductor"),
 *     @OA\Property(property="user_id", type="integer", nullable=true, description="ID del usuario que registra"),
 *     @OA\Property(property="tire_id", type="integer", description="ID del neumático relacionado")
 * )
 */
class StoreTireOperationRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'operation_type' => ['required', 'string'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'position' => ['required', 'integer', 'min:0'],
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
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            if (strtolower($this->operation_type) === 'asignacion') {
                $tire = \App\Models\Tire::withTrashed()->find($this->tire_id);

                if ($tire) {
                    // Verifica si está asignado a otro vehículo activo
                    if ($tire->vehicle_id && $tire->vehicle_id != $this->vehicle_id) {
                        $otherVehicle = \App\Models\Vehicle::withTrashed()->find($tire->vehicle_id);
                        if ($otherVehicle && is_null($otherVehicle->deleted_at)) {
                            $plate = $otherVehicle->plate ?? 'desconocida';
                            $validator->errors()->add('tire_id', "Este neumático ya está asignado a otro vehículo con placa {$plate}.");
                        }
                    }

                    // Verifica si ya está asignado al mismo vehículo en alguna posición
                    if ($tire->vehicle_id == $this->vehicle_id && $tire->position_vehicle) {
                        $validator->errors()->add('tire_id', "Este neumático ya está asignado en la posición {$tire->position_vehicle} de este mismo vehículo.");
                    }
                    $vehicle = Vehicle::find($this->vehicle_id);

                    // 🚫 Validar que la posición esté dentro del rango permitido por los ejes del vehículo
                    if ($vehicle->ejes > 0) {
                       

                        if ($vehicle->ejes) {
                            $maxPosition = $vehicle->ejes * 4;
                            if ( $this->position> $maxPosition) {
                                $validator->errors()->add('position', "Este vehículo tiene {$vehicle->ejes} ejes, por lo tanto solo permite posiciones de 1 a {$maxPosition}.");
                            }

                        } else {
                            $validator->errors()->add('position', "Este vehículo no tiene ejes registrado");
                        }
                    }
                }
            }
        });
    }




}
