<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\IndexRequest;

class IndexCargaDocumentRequest extends IndexRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id'              => 'nullable|integer',
            'movement_date'   => 'nullable|date',
            'quantity'        => 'nullable|string',
            'unit_price'      => 'nullable|string',
            'total_cost'      => 'nullable|string',
            'weight'          => 'nullable|string',
            'movement_type'   => 'nullable|string',
            'stock_balance'   => 'nullable|string',
            'comment'         => 'nullable|string',
            'product_id'      => 'nullable|integer',
            'person_id'       => 'nullable|integer',

        ];
    }
    
}
