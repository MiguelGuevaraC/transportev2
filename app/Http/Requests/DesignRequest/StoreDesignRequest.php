<?php

namespace App\Http\Requests\DesignRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreDesignRequest",
 *     required={"name", "state"},
 *     @OA\Property(property="name", type="string")
 *     @OA\Property(property="state", type="boolean")
 * )
 */
class StoreDesignRequest extends StoreRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
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