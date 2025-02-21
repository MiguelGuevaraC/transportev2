<?php
namespace App\Http\Requests\BankAccountRequest;

use App\Http\Requests\IndexRequest;

class IndexBankAccountRequest extends IndexRequest
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

            'bank_id'        => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_type'   => 'nullable|string',
            'currency'       => 'nullable|string',
            'balance'        => 'nullable|string',
            'holder_name'    => 'nullable|string',
            'holder_id'      => 'nullable|string',
            'status'         => 'nullable|string',
        ];
    }

}
