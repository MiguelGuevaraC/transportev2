<?php
namespace App\Http\Requests\CheckListRequest;

use App\Http\Requests\IndexRequest;

class IndexCheckListRequest extends IndexRequest
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

            'numero'          => 'nullable|string',
            'date_check_list' => 'nullable|string',
            'vehicle_id'      => 'nullable|string',
            'observation'     => 'nullable|string',
            'status'          => 'nullable|string',
        ];
    }

}
