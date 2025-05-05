<?php
namespace App\Http\Requests\ServiceRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;

/**
 * @OA\Schema(
 *     schema="ServiceRequest",
 *     title="ServiceRequest",
 *     description="Request model for creating a Service",
 *     required={"name", "description", "status"},
 *     @OA\Property(property="name", type="string", maxLength=255, description="Nombre del servicio"),
 *     @OA\Property(property="description", type="string", description="Descripción del servicio"),
 *     @OA\Property(property="status", type="string", enum={"ACTIVO", "INACTIVO"}, description="Estado del servicio"),
 * )
 */

class StoreServiceRequest extends StoreRequest
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
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'status'      => 'nullable|string|in:ACTIVO,INACTIVO',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'El campo nombre es obligatorio.',
            'name.string'          => 'El campo nombre debe ser una cadena de texto.',
            'name.max'             => 'El campo nombre no debe exceder los 255 caracteres.',

            'description.required' => 'El campo descripción es obligatorio.',
            'description.string'   => 'El campo descripción debe ser una cadena de texto.',

            'status.required'      => 'El campo estado es obligatorio.',
            'status.string'        => 'El campo estado debe ser una cadena de texto.',
            'status.in'            => 'El estado debe ser "ACTIVO" o "INACTIVO".',
        ];
    }

}
