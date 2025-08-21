<?php

namespace App\Http\Requests\OptionMenuRequest;

use App\Http\Requests\StoreRequest;
/**
 * @OA\Schema(
 *     schema="StoreGroupMenuRequest",
 *     required={"name", "icon"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="icon", type="string")
 * )
 */
class StoreGroupMenuRequest extends StoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 🔑 name debe ser único en group_menus
            'name' => 'required|string|max:100',
            'state' => 'nullable',
            'icon' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser un texto.',
            'name.max' => 'El campo nombre no puede exceder 100 caracteres.',
            'name.unique' => 'El nombre ya existe en otro grupo de menú.',

            'icon.required' => 'El campo icono es obligatorio.',
            'icon.string' => 'El campo icono debe ser un texto.',
            'icon.max' => 'El campo icono no puede exceder 100 caracteres.',
        ];
    }
}
