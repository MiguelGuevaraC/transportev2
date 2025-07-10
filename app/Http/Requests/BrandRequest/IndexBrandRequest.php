<?php

namespace App\Http\Requests\BrandRequest;

use App\Http\Requests\IndexRequest;

class IndexBrandRequest extends IndexRequest
{
    public function authorize() { return true; }

    public function rules(): array
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