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
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => ['nullable', 'string'],
            'state' => ['nullable', 'boolean'],
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