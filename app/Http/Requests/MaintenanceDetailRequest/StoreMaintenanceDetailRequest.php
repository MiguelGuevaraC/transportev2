<?php

namespace App\Http\Requests\MaintenanceDetailRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreMaintenanceDetailRequest",
 *     title="Store Maintenance Detail Request",
 *     description="Request model for Maintenance Detail",
 *     required={"name", "type", "price", "quantity", "maintenance_id", "repuesto_id"},
 *     @OA\Property(property="name", type="string", maxLength=255, description="Nombre del repuesto o servicio"),
 *     @OA\Property(property="type", type="string", description="Tipo (PRODUCTO o SERVICIO)"),
 *     @OA\Property(property="price", type="number", format="float", description="Precio del repuesto o servicio"),
 *     @OA\Property(property="quantity", type="integer", description="Cantidad utilizada"),
 *     @OA\Property(property="maintenance_id", type="integer", description="ID del mantenimiento"),
 *     @OA\Property(property="repuesto_id", type="integer", description="ID del repuesto"),
 * )
 */
class StoreMaintenanceDetailRequest extends StoreRequest
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
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|in:PRODUCTO,SERVICIO', // Aquí el IN corregido
            'price'          => 'required|numeric',
            'quantity'       => 'required|integer|min:0',
            'maintenance_id' => 'required|integer|exists:maintenances,id', // Corrijo exist y typo
            'repuesto_id'    => 'nullable|integer|exists:repuestos,id',    // Corrijo exist y typo
            'service_id'    => 'nullable|integer|exists:services,id',    // Corrijo exist y typo
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

            'service_id.required'      => 'El ID del servicio es obligatorio.',
            'service_id.integer'       => 'El ID del servicio debe ser un número entero.',
            'service_id.exists'        => 'El servicio seleccionado no existe.',
        ];
    }
}
