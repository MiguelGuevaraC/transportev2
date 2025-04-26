<?php
namespace App\Http\Requests\CheckListRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateCheckListRequest extends UpdateRequest
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

    public function rules(): array
    {
        return [
            'branch_office_id'      => 'required|integer|exists:branch_offices,id',
            'date_check_list'       => 'nullable|date',
            'vehicle_id'            => 'nullable|integer|exists:vehicles,id', // Asumiendo que existe tabla vehicles
            'observation'           => 'nullable|string|max:1000',
            'status'                => 'nullable|string|in:Activo,Inactivo', // Puedes ajustar los estados válidos

            'check_list_item_ids'   => 'nullable|array',
            'check_list_item_ids.*' => 'nullable|integer|exists:check_list_items,id', // Solo valida los elementos si el array no está vacío
        ];
    }

    public function messages(): array
    {
        return [
            'branch_office_id.required'     => 'Debe seleccionar una sucursal.',
            'branch_office_id.integer'      => 'El ID de la sucursal debe ser un número entero.',
            'branch_office_id.exists'       => 'La sucursal seleccionada no existe en el sistema.',

            'date_check_list.required'      => 'La fecha del Check List es obligatoria.',
            'vehicle_id.required'           => 'Debe seleccionar un vehículo.',
            'vehicle_id.exists'             => 'El vehículo seleccionado no existe.',
            'status.required'               => 'El estado es obligatorio.',
            'status.in'                     => 'El estado debe ser "Activo" o "Inactivo".',

            // Validación del campo check_list_item_ids
            'check_list_item_ids.array'     => 'El campo de ítems debe ser un array.',
            'check_list_item_ids.*.integer' => 'Cada ID de ítem debe ser un número entero.',
            'check_list_item_ids.*.exists'  => 'El ítem con ID :input no existe en la base de datos.',
        ];
    }

}
