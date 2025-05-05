<?php
namespace App\Http\Requests\MaintenanceDetailRequest;

use App\Http\Requests\IndexRequest;

class IndexMaintenanceDetailRequest extends IndexRequest
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
            'name'           => 'nullable|string',
            'type'           => 'nullable|string',
            'price'          => 'nullable|string',
            'quantity'       => 'nullable|string',
            'maintenance_id' => 'nullable|string',
            'repuesto_id'    => 'nullable|string',
            'service_id'    => 'nullable|string',
        ];
    }

}
