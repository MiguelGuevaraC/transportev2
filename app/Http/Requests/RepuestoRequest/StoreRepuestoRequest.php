<?php
namespace App\Http\Requests\RepuestoRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;

/**
 * @OA\Schema(
 *     schema="RepuestoRequest",
 *     title="RepuestoRequest",
 *     description="Request model for Repuesto information with filters and sorting",
 *     required={"name", "price_compra", "category_id"},
 *     @OA\Property(property="name", type="string", maxLength=255, description="Nombre del repuesto"),
 *     @OA\Property(property="price_compra", type="number", format="float", minimum=0, description="Precio de compra del repuesto"),
 *     @OA\Property(property="category_id", type="integer", description="ID de la categoría a la que pertenece el repuesto"),
 * )
 */

class StoreRepuestoRequest extends StoreRequest
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

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'price_compra' => 'required|numeric|min:0',
            'category_id'  => 'required|exists:categories,id,deleted_at,NULL',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'          => 'El nombre debe ser una cadena de texto.',
            'name.max'             => 'El nombre no debe superar los 255 caracteres.',
            'price_compra.numeric' => 'El precio de compra debe ser un valor numérico.',
            'price_compra.min'     => 'El precio de compra no puede ser negativo.',
            'category_id.exists'   => 'La categoría seleccionada no existe o está eliminada.',
        ];
    }

}
