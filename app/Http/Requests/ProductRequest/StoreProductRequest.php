<?php
namespace App\Http\Requests\ProductRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="ProductRequest",
 *     title="ProductRequest",
 *     description="Request model for Product information with filters and sorting",
 *     required={"id", "quantity", "person_id"},
 *     @OA\Property(property="id", type="integer", nullable=true, description="ID of the Product"),
 *     @OA\Property(property="tarifa", type="string", nullable=true, description="Tarifa description"),
 *     @OA\Property(property="quantity", type="string", nullable=true, description="Quantity of the item"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Description of the Product"),
 *     @OA\Property(property="unity_id", type="string", nullable=true, description="ID of the unity associated with the Product"),
 *     @OA\Property(property="person_id", type="string", nullable=true, description="ID of the person associated with the Product")
 * )
 */


class StoreProductRequest extends StoreRequest
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
            'description' => 'required|string', // Descripción es obligatoria y debe ser un texto
            'weight' => 'required|numeric|min:0', // El peso es obligatorio, debe ser numérico y no menor a 0
    
            'unity_id' => 'required|exists:unities,id,deleted_at,NULL', // La unidad es obligatoria y debe existir
            'person_id' => 'required|exists:people,id,deleted_at,NULL', // La persona es obligatoria y debe existir
        ];
    }
    
    public function messages()
    {
        return [
            'description.required'   => 'La descripción es obligatoria.',
            'description.string'     => 'La descripción debe ser un texto válido.',
            
            'weight.required'        => 'El peso es obligatorio.',
            'weight.numeric'         => 'El peso debe ser un número.',
            'weight.min'             => 'El peso no puede ser menor a 0.',
    
            'unity_id.required'      => 'La unidad es obligatoria.',
            'unity_id.exists'        => 'La unidad seleccionada no es válida o ha sido eliminada.',
            
            'person_id.required'     => 'El ID de la persona es obligatorio.',
            'person_id.exists'       => 'La persona seleccionada no es válida o ha sido eliminada.',
        ];
    }
    
    
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
