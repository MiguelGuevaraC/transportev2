<?php
declare(strict_types=1);

namespace App\Http\Requests\TireRequest;

use App\Http\Requests\StoreRequest;

class GenerateCodeRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base' => ['required', 'string'],
            // límite global de 1000 además de min:1
            'cant' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'base.required' => 'La base es obligatoria.',
            'base.string' => 'La base debe ser una cadena de texto.',
            'cant.required' => 'La cantidad es obligatoria.',
            'cant.integer' => 'La cantidad debe ser un número entero.',
            'cant.min' => 'La cantidad debe ser al menos 1.',
            'cant.max' => 'La cantidad no puede ser mayor a 1000.',
        ];
    }

    /**
     * Validación adicional dependiente de la longitud de la base:
     * - la base no puede ocupar 12 o más caracteres.
     * - la cantidad no puede exceder el máximo posible según los dígitos disponibles.
     * - además se respeta el tope absoluto de 1000 códigos.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $base = (string) $this->input('base', '');
            $cant = (int) $this->input('cant', 0);

            // longitud real considerando multibyte
            $baseLen = mb_strlen($base, 'UTF-8');
            $totalLength = 12;
            $seqLength = $totalLength - $baseLen;

            if ($seqLength <= 0) {
                $validator->errors()->add(
                    'base',
                    "La base ocupa {$baseLen} caracteres. Debe tener menos de {$totalLength} caracteres para poder añadir la secuencia."
                );
                return;
            }

            // máximo teórico según dígitos disponibles: 10^seqLength - 1
            $theoreticalMax = (int) (pow(10, $seqLength) - 1);

            // tope absoluto de la petición
            $absoluteLimit = 1000;

            // el máximo efectivo es el menor entre teórico y tope absoluto
            $effectiveMax = min($theoreticalMax, $absoluteLimit);

            if ($cant > $effectiveMax) {
                if ($effectiveMax === $absoluteLimit && $theoreticalMax > $absoluteLimit) {
                    // el tope que corta es el absoluto de 1000
                    $validator->errors()->add(
                        'cant',
                        "La cantidad solicitada ({$cant}) excede el tope máximo permitido ({$absoluteLimit})."
                    );
                } else {
                    // el tope que corta es la capacidad de la secuencia (ej: 9999)
                    $validator->errors()->add(
                        'cant',
                        "La cantidad solicitada ({$cant}) excede el máximo posible ({$theoreticalMax}) para una secuencia de {$seqLength} dígitos (base de {$baseLen} caracteres)."
                    );
                }
            }
        });
    }
}
