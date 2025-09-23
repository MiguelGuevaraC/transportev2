<?php

namespace App\Http\Requests\TireMeasureRequest;

use App\Http\Requests\IndexRequest;

class IndexTireMeasureRequest extends IndexRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
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