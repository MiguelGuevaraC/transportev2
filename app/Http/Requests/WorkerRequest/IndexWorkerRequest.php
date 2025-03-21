<?php
namespace App\Http\Requests\WorkerRequest;

use App\Http\Requests\IndexRequest;

class IndexWorkerRequest extends IndexRequest
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
            'name' => 'nullable|string',
            'occupation' => 'nullable|string',
            'licencia' => 'nullable|string',
            'licencia_date' => 'nullable|string',
        ];
    }

}
