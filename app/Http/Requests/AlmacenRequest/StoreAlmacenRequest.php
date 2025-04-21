<?php
namespace App\Http\Requests\AlmacenRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="AlmacenRequest",
 *     title="AlmacenRequest",
 *     description="Request model for Almacen information with filters and sorting",
 *     required={"name", "address"},
 *     @OA\Property(property="name", type="string", nullable=false, description="Nombre del almacén"),
 *     @OA\Property(property="address", type="string", nullable=false, description="Dirección del almacén"),
 *     @OA\Property(property="status", type="string", nullable=true, description="Estado del almacén")
 * )
 */

class StoreAlmacenRequest extends StoreRequest
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
            'name'    => ['required', 'string'],
            'address' => ['required', 'string'],
            'status'  => ['nullable', 'string','in:Activo,Inactivo'],
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'    => 'El nombre del almacén es obligatorio.',
            'name.string'      => 'El nombre del almacén debe ser una cadena de texto.',
            'address.required' => 'La dirección del almacén es obligatoria.',
            'address.string'   => 'La dirección del almacén debe ser una cadena de texto.',
            'status.string'    => 'El estado del almacén debe ser una cadena de texto.',
            'status.in'     => 'El estado del almacén debe ser "Activo" o "Inactivo".',
        ];
    }

}
