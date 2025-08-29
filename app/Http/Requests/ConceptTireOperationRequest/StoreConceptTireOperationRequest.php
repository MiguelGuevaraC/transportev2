<?php

namespace App\Http\Requests\ConceptTireOperationRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreConceptTireOperationRequest",
 *     required={"name", "type", "status"},
 *     @OA\Property(property="name", type="string")
 *     @OA\Property(property="type", type="string")
 *     @OA\Property(property="status", type="string")
 * )
 */
class StoreConceptTireOperationRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'type' => ['required', 'string'],
            //'status' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo name es obligatorio.',
            'name.name' => 'El formato del campo name es inválido.',
            'type.required' => 'El campo type es obligatorio.',
            'type.type' => 'El formato del campo type es inválido.',
            'status.required' => 'El campo status es obligatorio.',
            'status.status' => 'El formato del campo status es inválido.'
        ];
    }
}