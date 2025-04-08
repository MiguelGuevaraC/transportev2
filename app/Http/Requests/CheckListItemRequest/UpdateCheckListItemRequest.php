<?php
namespace App\Http\Requests\CheckListItemRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCheckListItemRequest extends UpdateRequest
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
        $unitId = $this->route('id'); // Obtén el ID de la ruta, que se asume que es el ID del usuario

        return [
            'name'   => [
                'required',
                'string',
                Rule::unique('categories', 'name')->whereNull('deleted_at')->ignore($unitId),
            ],
            'status' => ['nullable', 'string', 'in:ACTIVO,INACTIVO'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nombre de la categoria es obligatorio.',
            'name.unique'   => 'El nombre de la categoria ya está en uso.',
        ];
    }

}
