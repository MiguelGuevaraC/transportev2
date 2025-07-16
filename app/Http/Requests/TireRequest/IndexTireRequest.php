<?php

namespace App\Http\Requests\TireRequest;

use App\Http\Requests\IndexRequest;

class IndexTireRequest extends IndexRequest
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
            'id' => 'nullable|string',
            'code' => 'nullable|string',
            'condition' => 'nullable|string',
            'retread_number' => 'nullable|string',
            'entry_date' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'material' => 'nullable|string',
            'brand' => 'nullable|string',
            'design' => 'nullable|string',
            'type' => 'nullable|string',
            'size' => 'nullable|string',
            'dot' => 'nullable|string',
            'tread_type' => 'nullable|string',
            'current_tread' => 'nullable|string',
            'minimum_tread' => 'nullable|string',
            'tread' => 'nullable|string',
            'shoulder1' => 'nullable|string',
            'shoulder2' => 'nullable|string',
            'shoulder3' => 'nullable|string',
            'created_at' => 'nullable|string',
            'updated_at' => 'nullable|string',
            'deleted_at' => 'nullable|string',

            'material_id' => 'nullable|string',
            'design_id' => 'nullable|string',
            'brand_id' => 'nullable|string',
        ];
    }
}
