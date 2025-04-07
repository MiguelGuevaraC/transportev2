<?php
namespace App\Http\Requests\RepuestoRequest;

use App\Http\Requests\IndexRequest;

class IndexRepuestoRequest extends IndexRequest
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
            'name'   => 'nullable|string',
            'code'=> 'nullable|string',
            'price_compra'=> 'nullable|string',
            'stock'=> 'nullable|string',
            'status'=> 'nullable|string',
            'category_id'=> 'nullable|string',
            'created_at'=> 'nullable|string',
        ];
    }
    
}
