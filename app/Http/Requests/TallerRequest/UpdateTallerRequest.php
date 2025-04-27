<?php
namespace App\Http\Requests\TallerRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateTallerRequest extends UpdateRequest
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
                Rule::unique('tallers', 'name')->whereNull('deleted_at')->ignore($unitId),
            ],
            'status' => ['nullable', 'string', 'in:ACTIVO,INACTIVO'],
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

}
