<?php

namespace App\Http\Requests\CarrierGuideRequest;

use Illuminate\Foundation\Http\FormRequest;

class SubcontractDataRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permitir el acceso a todos los usuarios
    }

    public function rules()
    {
        return [
            'docdriver' => 'required',
            'typedocdriver' => 'required|in:DNI,CARNET,RUC',
            'namedriver' => 'required|max:255',
            'lastnamedriver' => 'required|max:255',
            'licenciadriver' => 'required|max:9',
            'placa1' => 'required|max:10',
            'placa2' => 'required|max:10',
            'mtc1' => 'required|max:10',
            'mtc2' => 'required|max:10',
        ];
    }

    public function messages()
    {
        return [
            'docdriver.required' => 'El campo Documento del conductor es obligatorio.',
            'typedocdriver.required' => 'El campo Tipo de documento del conductor es obligatorio.',
            'typedocdriver.in' => 'El campo Tipo de documento del conductor debe ser uno de los siguientes valores: DNI, CARNET, RUC.',
            'namedriver.required' => 'El campo Nombre del conductor es obligatorio.',
            'namedriver.max' => 'El campo Nombre del conductor no puede tener más de :max caracteres.',
            'lastnamedriver.required' => 'El campo Apellido del conductor es obligatorio.',
            'lastnamedriver.max' => 'El campo Apellido del conductor no puede tener más de :max caracteres.',
            'licenciadriver.required' => 'El campo Licencia del conductor es obligatorio.',
            'licenciadriver.max' => 'El campo Licencia del conductor no puede tener más de :max caracteres.',
            'placa1.required' => 'El campo Placa del vehículo 1 es obligatorio.',
            'placa1.max' => 'El campo Placa del vehículo 1 no puede tener más de :max caracteres.',
            'placa2.required' => 'El campo Placa del vehículo 2 es obligatorio.',
            'placa2.max' => 'El campo Placa del vehículo 2 no puede tener más de :max caracteres.',
            'mtc1.required' => 'El campo MTC del vehículo 1 es obligatorio.',
            'mtc1.max' => 'El campo MTC del vehículo 1 no puede tener más de :max caracteres.',
            'mtc2.required' => 'El campo MTC del vehículo 2 es obligatorio.',
            'mtc2.max' => 'El campo MTC del vehículo 2 no puede tener más de :max caracteres.',
        ];
    }
}
