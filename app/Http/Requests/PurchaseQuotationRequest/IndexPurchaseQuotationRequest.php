<?php

namespace App\Http\Requests\PurchaseQuotationRequest;

use App\Http\Requests\IndexRequest;

class IndexPurchaseQuotationRequest extends IndexRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'product_requirement_id' => 'nullable|integer',
            'proveedor_id'           => 'nullable|integer',
            'is_winner'              => 'nullable|boolean',
            'status'                 => 'nullable|string',
        ]);
    }
}
