<?php
namespace App\Http\Requests\BankRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="BankRequest",
 *     title="BankRequest",
 *     description="Request model for Bank information with filters and sorting",
 *     required={"id", "name"},
 *     @OA\Property(property="name", type="string", nullable=true, description="Nombre Banco"),

 * )
 */


class StoreBankRequest extends StoreRequest
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
                Rule::unique('banks', 'name')->whereNull('deleted_at'),
            ],
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Nombre de la unidad es obligatorio.',
            'name.unique'   => 'El nombre de la unidad ya está en uso.',
        ];
    }
    
    
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
