<?php
namespace App\Http\Requests\BankAccountRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends UpdateRequest
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
        $id = $this->route('id'); // Obtén el ID de la ruta, que se asume que es el ID del usuario

        return [
            'bank_id'        => ['required', 'integer', 'exists:banks,id,deleted_at,NULL'],
            'account_number' => [
                'required',
                'string',
                Rule::unique('bank_accounts', 'account_number')
                    ->ignore($id) // Ignorar el registro actual en la validación de unicidad
                    ->whereNull('deleted_at')    // No considerar los eliminados
            ],
            'account_type'   => ['required', 'string', 'in:ahorros,corriente,credito'],
            'currency'       => ['required', 'string', 'max:3'],
            // 'holder_name'    => ['required', 'string', 'max:500'],
            // 'holder_id'      => ['required', 'integer', 'exists:people,id,deleted_at,NULL'],
            'status'         => ['nullable', 'string', 'in:activa,inactiva,cerrada'],
        ];
    }

    public function messages()
{
    return [
        'bank_id.required'        => 'El banco es obligatorio.',
        'bank_id.integer'         => 'El banco debe ser un número entero.',
        'bank_id.exists'          => 'El banco seleccionado no es válido o ha sido eliminado.',

        'account_number.required' => 'El número de cuenta es obligatorio.',
        'account_number.string'   => 'El número de cuenta debe ser una cadena de texto.',
        'account_number.unique'   => 'El número de cuenta ya está registrado en otra cuenta activa.',

        'account_type.required'   => 'El tipo de cuenta es obligatorio.',
        'account_type.string'     => 'El tipo de cuenta debe ser una cadena de texto.',
        'account_type.in'         => 'El tipo de cuenta debe ser uno de los siguientes valores: ahorros, corriente, crédito.',

        'currency.required'       => 'La moneda es obligatoria.',
        'currency.string'         => 'La moneda debe ser una cadena de texto.',
        'currency.max'            => 'El código de la moneda no debe exceder los 3 caracteres.',

        // 'holder_name.required'    => 'El nombre del titular es obligatorio.',
        // 'holder_name.string'      => 'El nombre del titular debe ser una cadena de texto.',
        // 'holder_name.max'         => 'El nombre del titular no debe exceder los 500 caracteres.',

        // 'holder_id.required'      => 'El titular es obligatorio.',
        // 'holder_id.integer'       => 'El ID del titular debe ser un número entero.',
        // 'holder_id.exists'        => 'El titular seleccionado no es válido o ha sido eliminado.',

        'status.required'         => 'El estado de la cuenta es obligatorio.',
        'status.string'           => 'El estado debe ser una cadena de texto.',
        'status.in'               => 'El estado debe ser uno de los siguientes valores: activa, inactiva, cerrada.',
    ];
}


}
