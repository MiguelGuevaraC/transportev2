<?php
namespace App\Http\Requests\PayableRequest;

use App\Http\Requests\IndexRequest;

class IndexPayableRequest extends IndexRequest
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
            'programming_numero' => 'nullable|string',
            'driver_expense$proveedor_id' => 'nullable|string',
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ];
    }

}
