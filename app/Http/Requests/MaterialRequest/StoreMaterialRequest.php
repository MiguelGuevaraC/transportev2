<?php

namespace App\Http\Requests\MaterialRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreMaterialRequest",
 *     required={"name", "state"},
 *     @OA\Property(property="name", type="string")
 *     @OA\Property(property="state", type="boolean")
 * )
 */
class StoreMaterialRequest extends StoreRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            //'state' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo name es obligatorio.',
            'name.name' => 'El formato del campo name es inválido.',
            'state.required' => 'El campo state es obligatorio.',
            'state.state' => 'El formato del campo state es inválido.'
        ];
    }
}