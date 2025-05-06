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
            'branch_office_id'               => 'required|integer|exists:branch_offices,id',
            'date_check_list'                => 'nullable|date',
            'vehicle_id'                     => 'nullable|integer|exists:vehicles,id', // Asumiendo que existe tabla vehicles
            'observation'                    => 'nullable|string|max:1000',
            'status'                         => 'nullable|string|in:Activo,Inactivo', // Puedes ajustar los estados válidos
            'check_list_items'               => 'nullable|array',
            'check_list_items.*.id'          => 'required|integer|exists:check_list_items,id',
            'check_list_items.*.observation' => 'nullable|string|required_if:check_list_items.*.is_selected,false',
            'check_list_items.*.is_selected' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_office_id.required'                  => 'Debe seleccionar una sucursal.',
            'branch_office_id.integer'                   => 'El ID de la sucursal debe ser un número entero.',
            'branch_office_id.exists'                    => 'La sucursal seleccionada no existe en el sistema.',

            'date_check_list.required'                   => 'La fecha del Check List es obligatoria.',
            'vehicle_id.required'                        => 'Debe seleccionar un vehículo.',
            'vehicle_id.exists'                          => 'El vehículo seleccionado no existe.',
            'status.required'                            => 'El estado es obligatorio.',
            'status.in'                                  => 'El estado debe ser "Activo" o "Inactivo".',

            // Validación del campo check_list_item_ids
            'check_list_items.array'                     => 'La lista de ítems debe ser un arreglo.',

            'check_list_items.*.id.required'             => 'Cada ítem debe tener un ID.',
            'check_list_items.*.id.integer'              => 'El ID de cada ítem debe ser un número entero.',
            'check_list_items.*.id.exists'               => 'El ID de uno de los ítems no existe en la base de datos.',

            'check_list_items.*.observation.string'      => 'La observación debe ser una cadena de texto.',

            'check_list_items.*.is_selected.required'    => 'Debe indicar si el ítem está seleccionado.',
            'check_list_items.*.is_selected.boolean'     => 'El valor de selección debe ser verdadero o falso.',
            'check_list_items.*.observation.required_if' => 'El ítem debe tener una observación cuando no esté seleccionado.',
        ];
    }

}
