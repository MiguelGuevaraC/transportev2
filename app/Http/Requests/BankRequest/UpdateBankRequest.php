<?php
namespace App\Http\Requests\BankRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateBankRequest extends UpdateRequest
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
                Rule::unique('banks', 'name')->whereNull('deleted_at')->ignore($unitId),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nombre del banco es obligatorio.',
            'name.unique'   => 'El nombre del banco ya está en uso.',
        ];
    }

}
