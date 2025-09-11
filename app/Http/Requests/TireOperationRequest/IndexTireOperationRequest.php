<?php

namespace App\Http\Requests\TireOperationRequest;

use App\Http\Requests\IndexRequest;

class IndexTireOperationRequest extends IndexRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'             => 'nullable|string',
            'operation_type' => 'nullable|string',
            'vehicle_id'     => 'nullable|string',
            'position'       => 'nullable|string',
            'vehicle_km'     => 'nullable|string',
            'operation_date' => 'nullable|string',
            'comment'        => 'nullable|string',
            'driver_id'      => 'nullable|string',
            'user_id'        => 'nullable|string',
            'tire_id'        => 'nullable|string',
            'created_at'     => 'nullable|string',
            'updated_at'     => 'nullable|string',
            'deleted_at'     => 'nullable|string',
            'presion_aire'   => 'nullable|string',
        ];
    }
}
