<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\StoreRequest;
use App\Models\ProductStockByBranch;
use Illuminate\Validation\Validator;

/**
 * @OA\Schema(
 *     schema="CargaDocumentRequest",
 *     title="CargaDocumentRequest",
 *     description="Request model for loading a document with product details",
 *     required={"movement_date", "branchOffice_id", "person_id", "distribuidor_id", "movement_type", "details"},
 *     @OA\Property(property="movement_date", type="string", format="date", description="Date of the movement"),
 *     @OA\Property(property="branchOffice_id", type="integer", description="Branch office ID"),
 *     @OA\Property(property="person_id", type="integer", description="ID of the person associated with the movement"),
 *     @OA\Property(property="distribuidor_id", type="integer", description="ID of the distributor associated with the movement"),
 *     @OA\Property(property="movement_type", type="string", description="Type of movement (ENTRADA or SALIDA)"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Optional comment about the movement"),
 *     @OA\Property(
 *         property="details",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", nullable=true),
 *             @OA\Property(property="quantity", type="number", format="float"),
 *             @OA\Property(property="product_id", type="integer"),
 *             @OA\Property(property="almacen_id", type="integer"),
 *             @OA\Property(property="seccion_id", type="integer", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *         )
 *     )
 * )
 */
class StoreCargaDocumentRequest extends StoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'movement_date'             => 'required|date',
            'branchOffice_id'           => 'required|exists:branch_offices,id,deleted_at,NULL',
            'person_id'                 => 'required|exists:people,id,deleted_at,NULL',
            'distribuidor_id'           => 'required|exists:people,id,deleted_at,NULL',
            'movement_type'             => 'required|string|in:ENTRADA,SALIDA',
            'comment'                   => 'nullable|string|max:500',

            'details'                   => 'required|array|min:1',
            'details.*.id'              => 'nullable|integer',
            'details.*.quantity'        => 'required|numeric|min:1',
            'details.*.product_id'      => 'required|exists:products,id,deleted_at,NULL',
            'details.*.almacen_id'      => 'required|exists:almacens,id,deleted_at,NULL',
            'details.*.seccion_id'      => 'required|exists:seccions,id,deleted_at,NULL',
            'details.*.comment'         => 'nullable|string|max:500',
            'details.*.num_anexo'       => 'nullable|string|max:500',
            'details.*.date_expiration' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'movement_date.required'             => 'La fecha del movimiento es obligatoria.',
            'movement_date.date'                 => 'La fecha del movimiento debe ser una fecha válida.',

            'branchOffice_id.required'           => 'La Sucursal es obligatoria.',
            'branchOffice_id.exists'             => 'La Sucursal seleccionada no es válida o ha sido eliminada.',

            'person_id.required'                 => 'La persona es obligatoria.',
            'person_id.exists'                   => 'La persona seleccionada no es válida o ha sido eliminada.',

            'distribuidor_id.required'           => 'El distribuidor es obligatorio.',
            'distribuidor_id.exists'             => 'El distribuidor seleccionado no es válido o ha sido eliminado.',

            'movement_type.required'             => 'El tipo de movimiento es obligatorio.',
            'movement_type.string'               => 'El tipo de movimiento debe ser un texto válido.',
            'movement_type.in'                   => 'El tipo de movimiento debe ser ENTRADA o SALIDA.',

            'comment.string'                     => 'El comentario debe ser un texto.',
            'comment.max'                        => 'El comentario no puede superar los 500 caracteres.',

            'details.required'                   => 'Debe ingresar al menos un detalle.',
            'details.array'                      => 'El campo detalles debe ser un arreglo.',

            'details.*.quantity.required'        => 'La cantidad es obligatoria.',
            'details.*.quantity.numeric'         => 'La cantidad debe ser numérica.',
            'details.*.quantity.min'             => 'La cantidad debe ser al menos 1.',

            'details.*.product_id.required'      => 'El producto es obligatorio.',
            'details.*.product_id.exists'        => 'El producto seleccionado no es válido o ha sido eliminado.',

            'details.*.almacen_id.required'      => 'El almacén es obligatorio.',
            'details.*.almacen_id.exists'        => 'El almacén seleccionado no es válido o ha sido eliminado.',

            'details.*.seccion_id.required'      => 'La sección es obligatorio.',

            'details.*.seccion_id.exists'        => 'La sección seleccionada no es válida o ha sido eliminada.',

            'details.*.comment.string'           => 'El comentario del detalle debe ser un texto.',
            'details.*.comment.max'              => 'El comentario del detalle no puede superar los 500 caracteres.',

            'details.*.num_anexo.nullable'       => 'El número de anexo es opcional.',
            'details.*.num_anexo.string'         => 'El número de anexo debe ser una cadena de texto.',
            'details.*.num_anexo.max'            => 'El número de anexo no puede tener más de 500 caracteres.',

            'details.*.date_expiration.nullable' => 'La fecha de expiración es opcional.',
            'details.*.date_expiration.date'     => 'La fecha de expiración debe ser una fecha válida.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->movement_type === 'SALIDA') {
                foreach ($this->details as $index => $detail) {
                    $stock = ProductStockByBranch::where('product_id', $detail['product_id'])
                        ->where('branchOffice_id', $this->branchOffice_id)
                        ->where('almacen_id', $detail['almacen_id'])
                        ->where('seccion_id', $detail['seccion_id'])
                        ->value('stock');

                    if ($stock === null || $stock < $detail['quantity']) {
                        $validator->errors()->add("details.$index.quantity", "Stock insuficiente para el producto en la sucursal, almacén y sección seleccionados. Disponible: " . ($stock ?? 0));
                    }
                }
            }
        });
    }

}
