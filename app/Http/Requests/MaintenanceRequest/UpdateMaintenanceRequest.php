<?php

namespace App\Http\Requests\MaintenanceRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\Maintenance;
use Carbon\Carbon;

class UpdateMaintenanceRequest extends UpdateRequest
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
            'type'             => 'nullable|string|in:PROPIO,EXTERNO',
            'mode'             => 'nullable|string|in:CORRECTIVO,PREVENTIVO',
            'km'               => 'nullable|integer|min:0',
            'date_maintenance' => 'nullable|date',
            'vehicle_id'       => 'nullable|integer|exists:vehicles,id',
            'taller_id'        => 'nullable|integer|exists:tallers,id',
            'status'           => 'nullable|in:Finalizado,Pendiente',
            'date_end'         => 'required_if:status,Finalizado|date|nullable',

            // Operaciones opcionales
            'maintenance_operations'                   => 'nullable|array',
            'maintenance_operations.*.id'              => 'nullable|integer|exists:maintenance_operations,id',
            'maintenance_operations.*.type_moviment'   => 'required_with:maintenance_operations|string|in:CORRECTIVO,PREVENTIVO',
            'maintenance_operations.*.name'            => 'required_with:maintenance_operations|string|max:255',
            'maintenance_operations.*.quantity'        => 'required_with:maintenance_operations|numeric|min:0',
            'maintenance_operations.*.unity'           => 'required_with:maintenance_operations|string|max:100',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $maintenance = Maintenance::find($this->id);

            if ($maintenance && $this->date_end && Carbon::parse($this->date_end)->lt(Carbon::parse($maintenance->date_maintenance))) {
                $validator->errors()->add(
                    'date_end',
                    'La fecha de finalización (' . Carbon::parse($this->date_end)->format('d/m/Y H:i') . ') no puede ser anterior a la fecha de mantenimiento (' . Carbon::parse($maintenance->date_maintenance)->format('d/m/Y H:i') . ').'
                );
            }
        });
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
            'date_end.required_if'      => 'La fecha de finalización es obligatoria cuando el estado es Finalizado.',
            'date_end.date'             => 'La fecha de finalización debe ser una fecha válida.',

            'maintenance_operations.array'                          => 'Las operaciones deben estar en formato de arreglo.',
            'maintenance_operations.*.id.integer'                   => 'El ID de la operación debe ser un número entero.',
            'maintenance_operations.*.id.exists'                    => 'El ID de la operación no existe.',
            'maintenance_operations.*.type_moviment.required_with'  => 'El tipo de movimiento es obligatorio.',
            'maintenance_operations.*.type_moviment.in'             => 'El tipo de movimiento debe ser "CORRECTIVO" o "PREVENTIVO".',
            'maintenance_operations.*.name.required_with'           => 'El nombre del ítem es obligatorio.',
            'maintenance_operations.*.quantity.required_with'       => 'La cantidad es obligatoria.',
            'maintenance_operations.*.quantity.numeric'             => 'La cantidad debe ser un número.',
            'maintenance_operations.*.unity.required_with'          => 'La unidad de medida es obligatoria.',
        ];
    }
}
