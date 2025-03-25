<?php
namespace App\Http\Requests\SaleRequest;

use App\Http\Requests\StoreRequest;
use App\Models\Installment;
use Illuminate\Validation\Rule;

class PayMasiveSaleRequest extends StoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Cambia esto si necesitas autorización específica
    }

    public function rules()
    {
        return [
            'paymasive'                  => 'required|array',
            'bank_account_id'            => [
                'nullable',
                'integer',
                Rule::exists('bank_accounts', 'id')->whereNull('deleted_at'),
            ],

            'transaction_concept_id'     => [
                'required_with:bank_account_id', // Se requiere si 'bank_account_id' está presente y no es null
                'exists:transaction_concepts,id,deleted_at,NULL',
            ],

            'bank_id'                    => [
                'required_with:bank_account_id', // Se requiere si 'bank_account_id' está presente y no es null
                'exists:banks,id,deleted_at,NULL',
            ],

            'paymasive.*.installment_id' => 'required|integer|exists:installments,id,deleted_at,NULL',
            'paymasive.*.amount'         => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $index         = explode('.', $attribute)[1]; // Extraer índice dinámicamente
                    $installmentId = request()->input("paymasive.{$index}.installment_id");

                    if ($installmentId) {
                        $installment = Installment::find($installmentId);

                        if ($installment && floatval($value) > floatval($installment->totalDebt)) {
                            // Aquí pasamos el monto ingresado y la deuda total en el mensaje
                            $fail("El monto ingresado ({$value}) no puede ser mayor a la deuda total ({$installment->totalDebt}).");
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'paymasive.required'                 => 'El campo de pagos masivos es obligatorio.',
            'paymasive.array'                    => 'El formato de los pagos masivos no es válido.',

            'paymasive.*.installment_id.integer' => 'El ID de la cuota debe ser un número entero.',
            'paymasive.*.installment_id.exists'  => 'La cuota seleccionada no existe o ha sido eliminada.',

            'paymasive.*.amount.required'        => 'El monto a pagar es obligatorio.',
            'paymasive.*.amount.numeric'         => 'El monto a pagar debe ser un número.',
            'paymasive.*.amount.min'             => 'El monto a pagar no puede ser negativo.',

            'bank_account_id.required'           => 'La cuenta bancaria es obligatoria.',
            'bank_account_id.integer'            => 'El ID de la cuenta bancaria debe ser un número.',
            'bank_account_id.exists'             => 'La cuenta bancaria seleccionada no es válida o ha sido eliminada.',

            'transaction_concept_id.required_if' => 'El concepto de transacción es obligatorio cuando se selecciona una cuenta bancaria.',
            'transaction_concept_id.exists'      => 'El concepto de transacción seleccionado no es válido o ha sido eliminado.',

            'person_id.required_if'              => 'La persona es obligatoria cuando se selecciona una cuenta bancaria.',
            'person_id.exists'                   => 'La persona seleccionada no es válida o ha sido eliminada.',

            'bank_id.required_if'                => 'El banco es obligatorio cuando se selecciona una cuenta bancaria.',
            'bank_id.exists'                     => 'El banco seleccionado no es válido o ha sido eliminado.',
        ];
    }

}
