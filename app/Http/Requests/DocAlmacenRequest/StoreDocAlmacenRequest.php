<?php

namespace App\Http\Requests\DocAlmacenRequest;

use App\Http\Requests\StoreRequest;
use App\Models\Tire;

/**
 * @OA\Schema(
 *     schema="StoreDocAlmacenRequest",
 *     required={"concept_id", "type", "movement_date", "details"},
 *     @OA\Property(property="concept_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="INGRESO"),
 *     @OA\Property(property="movement_date", type="string", format="date", example="2025-08-01"),
 *     @OA\Property(property="note", type="string", example="Entrada al almacén"),
 *     @OA\Property(
 *         property="details",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             required={"tire_id", "quantity"},
 *             @OA\Property(property="tire_id", type="integer", example=5),
 *             @OA\Property(property="quantity", type="integer", example=10),
 *             @OA\Property(property="note", type="string", example="Detalle del tireo")
 *         )
 *     )
 * )
 */
class StoreDocAlmacenRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'concept_id' => ['required', 'integer', 'exists:concept_tire_operations,id'],
            'type' => ['required', 'string', 'in:INGRESO,EGRESO'],
            'movement_date' => ['required', 'date'],
            'note' => ['nullable'],

            'details' => ['required', 'array', 'min:1'],
            'details.*.tire_id' => ['required', 'integer', 'exists:tires,id'],
            'details.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'details.*.note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'concept_id.required' => 'El campo concept_id es obligatorio.',
            'concept_id.integer' => 'El campo concept_id debe ser un número entero.',
            'concept_id.exists' => 'El concept_id no existe en la tabla de conceptos.',

            'type.required' => 'El campo type es obligatorio.',
            'type.string' => 'El campo type debe ser una cadena de texto.',
            'type.in' => 'El campo type debe ser INGRESO o EGRESO.',

            'movement_date.required' => 'El campo movement_date es obligatorio.',
            'movement_date.date' => 'El campo movement_date debe tener un formato de fecha válido (YYYY-MM-DD).',

            'note.string' => 'El campo note debe ser una cadena de texto.',

            'details.required' => 'Debes ingresar al menos un detalle.',
            'details.array' => 'El campo details debe ser un arreglo.',
            'details.min' => 'Debe haber al menos un item en details.',

            'details.*.tire_id.required' => 'El campo tire_id es obligatorio en cada detalle.',
            'details.*.tire_id.integer' => 'El campo tire_id debe ser un número entero.',
            'details.*.tire_id.exists' => 'El tire_id no existe en la tabla de tires.',

            'details.*.quantity.required' => 'El campo quantity es obligatorio en cada detalle.',
            'details.*.quantity.numeric' => 'El campo quantity debe ser un número.',
            'details.*.quantity.min' => 'La cantidad debe ser mayor a 0.',

            'details.*.note.string' => 'El campo note en detalles debe ser una cadena de texto.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->type === 'EGRESO') {
                $sumaPorTire = [];

                // Sumamos cantidades por cada tireo
                foreach ($this->details as $detail) {
                    $tireId = $detail['tire_id'];
                    $quantity = $detail['quantity'];

                    if (!isset($sumaPorTire[$tireId])) {
                        $sumaPorTire[$tireId] = 0;
                    }

                    $sumaPorTire[$tireId] += $quantity;
                }

                $tiresInsuficientes = [];

                // Verificamos contra el stock
                foreach ($sumaPorTire as $tireId => $cantidadTotal) {
                    $tire = Tire::find($tireId);

                    if (!$tire) {
                        $validator->errors()->add("details", "El tireo con ID {$tireId} no existe.");
                        continue;
                    }

                    $stockActual = $tire->stock;

                    if ($stockActual < $cantidadTotal) {
                        $tiresInsuficientes[] = "<li><strong>{$tire->nombre}</strong>: stock actual <strong>{$stockActual}</strong>, solicitado <strong>{$cantidadTotal}</strong></li>";
                    }
                }

                if (!empty($tiresInsuficientes)) {
                    $mensaje = "<strong>Stock insuficiente para los siguientes tires:</strong><ol>" . implode('', $tiresInsuficientes) . "</ol>";
                    $validator->errors()->add('stock', $mensaje);
                }
            }
        });
    }


}
