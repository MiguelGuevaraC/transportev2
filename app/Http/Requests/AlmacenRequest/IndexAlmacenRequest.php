<?php
namespace App\Http\Requests\AlmacenRequest;

use App\Http\Requests\IndexRequest;

class IndexAlmacenRequest extends IndexRequest
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
            'name'=> 'nullable|string',
            'address'=> 'nullable|string',
            'status'=> 'nullable|string',
        ];
    }
    
}
