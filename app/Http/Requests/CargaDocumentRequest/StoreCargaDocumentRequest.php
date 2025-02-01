<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="CargaDocumentRequest",
 *     title="CargaDocumentRequest",
 *     description="Request model for loading a document with product details",
 *     required={"movement_date", "quantity", "unit_price", "total_cost", "weight", "movement_type", "product_id", "person_id"},
 *     @OA\Property(property="movement_date", type="string", format="date", description="Date of the movement"),
 *     @OA\Property(property="quantity", type="number", format="float", description="Quantity of the product in the movement"),
 *     @OA\Property(property="unit_price", type="number", format="float", description="Unit price of the product"),
 *     @OA\Property(property="total_cost", type="number", format="float", description="Total cost of the product in the movement"),
 *     @OA\Property(property="weight", type="number", format="float", description="Weight of the product in the movement"),
 *     @OA\Property(property="movement_type", type="string", description="Type of movement (e.g., entry, exit, etc.)"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Optional comment about the movement"),
 *     @OA\Property(property="product_id", type="integer", description="ID of the product being moved"),
 *     @OA\Property(property="person_id", type="integer", description="ID of the person associated with the movement"),
 * )
 */

class StoreCargaDocumentRequest extends StoreRequest
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
            'movement_date'   => 'required|date',
            'quantity'        => 'required|numeric|min:0',
            'unit_price'      => 'required|numeric|min:0',
            'total_cost'      => 'required|numeric|min:0',
            'weight'          => 'required|numeric|min:0',
            'movement_type'   => 'required|string',

            'comment'         => 'nullable|string|max:500',
            'product_id'      => 'required|exists:products,id,deleted_at,NULL',
            'person_id'       => 'required|exists:people,id,deleted_at,NULL',
        ];
    }

    /**
     * Obtener los mensajes de error personalizados para la validación.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'movement_date.required'   => 'La fecha del movimiento es obligatoria.',
            'movement_date.date'       => 'La fecha del movimiento debe ser una fecha válida.',
    
            'quantity.required'        => 'La cantidad es un campo obligatorio.',
            'quantity.numeric'         => 'La cantidad debe ser un número.',
            'quantity.min'             => 'La cantidad no puede ser menor a 0.',
    
            'unit_price.required'      => 'El precio unitario es un campo obligatorio.',
            'unit_price.numeric'       => 'El precio unitario debe ser un número.',
            'unit_price.min'           => 'El precio unitario no puede ser menor a 0.',
    
            'total_cost.required'      => 'El costo total es obligatorio.',
            'total_cost.numeric'       => 'El costo total debe ser un número.',
            'total_cost.min'           => 'El costo total no puede ser menor a 0.',
    
            'weight.numeric'           => 'El peso debe ser un número.',
            'weight.min'               => 'El peso no puede ser menor a 0.',
            'weight.required'      => 'El peso es obligatorio.',

            'movement_type.required'   => 'El tipo de movimiento es obligatorio.',
            'movement_type.string'     => 'El tipo de movimiento debe ser un texto válido.',
    
            'comment.string'           => 'El comentario debe ser un texto.',
            'comment.max'              => 'El comentario no puede superar los 500 caracteres.',
    
            'product_id.required'      => 'El producto es obligatorio.',
            'product_id.exists'        => 'El producto seleccionado no es válido o ha sido eliminado.',
    
            'person_id.required'       => 'La persona es obligatoria.',
            'person_id.exists'         => 'La persona seleccionada no es válida o ha sido eliminada.',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
