<?php
namespace App\Http\Requests\PayableRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayPayableRequest extends StoreRequest
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
           'bank_id' => 'sometimes|nullable|exists:banks,id,deleted_at,NULL',

            'bank_movement_id'       => 'sometimes|nullable|required_if:is_anticipo,1|exists:bank_movements,id,deleted_at,NULL',
            'card'                   => 'nullable|numeric',
            'cash'                   => 'nullable|numeric',
            'comment'                => 'nullable|string',
            'deposit'                => 'nullable|numeric',
            'payable_id'         => 'required|exists:payables,id',

            'is_anticipo'            => [
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($value == 1) {
                        $card            = request('card', 0);
                        $deposit         = request('deposit', 0);
                        $totalAnticipado = $card + $deposit;

                        $totalRestante = DB::table('bank_movements')
                            ->where('id', request('bank_movement_id'))
                            ->value('total_anticipado_egreso_restante');
                         

                        if (! $totalRestante) {
                            return $fail('El movimiento bancario no existe o no tiene saldo restante.');
                        }

                        if ($totalAnticipado > $totalRestante) {
                            $fail("La suma de tarjeta y depósito ($totalAnticipado) no puede superar $totalRestante.");
                        }
                    }
                },
            ],

            'is_detraction'          => 'nullable',
            'nroOperacion'           => 'nullable',
            'paymentDate'            => 'required|date',
            'plin'                   => 'nullable|numeric',

            'yape'                   => 'nullable|numeric',
            'bank_account_id'        => [
                'required_if:is_anticipo,1',
                Rule::exists('bank_accounts', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    $status = DB::table('bank_accounts')->where('id', $value)->value('status');
                    if ($status === 'inactiva') {
                        $fail('No se pueden realizar movimientos en una cuenta bancaria inactiva.');
                    }
                },
            ],

            'transaction_concept_id' => 'nullable|exists:transaction_concepts,id,deleted_at,NULL',
        ];
    }

    public function messages()
    {
        return [
            'paymentDate.required'               => 'La fecha de pago es obligatoria.',
            'paymentDate.date'                   => 'La fecha de pago debe ser una fecha válida.',

            'yape.numeric'                       => 'El monto de Yape debe ser un número.',
            'deposit.numeric'                    => 'El monto de depósito debe ser un número.',
            'cash.numeric'                       => 'El monto en efectivo debe ser un número.',
            'card.numeric'                       => 'El monto de tarjeta debe ser un número.',
            'plin.numeric'                       => 'El monto de Plin debe ser un número.',

            'comment.string'                     => 'El comentario debe ser una cadena de texto.',

            'payable_id.required'            => 'El ID de la cuota es obligatorio.',
            'payable_id.exists'              => 'El ID de la cuota no existe en la base de datos.',

            'bank_id.required'                   => 'El ID del Banco es obligatorio.',
            'bank_id.exists'                     => 'El ID del Banco no existe en la base de datos.',

            'is_anticipo.boolean'                => 'El campo "Es anticipo" debe ser 1 o 0.',
            'total_anticipado.numeric'           => 'El campo "Total anticipado" debe ser un número.',
            'total_anticipado.min'               => 'El campo "Total anticipado" debe ser mayor o igual a 0.',

            'bank_account_id.required'           => 'La cuenta bancaria es obligatoria.',
            'bank_account_id.exists'             => 'La cuenta bancaria seleccionada no existe o ha sido eliminada.',

            'total_anticipado.required_if'       => 'El total anticipado es obligatorio si es un anticipo.',
            'bank_account_id.required_if'        => 'La cuenta bancaria es obligatoria si es un anticipo.',
            'anticipo_id.required_if'            => 'El anticipo ID es obligatorio si es un anticipo.',
            'transaction_concept_id.required_if' => 'El concepto de transacción es obligatorio si es un anticipo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
