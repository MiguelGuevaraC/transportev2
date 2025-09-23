<?php
namespace App\Http\Requests\TireRequest;

use App\Http\Requests\UpdateRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateTireRequest extends UpdateRequest
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
        $tireId = $this->route('id'); // o simplemente: $this->tire->id;

        return [
            // 'code' => [
            //     'nullable',
            //     'string',
            //     Rule::unique('tires', 'code')
            //     ->ignore($tireId)
            //     ->whereNull('deleted_at'),
            // ],
            'condition' => ['nullable', 'string'],
            'retread_number' => ['nullable', 'integer'],
            'entry_date' => ['nullable', 'date'],
            'supplier_id' => ['nullable', 'integer', 'exists:people,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],

            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'design_id' => ['nullable', 'integer', 'exists:designs,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],

            'tire_measure_id' => ['nullable', 'integer', 'exists:tire_measures,id'],
            'number_fact' => ['nullable', 'string'],

            // 'material' => ['nullable', 'string'],
            // 'brand' => ['nullable', 'string'],
            // 'design' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'size' => ['nullable', 'string'],
            'dot' => ['nullable', 'string'],
            'tread_type' => ['nullable', 'string'],
            'current_tread' => ['nullable', 'numeric'],
            'minimum_tread' => ['nullable', 'numeric'],
            'tread' => ['nullable', 'numeric'],
            'shoulder1' => ['nullable', 'numeric'],
            'shoulder2' => ['nullable', 'numeric'],
            'shoulder3' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código del neumático es obligatorio.',
            'code.string' => 'El código debe ser una cadena de texto.',
            'entry_date.required' => 'La fecha de ingreso es obligatoria.',
            'entry_date.date' => 'La fecha de ingreso no es válida.',

            'supplier_id.integer' => 'El proveedor debe ser un número.',
            'supplier_id.exists' => 'El proveedor no existe en el sistema.',
            'retread_number.integer' => 'El número de reencauche debe ser numérico.',

            'current_tread.numeric' => 'La cocada actual debe ser un número.',
            'minimum_tread.numeric' => 'La cocada mínima debe ser un número.',
            'tread.numeric' => 'La cocada debe ser un número.',
            'shoulder1.numeric' => 'El ribete 1 debe ser un número.',
            'shoulder2.numeric' => 'El ribete 2 debe ser un número.',
            'shoulder3.numeric' => 'El ribete 3 debe ser un número.',

            'vehicle_id.integer' => 'El ID del vehículo debe ser un número entero.',
            'vehicle_id.exists' => 'El vehículo seleccionado no existe.',

            'material_id.required' => 'El campo material es obligatorio.',
            'material_id.integer' => 'El campo material debe ser un número entero.',
            'material_id.exists' => 'El material seleccionado no es válido.',
            'design_id.required' => 'El campo diseño es obligatorio.',
            'design_id.integer' => 'El campo diseño debe ser un número entero.',
            'design_id.exists' => 'El diseño seleccionado no es válido.',
            'brand_id.required' => 'El campo marca es obligatorio.',
            'brand_id.integer' => 'El campo marca debe ser un número entero.',
            'brand_id.exists' => 'La marca seleccionada no es válida.',

        ];
    }

}
