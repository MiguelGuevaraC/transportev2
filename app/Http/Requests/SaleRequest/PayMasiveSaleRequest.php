<?php
namespace App\Http\Requests\SaleRequest;

use App\Http\Requests\StoreRequest;
use App\Models\Installment;

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
        ];
    }

}
