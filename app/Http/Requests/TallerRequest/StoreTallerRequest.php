<?php
namespace App\Http\Requests\TallerRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="TallerRequest",
 *     title="TallerRequest",
 *     description="Request model for Taller information with filters and sorting",
 *     required={"id", "name"},
 *     @OA\Property(property="name", type="string", nullable=true, description="Nombre Taller"),
 *     @OA\Property(property="status", type="string", nullable=true, description="Estado Taller"),
 * )
 */

class StoreTallerRequest extends StoreRequest
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
            'name' => [
                'required',
                'string',
                Rule::unique('tallers', 'name')->whereNull('deleted_at'),
            ],
            'address' => ['nullable','string'],
            'type' => ['nullable', 'string', 'in:PINTURA,ELECTRICO,MECANICO'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nombre del taller es obligatorio.',
            'name.unique'   => 'El nombre del taller ya está en uso.',
            'type.in' => 'El tipo de mantenimiento debe ser uno de los siguientes: PINTURA, ELECTRICO, MECANICO.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
