<?php

namespace App\Http\Requests\OptionMenuRequest;

use App\Http\Requests\StoreRequest;

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
class StoreOptionMenuRequest extends StoreRequest
{
    public function authorize() { return true; }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:100',
            'action'       => 'required|string|max:50',
            'route'        => 'nullable|string|max:100',
            'groupMenu_id' => 'nullable|exists:group_menus,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El campo nombre es obligatorio.',
            'name.string'           => 'El campo nombre debe ser un texto.',
            'name.max'              => 'El campo nombre no puede exceder 100 caracteres.',

            'action.required'       => 'El campo acción es obligatorio.',
            'action.string'         => 'El campo acción debe ser un texto.',
            'action.max'            => 'El campo acción no puede exceder 50 caracteres.',

            'route.string'          => 'El campo ruta debe ser un texto.',
            'route.max'             => 'El campo ruta no puede exceder 100 caracteres.',

            'groupMenu_id.exists'   => 'El grupo seleccionado no existe.',
        ];
    }
}
