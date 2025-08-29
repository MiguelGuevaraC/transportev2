<?php

namespace App\Http\Requests\DocAlmacenDetailRequest;

use App\Http\Requests\UpdateRequest;

/**
 * @OA\Schema(
 *     schema="UpdateDocAlmacenDetailRequest",
 *     @OA\Property(property="doc_almacen_id", type="integer")
 *     @OA\Property(property="tire_id", type="integer")
 *     @OA\Property(property="quantity", type="integer")
 *     @OA\Property(property="unit_price", type="decimal")
 *     @OA\Property(property="total_value", type="decimal")
 *     @OA\Property(property="note", type="text")
 * )
 */
class UpdateDocAlmacenDetailRequest extends UpdateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'doc_almacen_id' => ['nullable', 'integer'],
            'tire_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer'],
            'unit_price' => ['nullable', 'decimal'],
            'total_value' => ['nullable', 'decimal'],
            'note' => ['nullable', 'text'],
        ];
    }

    public function messages()
    {
        return [
            'doc_almacen_id.required' => 'El campo doc_almacen_id es obligatorio.',
            'doc_almacen_id.doc_almacen_id' => 'El formato del campo doc_almacen_id es inválido.',
            'tire_id.required' => 'El campo tire_id es obligatorio.',
            'tire_id.tire_id' => 'El formato del campo tire_id es inválido.',
            'quantity.required' => 'El campo quantity es obligatorio.',
            'quantity.quantity' => 'El formato del campo quantity es inválido.',
            'unit_price.required' => 'El campo unit_price es obligatorio.',
            'unit_price.unit_price' => 'El formato del campo unit_price es inválido.',
            'total_value.required' => 'El campo total_value es obligatorio.',
            'total_value.total_value' => 'El formato del campo total_value es inválido.',
            'note.required' => 'El campo note es obligatorio.',
            'note.note' => 'El formato del campo note es inválido.'
        ];
    }
}