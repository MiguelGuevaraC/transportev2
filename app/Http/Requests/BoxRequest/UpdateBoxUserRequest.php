<?php
namespace App\Http\Requests\BoxRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\Box;
use App\Models\User;
use Illuminate\Validation\Validator;

class UpdateBoxUserRequest extends UpdateRequest
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
    public function rules()
    {
        $id = $this->route('id');

        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Mensajes de validación personalizados.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'El Id de usuario es obligatorio.',
            'user_id.integer'  => 'El ID del usuario debe ser un número entero.',
            'user_id.exists'   => 'El usuario seleccionado no es válido o ha sido eliminado.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $id     = $this->route('id'); // ID de la caja que se está actualizando
            $box    = Box::find($id);
            $userId = $this->input('user_id');

            // Verificar si la caja está activa
            if ($box && strtolower($box->status) === 'activa') {
                $validator->errors()->add('status', 'La caja debe estar cerrada para poder modificar el usuario asignado.');
            }

            // Verificar si el usuario ya tiene asignada otra caja distinta a esta
            $user = User::find($userId);
            if ($user && $user->box_id && $user->box_id != $id) {
                $otherBox = Box::find($user->box_id);

                if ($otherBox) {
                    $validator->errors()->add(
                        'user_id',
                        'El usuario ' . $user->username . ' ya tiene asignada la caja ' . $otherBox->name . ' (serie: ' . $otherBox->serie . ').'
                    );
                }
            }

        });
    }

}
