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

            'tarifa'          => 'nullable|string',
            'description'     => 'nullable|string',
            'origin_id'       => 'nullable|string',
            'destination_id'  => 'nullable|string',
            'person_id'       => 'nullable|string',
            'unity_id'        => 'nullable|string',
            'limitweight_min' => 'nullable|string',
            'limitweight_max' => 'nullable|string',
            'created_at'      => 'nullable|string',

        ];
    }

}
