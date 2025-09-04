<?php
declare(strict_types=1);

namespace App\Http\Requests\TireRequest;

use App\Http\Requests\StoreRequest;
use App\Models\Tire;
use Illuminate\Validation\Rule;

class StoreTireRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'nullable',
                'string',
                'required_without:codes',
                Rule::unique('tires', 'code')->whereNull('deleted_at'),
            ],

            'codes' => [
                'nullable',
                'array',
                'required_without:code',
                'min:1',
                'max:10000', // tope absoluto
            ],

            'codes.*' => [
                'string',
            ],

            // resto de reglas...
            'condition' => ['required', 'string'],
            'entry_date' => ['required', 'date'],
            'supplier_id' => ['nullable', 'integer', 'exists:people,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'design_id' => ['required', 'integer', 'exists:designs,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'type' => ['nullable', 'string'],
            'size' => ['nullable', 'string'],
            'dot' => ['nullable', 'string'],
            'tread_type' => ['nullable', 'string'],
            'current_tread' => ['nullable', 'numeric'],
            'minimum_tread' => ['nullable', 'numeric'],
            'tread' => ['nullable', 'numeric'],
            'shoulder1' => ['nullable', 'numeric'],
            'shoulder2' => ['nullable', 'numeric'],
            'shoulder3' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required_without' => 'Debe enviar el campo code o el array codes.',
            'codes.required_without' => 'Debe enviar el campo codes o el campo code.',
            'codes.array' => 'Codes debe ser un arreglo.',
            'codes.min' => 'Debe enviar al menos 1 código.',
            'codes.max' => 'No puede generar más de 10000 códigos a la vez.',
            'codes.*.string' => 'Cada código debe ser una cadena de texto.',

            'entry_date.required' => 'La fecha de ingreso es obligatoria.',
            'entry_date.date' => 'La fecha de ingreso no es válida.',

            'material_id.required' => 'El campo material es obligatorio.',
            'design_id.required' => 'El campo diseño es obligatorio.',
            'brand_id.required' => 'El campo marca es obligatorio.',
        ];
    }

    /**
     * Validación adicional:
     *  - normaliza (trim) los códigos
     *  - revisa duplicados dentro del array (y los reporta)
     *  - revisa códigos ya existentes en la BD (y los reporta indicando cuáles)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $baseCodes = $this->input('codes');

            // si no vienen codes, nada más que validar (el rule unique ya aplica para 'code')
            if (empty($baseCodes) || !is_array($baseCodes)) {
                return;
            }

            // normalizar (trim) y filtrar empty
            $codes = array_values(array_filter(array_map(fn($c) => (string) trim($c), $baseCodes), fn($v) => $v !== ''));

            if (count($codes) === 0) {
                $validator->errors()->add('codes', 'El arreglo codes no puede estar vacío o contener solo valores vacíos.');
                return;
            }

            // 1) duplicados dentro del arreglo de entrada
            $counts = array_count_values($codes);
            $duplicates = array_keys(array_filter($counts, fn($n) => $n > 1));

            if (!empty($duplicates)) {
                $validator->errors()->add('codes', 'Se detectaron códigos repetidos en la petición: ' . implode(', ', $duplicates));
                // no retornamos; también queremos informar si hay códigos ya en BD
            }

            // 2) códigos que ya existen en la BD (soft-deleted ignorados)
            $existing = Tire::whereIn('code', $codes)
                        ->whereNull('deleted_at')
                        ->pluck('code')
                        ->unique()
                        ->values()
                        ->all();

            if (!empty($existing)) {
                $validator->errors()->add('codes', 'Los siguientes códigos ya existen en el sistema: ' . implode(', ', $existing));
            }

            // opcional: re-assign normalized codes back to request so controller/servicio use la versión trimmed
            // Sólo posible si necesitas que el request contenga la versión normalizada:
            $this->merge(['codes' => $codes]);
        });
    }
}
