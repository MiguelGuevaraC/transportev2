<?php

namespace App\Http\Requests\MaintenanceRequest;

use App\Http\Requests\StoreRequest;
use App\Models\Vehicle;

/**
 * @OA\Schema(
 *     schema="MaintenanceRequest",
 *     title="MaintenanceRequest",
 *     description="Request model for Maintenance information with filters and sorting",
 *     required={"type", "mode", "km", "date_maintenance", "vehicle_id", "taller_id"},
 *     @OA\Property(property="type", type="string", maxLength=255, description="Tipo de mantenimiento (PROPIO o EXTERNO)"),
 *     @OA\Property(property="mode", type="string", maxLength=255, description="Modo de mantenimiento (CORRECTIVO o PREVENTIVO)"),
 *     @OA\Property(property="km", type="integer", description="Kilometraje del vehículo"),
 *     @OA\Property(property="date_maintenance", type="string", format="date", description="Fecha de mantenimiento"),
 *     @OA\Property(property="vehicle_id", type="integer", description="ID del vehículo"),
 *     @OA\Property(property="taller_id", type="integer", description="ID del taller"),
 *     @OA\Property(
 *         property="maintenance_operations",
 *         type="array",
 *         description="Detalle opcional de operaciones de mantenimiento",
 *         @OA\Items(
 *             @OA\Property(property="type_moviment", type="string", description="Tipo de movimiento (CORRECTIVO o PREVENTIVO)"),
 *             @OA\Property(property="name", type="string", description="Nombre del insumo o tarea"),
 *             @OA\Property(property="quantity", type="number", format="float", description="Cantidad"),
 *             @OA\Property(property="unity", type="string", description="Unidad de medida")
 *         )
 *     )
 * )
 */
class StoreMaintenanceRequest extends StoreRequest
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
            'type'             => 'required|string|in:PROPIO,EXTERNO',
            'mode'             => 'required|string|in:CORRECTIVO,PREVENTIVO',
            'km'               => 'required|integer|min:0',
            'date_maintenance' => 'required|date',
            'vehicle_id'       => [
                'required',
                'integer',
                'exists:vehicles,id',
                function ($attribute, $value, $fail) {
                    $vehicle = Vehicle::find($value);
                    if ($vehicle && $vehicle->status === 'Mantenimiento') {
                        $fail('El vehículo ya se encuentra en mantenimiento.');
                    }
                },
            ],
            'taller_id'        => 'required|integer|exists:tallers,id',

            // Campo opcional de operaciones
            'maintenance_operations'               => 'nullable|array',
            'maintenance_operations.*.type_moviment' => 'required_with:maintenance_operations|string|in:CORRECTIVO,PREVENTIVO',
            'maintenance_operations.*.name'        => 'required_with:maintenance_operations|string|max:255',
            'maintenance_operations.*.quantity'    => 'required_with:maintenance_operations|numeric|min:0',
            'maintenance_operations.*.unity'       => 'required_with:maintenance_operations|string|max:100',
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
            'type.required'             => 'El tipo de mantenimiento es obligatorio.',
            'type.string'               => 'El tipo de mantenimiento debe ser una cadena de texto.',
            'type.in'                   => 'El tipo de mantenimiento debe ser "PROPIO" o "EXTERNO".',
            'mode.required'             => 'El modo de mantenimiento es obligatorio.',
            'mode.string'               => 'El modo de mantenimiento debe ser una cadena de texto.',
            'mode.in'                   => 'El modo de mantenimiento debe ser "CORRECTIVO" o "PREVENTIVO".',
            'km.required'               => 'El kilometraje es obligatorio.',
            'km.integer'                => 'El kilometraje debe ser un número entero.',
            'km.min'                    => 'El kilometraje no puede ser negativo.',
            'date_maintenance.required' => 'La fecha de mantenimiento es obligatoria.',
            'date_maintenance.date'     => 'La fecha de mantenimiento debe ser una fecha válida.',
            'vehicle_id.required'       => 'El vehículo es obligatorio.',
            'vehicle_id.integer'        => 'El ID del vehículo debe ser un número entero.',
            'vehicle_id.exists'         => 'El vehículo seleccionado no existe.',
            'taller_id.required'        => 'El taller es obligatorio.',
            'taller_id.integer'         => 'El ID del taller debe ser un número entero.',
            'taller_id.exists'          => 'El taller seleccionado no existe.',

            'maintenance_operations.array'                        => 'El campo de operaciones debe ser un arreglo.',
            'maintenance_operations.*.type_moviment.required_with'=> 'El tipo de movimiento es obligatorio para cada operación.',
            'maintenance_operations.*.type_moviment.in'           => 'El tipo de movimiento debe ser "CORRECTIVO" o "PREVENTIVO".',
            'maintenance_operations.*.name.required_with'         => 'El nombre del insumo o tarea es obligatorio.',
            'maintenance_operations.*.quantity.required_with'     => 'La cantidad es obligatoria.',
            'maintenance_operations.*.quantity.numeric'           => 'La cantidad debe ser un número.',
            'maintenance_operations.*.unity.required_with'        => 'La unidad de medida es obligatoria.',
        ];
    }
}
