<?php
namespace App\Http\Requests\UnityRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUnityRequest extends UpdateRequest
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
            'name' => [
                'required',
                'string',
                Rule::unique('unities', 'name')->whereNull('deleted_at')->ignore($unitId),
            ],
            'code' => [
                'required',
                'string',
                Rule::unique('unities', 'code')->whereNull('deleted_at')->ignore($unitId),
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

}
