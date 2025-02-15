<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\StoreRequest;
use App\Models\Product;
use App\Models\User;

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
            'product_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (! is_numeric($value) && ! is_array($value) && $value !== "null") {
                        return $fail('No se está enviando de forma correcta los productos.');
                    }

                    if ($value !== "null") {
                        $ids = is_array($value) ? $value : [$value];
                        if (Product::whereIn('id', $ids)->whereNull('deleted_at')->count() !== count($ids)) {
                            $fail('Uno o más productos no existen o han sido eliminados.');
                        }
                    }
                },
            ],
            'from'       => 'required|date',
            'to'         => 'nullable|date',
            'branchOffice_id'=> 'required|exists:branch_offices,id,deleted_at,NULL',
        ];
    }

    public function messages()
    {
        return [
            'from.required' => 'El campo "from" es obligatorio.',
            'from.date'     => 'El campo "from" debe ser una fecha válida.',
            'to.nullable'   => 'El campo "to" es opcional.',
            'to.date'       => 'El campo "to" debe ser una fecha válida si se proporciona.',

            'branchOffice_id.required'    => 'La Sucursal es obligatorio.',
            'branchOffice_id.exists'      => 'La Sucursal seleccionado no es válido o ha sido eliminado.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
