<?php
namespace App\Http\Requests\InstallmentRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayInstallmentRequest extends StoreRequest
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
            'paymentDate'      => 'required|date',
            'yape'             => 'nullable|numeric',
            'deposit'          => 'nullable|numeric',
            'cash'             => 'nullable|numeric',
            'card'             => 'nullable|numeric',
            'plin'             => 'nullable|numeric',
            'comment'          => 'nullable|string',
            'installment_id'   => 'required|exists:installments,id',
            'transaction_concept_id' => 'required|exists:transaction_concepts,id,deleted_at,NULL',
            'bank_id'          => 'nullable|exists:banks,id',
            'is_anticipo'      => 'nullable|boolean',
            'total_anticipado' => 'nullable|numeric|min:0',
            'bank_account_id'  => [
                'nullable',
                Rule::exists('bank_accounts', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    $status = DB::table('bank_accounts')->where('id', $value)->value('status');
                    if ($status === 'inactiva') {
                        $fail('No se pueden realizar movimientos en una cuenta bancaria inactiva.');
                    }
                },
            ],

            'transaction_concept_id.required' => 'El concepto de transacción es obligatorio.',
            'transaction_concept_id.exists'   => 'El concepto de transacción seleccionado no existe o ha sido eliminado.',
        ];
    }

    public function messages()
    {
        return [
            'paymentDate.required'     => 'La fecha de pago es obligatoria.',
            'paymentDate.date'         => 'La fecha de pago debe ser una fecha válida.',

            'yape.numeric'             => 'El monto de Yape debe ser un número.',
            'deposit.numeric'          => 'El monto de depósito debe ser un número.',
            'cash.numeric'             => 'El monto en efectivo debe ser un número.',
            'card.numeric'             => 'El monto de tarjeta debe ser un número.',
            'plin.numeric'             => 'El monto de Plin debe ser un número.',

            'comment.string'           => 'El comentario debe ser una cadena de texto.',

            'installment_id.required'  => 'El ID de la cuota es obligatorio.',
            'installment_id.exists'    => 'El ID de la cuota no existe en la base de datos.',

            'bank_id.required'         => 'El ID de la cuota es obligatorio.',
            'bank_id.exists'           => 'El ID de la cuota no existe en la base de datos.',

            'is_anticipo.boolean'      => 'El campo "Es anticipo" debe ser 1 o 0.',
            'total_anticipado.numeric' => 'El campo "Total anticipado" debe ser un número.',
            'total_anticipado.min'     => 'El campo "Total anticipado" debe ser mayor o igual a 0.',

            'bank_account_id.required' => 'La cuenta bancaria es obligatoria.',
            'bank_account_id.exists'   => 'La cuenta bancaria seleccionada no existe o ha sido eliminada.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
