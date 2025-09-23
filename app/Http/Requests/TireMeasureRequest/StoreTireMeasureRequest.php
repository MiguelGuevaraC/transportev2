<?php

namespace App\Http\Requests\TireMeasureRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreTireMeasureRequest",
 *     required={"name", "status"},
 *     @OA\Property(property="name", type="string")
 *     @OA\Property(property="status", type="boolean")
 * )
 */
class StoreTireMeasureRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo name es obligatorio.',
            'name.name' => 'El formato del campo name es inválido.',
            'status.required' => 'El campo status es obligatorio.',
            'status.status' => 'El formato del campo status es inválido.'
        ];
    }
}