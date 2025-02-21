<?php
namespace App\Http\Requests\BankAccountRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="BankAccountRequest",
 *     title="BankAccountRequest",
 *     description="Request model for Bank Account information with filters and sorting",
 *     required={"bank_id", "account_number", "account_type", "currency", "holder_id", "status"},
 *
 *     @OA\Property(property="bank_id", type="integer", nullable=false, description="ID del banco asociado"),
 *     @OA\Property(property="account_number", type="string", nullable=false, description="Número de cuenta bancaria"),
 *     @OA\Property(property="account_type", type="string", nullable=false, enum={"ahorros", "corriente", "credito"}, description="Tipo de cuenta bancaria (ahorros, corriente, crédito)"),
 *     @OA\Property(property="currency", type="string", nullable=false, description="Código de la moneda (Ej: USD, EUR, PEN)"),
 *     @OA\Property(property="balance", type="number", format="float", nullable=true, description="Saldo de la cuenta bancaria"),
 *     @OA\Property(property="holder_name", type="string", nullable=false, description="Nombre del titular de la cuenta"),
 *     @OA\Property(property="holder_id", type="integer", nullable=false, description="ID del titular de la cuenta (referencia a tabla People)"),
 *     @OA\Property(property="status", type="string", nullable=false, enum={"activa", "inactiva", "cerrada"}, description="Estado de la cuenta bancaria"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true, description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Última actualización"),
 * )
 */

class StoreBankAccountRequest extends StoreRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'bank_id'        => ['required', 'integer', 'exists:banks,id,deleted_at,NULL'],
            'account_number' => [
                'required',
                'string',
                Rule::unique('bank_accounts', 'account_number')->whereNull('deleted_at'),
            ],
            'account_type'   => ['required', 'string', 'in:ahorros,corriente,credito'],
            'currency'       => ['required', 'string', 'max:3'],
            'holder_name'    => ['required', 'string', 'max:500'],
            'holder_id'      => ['required', 'integer', 'exists:people,id,deleted_at,NULL'],
            'status'         => ['required', 'string', 'in:activa,inactiva,cerrada'],
        ];
    }

    public function messages()
    {
        return [
            'bank_id.required'        => 'El banco es obligatorio.',
            'bank_id.integer'         => 'El ID del banco debe ser un número entero.',
            'bank_id.exists'          => 'El banco seleccionado no existe o ha sido eliminado.',

            'account_number.required' => 'El número de cuenta es obligatorio.',
            'account_number.string'   => 'El número de cuenta debe ser un texto.',
            'account_number.unique'   => 'El número de cuenta ya está en uso.',

            'account_type.required'   => 'El tipo de cuenta es obligatorio.',
            'account_type.string'     => 'El tipo de cuenta debe ser un texto.',
            'account_type.in'         => 'El tipo de cuenta debe ser "ahorros", "corriente" o "credito".',

            'currency.required'       => 'La moneda es obligatoria.',
            'currency.string'         => 'El código de la moneda debe ser un texto.',
            'currency.max'            => 'El código de la moneda no debe exceder los 3 caracteres.',

            'holder_name.required'    => 'El nombre del titular es obligatorio.',
            'holder_name.string'      => 'El nombre del titular debe ser un texto.',
            'holder_name.max'         => 'El nombre del titular no debe exceder los 500 caracteres.',

            'holder_id.required'      => 'El titular es obligatorio.',
            'holder_id.integer'       => 'El ID del titular debe ser un número entero.',
            'holder_id.exists'        => 'El titular seleccionado no existe o ha sido eliminado.',

            'status.required'         => 'El estado de la cuenta es obligatorio.',
            'status.string'           => 'El estado de la cuenta debe ser un texto.',
            'status.in'               => 'El estado de la cuenta debe ser "activa", "inactiva" o "cerrada".',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
