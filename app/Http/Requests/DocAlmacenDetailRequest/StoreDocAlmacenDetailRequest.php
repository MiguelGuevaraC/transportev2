<?php

namespace App\Http\Requests\DocAlmacenDetailRequest;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="StoreDocAlmacenDetailRequest",
 *     required={"doc_almacen_id", "tire_id", "quantity", "unit_price", "total_value", "note"},
 *     @OA\Property(property="doc_almacen_id", type="integer")
 *     @OA\Property(property="tire_id", type="integer")
 *     @OA\Property(property="quantity", type="integer")
 *     @OA\Property(property="unit_price", type="decimal")
 *     @OA\Property(property="total_value", type="decimal")
 *     @OA\Property(property="note", type="text")
 * )
 */
class StoreDocAlmacenDetailRequest extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'doc_almacen_id' => ['required', 'integer'],
            'tire_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
            'unit_price' => ['required', 'decimal'],
            'total_value' => ['required', 'decimal'],
            'note' => ['required', 'text'],
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