<?php
namespace App\Http\Requests\CheckListRequest;

use App\Http\Requests\StoreRequest;
use App\Models\CheckListItem;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StoreCheckListRequest",
 *     title="Store Check List Request",
 *     description="Request model for creating a Check List",
 *     required={"date_check_list", "vehicle_id"},

 *     @OA\Property(property="date_check_list", type="string", format="date", description="Fecha del Check List (YYYY-MM-DD)"),
 *     @OA\Property(property="vehicle_id", type="integer", description="ID del Vehículo asociado"),
 *     @OA\Property(property="observation", type="string", nullable=true, description="Observaciones generales"),
 *     @OA\Property(property="status", type="string", enum={"Activo", "Inactivo"}, description="Estado del Check List")
 * )
 */

class StoreCheckListRequest extends StoreRequest
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
            'date_check_list'                => 'required|date',
            'vehicle_id'                     => 'required|integer|exists:vehicles,id', // Asumiendo que existe tabla vehicles
            'observation'                    => 'nullable|string|max:1000',
            'status'                         => 'nullable|string|in:Activo,Inactivo', // Puedes ajustar los estados válidos

            'check_list_items'               => 'nullable|array',
            'check_list_items.*.id'          => 'required|integer|exists:check_list_items,id',
            'check_list_items.*.observation' => 'nullable|string',
            'check_list_items.*.is_selected' => 'required|boolean',

        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('check_list_items', []);

            foreach ($items as $index => $item) {
                // Verificamos si 'is_selected' es false y 'observation' está vacío
                if (isset($item['is_selected']) && $item['is_selected'] == false && empty($item['observation'])) {
                    $checkListItem = CheckListItem::find($item['id']);

                    $errorMessage = $checkListItem
                    ? "El item '{$checkListItem->name}' debe completar la observación cuando no esté seleccionado."
                    : "El ítem con ID {$item['id']} no existe.";

                    // Añadir el mensaje de error
                    $validator->errors()->add("check_list_items.{$index}.observation", $errorMessage);
                }
            }
        });
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
            'check_list_item_ids.array'                  => 'El campo de ítems debe ser un array.',
            'check_list_item_ids.*.integer'              => 'Cada ID de ítem debe ser un número entero.',
            'check_list_item_ids.*.exists'               => 'El ítem con ID :input no existe en la base de datos.',

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
