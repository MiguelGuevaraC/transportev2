<?php
namespace App\Http\Requests\BankMovementRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateBankMovementRequest extends UpdateRequest
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
        $id = $this->route('id'); // Obtén el ID de la ruta, que se asume que es el ID del usuario
        return [
            'type_moviment'          => 'required|string|in:ENTRADA,SALIDA',
            'date_moviment'          => 'required',
            'total_moviment'         => 'required|numeric|min:0',
            'currency'               => 'required|string|max:3',
            'comment'                => 'nullable|string',
            'number_operation'       => [
                'nullable',
                'string',
                Rule::unique('bank_movements', 'number_operation')
                    ->ignore($id) // Ignora el ID actual
                    ->whereNull('deleted_at'),
            ],
            'bank_id'                => 'required|exists:banks,id,deleted_at,NULL',
            'bank_account_id'        => [
                'required',
                Rule::exists('bank_accounts', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    $status = DB::table('bank_accounts')->where('id', $value)->value('status');
                    if ($status === 'inactiva') {
                        $fail('No se pueden realizar movimientos en una cuenta bancaria inactiva.');
                    }
                },
            ],
            'transaction_concept_id' => 'required|exists:transaction_concepts,id,deleted_at,NULL',
            'person_id'              => 'required|exists:people,id,deleted_at,NULL',
        ];
    }

    public function messages()
    {
        return [
            'type_moviment.required'          => 'El tipo de movimiento es obligatorio.',
            'type_moviment.string'            => 'El tipo de movimiento debe ser un texto válido.',
            'type_moviment.in'                => 'El tipo de movimiento debe ser "ENTRADA" o "SALIDA".',

            'date_moviment.required'          => 'La fecha del movimiento es obligatoria.',
            'date_moviment.date'              => 'La fecha del movimiento debe ser una fecha válida.',

            'total_moviment.required'         => 'El total del movimiento es obligatorio.',
            'total_moviment.numeric'          => 'El total del movimiento debe ser un número.',
            'total_moviment.min'              => 'El total del movimiento no puede ser un valor negativo.',

            'currency.required'               => 'La moneda es obligatoria.',
            'currency.string'                 => 'La moneda debe ser un texto válido.',
            'currency.max'                    => 'La moneda debe tener un máximo de 3 caracteres (ej. USD, EUR).',

            'comment.string'                  => 'El comentario debe ser un texto válido.',

            'bank_id.required'                => 'El banco es obligatorio.',
            'bank_id.exists'                  => 'El banco seleccionado no existe o ha sido eliminado.',

            'bank_account_id.required'        => 'La cuenta bancaria es obligatoria.',
            'bank_account_id.exists'          => 'La cuenta bancaria seleccionada no existe o ha sido eliminada.',

            'transaction_concept_id.required' => 'El concepto de transacción es obligatorio.',
            'transaction_concept_id.exists'   => 'El concepto de transacción seleccionado no existe o ha sido eliminado.',

            'person_id.required'              => 'La persona es obligatoria.',
            'person_id.exists'                => 'La persona seleccionada no existe o ha sido eliminada.',

            'number_operation.unique' => 'El número de operación ya está en uso.',
        ];
    }

}
