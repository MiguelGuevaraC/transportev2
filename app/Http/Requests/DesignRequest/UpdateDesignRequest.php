<?php

namespace App\Http\Requests\DesignRequest;

use App\Http\Requests\UpdateRequest;

/**
 * @OA\Schema(
 *     schema="UpdateDesignRequest",
 *     @OA\Property(property="name", type="string")
 *     @OA\Property(property="state", type="boolean")
 * )
 */
class UpdateDesignRequest extends UpdateRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['nullable', 'string'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo name es obligatorio.',
            'name.name' => 'El formato del campo name es inválido.',
            'brand_id.required' => 'El campo brand_id es obligatorio.',
            'brand_id.integer' => 'El campo brand_id debe ser un número entero.',
            'brand_id.exists' => 'El campo brand_id debe existir en la tabla brands.'
        ];
    }
}