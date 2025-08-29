<?php

namespace App\Http\Requests\DocAlmacenRequest;

use App\Http\Requests\UpdateRequest;

/**
 * @OA\Schema(
 *     schema="UpdateDocAlmacenRequest",
 *     @OA\Property(property="concept_id", type="integer")
 *     @OA\Property(property="type", type="string")
 *     @OA\Property(property="movement_date", type="datetime")
 *     @OA\Property(property="reference_id", type="integer")
 *     @OA\Property(property="reference_type", type="string")
 *     @OA\Property(property="note", type="text")
 * )
 */
class UpdateDocAlmacenRequest extends UpdateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'concept_id' => ['nullable', 'integer', 'exists:concepts,id'],
            'type' => ['nullable', 'string', 'in:INGRESO,EGRESO'],
            'movement_date' => ['nullable', 'date'],
            'note' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'concept_id.required' => 'El campo concept_id es obligatorio.',
            'concept_id.integer' => 'El campo concept_id debe ser un número entero.',
            'concept_id.exists' => 'El concept_id no existe en la tabla de conceptos.',

            'type.required' => 'El campo type es obligatorio.',
            'type.string' => 'El campo type debe ser una cadena de texto.',
            'type.in' => 'El campo type debe ser INGRESO o EGRESO.',

            'movement_date.required' => 'El campo movement_date es obligatorio.',
            'movement_date.date' => 'El campo movement_date debe tener un formato de fecha válido (YYYY-MM-DD).',

            'note.string' => 'El campo note debe ser una cadena de texto.',
        ];
    }
}