<?php
namespace App\Http\Requests\CompraOrderDetailRequest;

use App\Http\Requests\IndexRequest;

class IndexCompraOrderDetailRequest extends IndexRequest
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

            'compra_order_id' => 'nullable|string',
            'repuesto_id'     => 'nullable|string',
            'quantity'        => 'nullable|string',
            'unit_price'      => 'nullable|string',
            'subtotal'        => 'nullable|string',
            'comment'         => 'nullable|string',
        ];
    }

}
