<?php
namespace App\Http\Requests\UnityRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UnityRequest",
 *     title="UnityRequest",
 *     description="Request model for Unity information with filters and sorting",
 *     required={"id", "name", "code"},
 *     @OA\Property(property="name", type="string", nullable=true, description="Nombre Unidad"),
 *     @OA\Property(property="code", type="string", nullable=true, description="Codigo Unidad"),

 * )
 */


class StoreUnityRequest extends StoreRequest
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
                Rule::unique('unities', 'name')->whereNull('deleted_at'),
            ],
            'code' => [
                'required',
                'string',
                Rule::unique('unities', 'code')->whereNull('deleted_at'),
            ],
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Nombre de la unidad es obligatorio.',
            'name.unique'   => 'El nombre de la unidad ya está en uso.',
            'code.required' => 'Código de la unidad es obligatorio.',
            'code.unique'   => 'El código de la unidad ya está en uso.',
        ];
    }
    
    
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
