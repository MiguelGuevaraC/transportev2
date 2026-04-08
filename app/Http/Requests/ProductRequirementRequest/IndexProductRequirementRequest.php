<?php

namespace App\Http\Requests\ProductRequirementRequest;

use App\Http\Requests\IndexRequest;

class IndexProductRequirementRequest extends IndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'check_list_id'      => 'nullable|integer',
            'branch_office_id'   => 'nullable|integer',
            'status'             => 'nullable|string',
        ]);
    }
}
