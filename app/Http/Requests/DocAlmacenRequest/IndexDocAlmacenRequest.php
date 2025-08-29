<?php

namespace App\Http\Requests\DocAlmacenRequest;

use App\Http\Requests\IndexRequest;

class IndexDocAlmacenRequest extends IndexRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'concept_id' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'movement_date' => ['nullable', 'string'],
            'reference_id' => ['nullable', 'string'],
            'reference_type' => ['nullable', 'string'],
            'user_id' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ];
    }

}