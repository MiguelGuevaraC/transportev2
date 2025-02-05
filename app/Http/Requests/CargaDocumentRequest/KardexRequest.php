<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class KardexRequest extends StoreRequest
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
        return [
            'from'       => 'required|date', // Campo obligatorio y debe ser una fecha válida
            'to'         => 'nullable|date', // No es obligatorio y debe ser una fecha válida si se proporciona
        ];
    }
    
    public function messages()
    {
        return [
            'from.required'       => 'El campo "from" es obligatorio.',
            'from.date'           => 'El campo "from" debe ser una fecha válida.',
            'to.nullable'         => 'El campo "to" es opcional.',
            'to.date'             => 'El campo "to" debe ser una fecha válida si se proporciona.',
        ];
    }
    

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
