<?php
namespace App\Http\Requests\ConceptTransactionRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateConceptTransactionRequest extends UpdateRequest
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
                Rule::unique('transaction_concepts', 'name')->whereNull('deleted_at')->ignore($unitId),
            ],
            'type' => ['required', Rule::in(['INGRESO', 'EGRESO'])], // Mejora la validación con Rule::in
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nombre del concepto es obligatorio.',
            'name.unique'   => 'El nombre del concepto ya está en uso.',
            'type.required' => 'El tipo del concepto es obligatorio.',
            'type.in'       => 'El tipo debe ser INGRESO o EGRESO.',
        ];
    }

}
