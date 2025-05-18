<?php
namespace App\Http\Requests\CompraOrderRequest;

use App\Http\Requests\IndexRequest;

class IndexCompraOrderRequest extends IndexRequest
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

            'number'          => 'nullable|string',
            'date_movement'   => 'nullable|string',
            'branchOffice_id' => 'nullable|string',
            'person_id'       => 'nullable|string',
            'proveedor_id'    => 'nullable|string',
            'comment'         => 'nullable|string',
            'status'          => 'nullable|string',
        ];
    }

}
