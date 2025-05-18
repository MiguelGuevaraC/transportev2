<?php
namespace App\Http\Requests\CompraMovimentRequest;

use App\Http\Requests\IndexRequest;

class IndexCompraMovimentRequest extends IndexRequest
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

   
       'number'  => 'nullable|string',
        'date_movement'  => 'nullable|string',
        'document_type'  => 'nullable|string',
        'branchOffice_id'  => 'nullable|string',
        'person_id'  => 'nullable|string',
        'proveedor_id'  => 'nullable|string',
        'compra_order_id'  => 'nullable|string',
        'payment_method'  => 'nullable|string',
        'comment'  => 'nullable|string',
        'status'  => 'nullable|string',
        ];
    }

}
