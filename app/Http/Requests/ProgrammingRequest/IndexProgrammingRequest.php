<?php
namespace App\Http\Requests\ProgrammingRequest;

use App\Http\Requests\IndexRequest;

class IndexProgrammingRequest extends IndexRequest
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
            'driver_id' => 'nullable|string',
            'programming_id' => 'nullable|string', 
            'numero' => 'nullable|string', 
        ];
    }

}
