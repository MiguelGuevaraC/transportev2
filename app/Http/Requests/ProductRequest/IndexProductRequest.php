<?php
namespace App\Http\Requests\ProductRequest;

use App\Http\Requests\IndexRequest;

class IndexProductRequest extends IndexRequest
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
 
            'description' => 'nullable|integer',
            'stock' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'category' => 'nullable|integer',
            'unity' => 'nullable|integer',
            'unity_id' => 'nullable|integer',
            'person_id' => 'nullable|integer',
        ];
    }
    
}
