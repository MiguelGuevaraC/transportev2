<?php

namespace App\Http\Requests\TireRequest;

use App\Http\Requests\StoreRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="TireRequest",
 *     title="TireRequest",
 *     description="Request model for Tire information",
 *     required={"code", "entry_date"},
 *     @OA\Property(property="code", type="string", description="Código único del neumático"),
 *     @OA\Property(property="condition", type="string", nullable=true, description="Condición del neumático"),
 *     @OA\Property(property="retread_number", type="integer", nullable=true, description="Número de reencauche"),
 *     @OA\Property(property="entry_date", type="string", format="date", description="Fecha de ingreso"),
 *     @OA\Property(property="supplier_id", type="integer", nullable=true, description="ID del proveedor"),
 *     @OA\Property(property="material", type="string", nullable=true, description="Material del neumático"),
 *     @OA\Property(property="brand", type="string", nullable=true, description="Marca del neumático"),
 *     @OA\Property(property="design", type="string", nullable=true, description="Diseño del neumático"),
 *     @OA\Property(property="type", type="string", nullable=true, description="Tipo de neumático"),
 *     @OA\Property(property="size", type="string", nullable=true, description="Medida del neumático"),
 *     @OA\Property(property="dot", type="string", nullable=true, description="Fecha de fabricación (DOT)"),
 *     @OA\Property(property="tread_type", type="string", nullable=true, description="Tipo de banda"),
 *     @OA\Property(property="current_tread", type="number", format="float", nullable=true, description="Cocada actual"),
 *     @OA\Property(property="minimum_tread", type="number", format="float", nullable=true, description="Cocada mínima"),
 *     @OA\Property(property="tread", type="number", format="float", nullable=true, description="Cocada inicial"),
 *     @OA\Property(property="shoulder1", type="number", format="float", nullable=true, description="Ribete 1"),
 *     @OA\Property(property="shoulder2", type="number", format="float", nullable=true, description="Ribete 2"),
 *     @OA\Property(property="shoulder3", type="number", format="float", nullable=true, description="Ribete 3")
 * )
 */
class StoreTireRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'code' => [
            //     'required',
            //     'string',
            //     Rule::unique('tires', 'code')->whereNull('deleted_at'),
            // ],
            'condition' => ['nullable', 'string'],
            'retread_number' => ['nullable', 'integer'],
            'entry_date' => ['required', 'date'],
            'supplier_id' => ['nullable', 'integer', 'exists:people,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],

            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'design_id' => ['required', 'integer', 'exists:designs,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],

            // 'material' => ['nullable', 'string'],
            // 'brand' => ['nullable', 'string'],
            // 'design' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'size' => ['nullable', 'string'],
            'dot' => ['nullable', 'string'],
            'tread_type' => ['nullable', 'string'],
            'current_tread' => ['nullable', 'numeric'],
            'minimum_tread' => ['nullable', 'numeric'],
            'tread' => ['nullable', 'numeric'],
            'shoulder1' => ['nullable', 'numeric'],
            'shoulder2' => ['nullable', 'numeric'],
            'shoulder3' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código del neumático es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'entry_date.required' => 'La fecha de ingreso es obligatoria.',
            'entry_date.date' => 'La fecha de ingreso no es válida.',

            'supplier_id.integer' => 'El proveedor debe ser un número.',
            'supplier_id.exists' => 'El proveedor no existe en el sistema.',
            'retread_number.integer' => 'El número de reencauche debe ser numérico.',

            'current_tread.numeric' => 'La cocada actual debe ser un número.',
            'minimum_tread.numeric' => 'La cocada mínima debe ser un número.',
            'tread.numeric' => 'La cocada debe ser un número.',
            'shoulder1.numeric' => 'El ribete 1 debe ser un número.',
            'shoulder2.numeric' => 'El ribete 2 debe ser un número.',
            'shoulder3.numeric' => 'El ribete 3 debe ser un número.',

            'vehicle_id.integer' => 'El ID del vehículo debe ser un número entero.',
            'vehicle_id.exists' => 'El vehículo seleccionado no existe.',

            'material_id.required' => 'El campo material es obligatorio.',
            'material_id.integer' => 'El campo material debe ser un número entero.',
            'material_id.exists' => 'El material seleccionado no es válido.',
            'design_id.required' => 'El campo diseño es obligatorio.',
            'design_id.integer' => 'El campo diseño debe ser un número entero.',
            'design_id.exists' => 'El diseño seleccionado no es válido.',
            'brand_id.required' => 'El campo marca es obligatorio.',
            'brand_id.integer' => 'El campo marca debe ser un número entero.',
            'brand_id.exists' => 'La marca seleccionada no es válida.',

        ];
    }
}
