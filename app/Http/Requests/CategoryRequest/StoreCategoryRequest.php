<?php
namespace App\Http\Requests\CategoryRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="CategoryRequest",
 *     title="CategoryRequest",
 *     description="Request model for Category information with filters and sorting",
 *     required={"id", "name"},
 *     @OA\Property(property="name", type="string", nullable=true, description="Nombre Category"),
 *     @OA\Property(property="status", type="string", nullable=true, description="Estado Category"),
 * )
 */

class StoreCategoryRequest extends StoreRequest
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
                Rule::unique('categories', 'name')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nombre de la categoria es obligatorio.',
            'name.unique'   => 'El nombre de la categoria ya está en uso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
