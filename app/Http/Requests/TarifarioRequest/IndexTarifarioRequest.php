<?php
namespace App\Http\Requests\TarifarioRequest;

use App\Http\Requests\IndexRequest;

class IndexTarifarioRequest extends IndexRequest
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
            'tarifa'   => 'nullable|string',
            'quantity'        => 'nullable|string',
            'description'      => 'nullable|string',
            'unity'      => 'nullable|string',
            'person_id'          => 'nullable|string',
            'unity_id'          => 'nullable|string',
        ];
    }
    
}
