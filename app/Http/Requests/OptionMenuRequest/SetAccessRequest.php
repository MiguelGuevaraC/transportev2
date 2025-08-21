<?php

namespace App\Http\Requests\OptionMenuRequest;

use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * @OA\Schema(
 *     schema="StoreOptionMenuRequest",
 *     required={"name", "action"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="action", type="string"),
 *     @OA\Property(property="route", type="string"),
 *     @OA\Property(property="groupMenu_id", type="integer")
 * )
 */
class SetAccessRequest extends UpdateRequest
{
    public function authorize(): bool
    {
        // Si quieres que solo usuarios con permisos puedan usarlo,
        // puedes poner lógica aquí. Por ahora true:
        return true;
    }

    public function rules(): array
    {
        return [
            'typeUserId' => 'prohibited', // evitamos que venga en body
            'optionsMenu' => 'required|array',
            'optionsMenu.*.id' => 'required|string|exists:option_Menus,id',
            'optionsMenu.*.state' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'typeUserId.required' => 'El ID del tipo de usuario es obligatorio.',
            'typeUserId.exists' => 'El tipo de usuario no existe.',
            'optionsMenu.required' => 'Debe enviar al menos un permiso.',
            'optionsMenu.array' => 'El campo optionsMenu debe ser un array.',
            'optionsMenu.*.id.required' => 'Cada permiso debe tener un ID.',
            'optionsMenu.*.id.exists' => 'Alguno de los permisos no existe en option_Menus.',
            'optionsMenu.*.state.required' => 'Cada permiso debe incluir el estado.',
            'optionsMenu.*.state.boolean' => 'El campo state debe ser true o false.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $typeUserId = $this->route('typeUserId');

            $role = Role::findOrFail($typeUserId);
            if (!$role) {
                $validator->errors()->add('typeUserId', 'El tipo de usuario no existe.');
            }
        });
    }
}