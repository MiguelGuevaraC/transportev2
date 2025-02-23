<?php
namespace App\Http\Requests\BankAccountRequest;

use App\Http\Requests\IndexRequest;

/**
 * @OA\Schema(
 *     schema="BankAccountFilters",
 *     type="object",
 *     title="Bank Account Filters",
 *     description="Parámetros de filtro para obtener cuentas bancarias",
 *     @OA\Property(property="bank_id", type="string", nullable=true, description="ID del banco asociado"),
 *     @OA\Property(property="account_number", type="string", nullable=true, description="Número de cuenta bancaria"),
 *     @OA\Property(property="account_type", type="string", nullable=true, description="Tipo de cuenta bancaria"),
 *     @OA\Property(property="currency", type="string", nullable=true, description="Moneda de la cuenta"),
 *     @OA\Property(property="balance", type="string", nullable=true, description="Saldo de la cuenta"),
 *     @OA\Property(property="holder_name", type="string", nullable=true, description="Nombre del titular de la cuenta"),
 *     @OA\Property(property="holder_id", type="string", nullable=true, description="ID del titular de la cuenta"),
 *     @OA\Property(property="status", type="string", nullable=true, description="Estado de la cuenta bancaria")
 * )
 */

 
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
