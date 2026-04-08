<?php

namespace App\Http\Requests\CompraPartialReceiptGroupRequest;

use App\Http\Requests\IndexRequest;

class IndexCompraPartialReceiptGroupRequest extends IndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'branch_office_id' => 'nullable|integer',
            'proveedor_id'     => 'nullable|integer',
        ]);
    }
}
