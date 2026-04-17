<?php
namespace App\Http\Requests\CargaDocumentRequest;

use App\Http\Requests\StoreRequest;
use App\Services\CargaDocumentService;
use Illuminate\Validation\Validator;

/**
 * @OA\Schema(
 *     schema="CargaDocumentRequest",
 *     title="CargaDocumentRequest",
 *     description="Request model for loading a document with product details",
 *     required={"movement_date", "person_id", "distribuidor_id", "movement_type", "details"},
 *     @OA\Property(property="movement_date", type="string", format="date", description="Date of the movement"),
 *     @OA\Property(property="branchOffice_id", type="integer", nullable=true, description="Opcional; se ignora y se asigna desde la sucursal del usuario (worker.branchOffice_id)"),
 *     @OA\Property(property="person_id", type="integer", description="ID of the person associated with the movement"),
 *     @OA\Property(property="distribuidor_id", type="integer", description="ID of the distributor associated with the movement"),
 *     @OA\Property(property="movement_type", type="string", description="Type of movement (ENTRADA or SALIDA)"),
 *     @OA\Property(property="carrier_guide_number", type="string", nullable=true, description="Número de guía externa (no es ID de guía registrada en el sistema)"),
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

    protected function prepareForValidation()
    {
        if ($this->has('carrier_guide_id') && ! $this->has('carrier_guide_number')) {
            $this->merge(['carrier_guide_number' => $this->input('carrier_guide_id')]);
        }

        $branchId = auth()->user()?->worker?->branchOffice_id;
        $this->merge(['branchOffice_id' => $branchId]);
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
            'billing_month'             => 'nullable|string|regex:/^\d{4}-\d{2}$/',
            'carrier_guide_number'      => 'nullable|string|max:191',
            'guide_pdf'                 => 'nullable|file|mimes:pdf|max:20480',

            'details'                   => 'required|array|min:1',
            'details.*.id'              => 'nullable|integer',
            'details.*.quantity'        => 'required|numeric|min:1',
            'details.*.product_id'      => 'required|exists:products,id,deleted_at,NULL',
            'details.*.almacen_id'      => 'required|exists:almacens,id,deleted_at,NULL',
            'details.*.seccion_id'      => 'required|exists:seccions,id,deleted_at,NULL',
            'details.*.comment'         => 'nullable|string|max:500',
            'details.*.num_anexo'       => 'nullable|string|max:500',
            'details.*.date_expiration' => 'nullable|date',
            'details.*.num_lot'         => 'nullable|string|max:120',
            'details.*.position_code'   => 'nullable|string|max:64',
        ];
    }

    public function messages()
    {
        return [
            'movement_date.required'             => 'La fecha del movimiento es obligatoria.',
            'movement_date.date'                 => 'La fecha del movimiento debe ser una fecha válida.',

            'branchOffice_id.required'           => 'El usuario no tiene sucursal asignada; no puede registrar el documento.',
            'branchOffice_id.exists'             => 'La sucursal del usuario no es válida o ha sido eliminada.',

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
            $service = app(CargaDocumentService::class);

            if ($this->movement_type === 'ENTRADA' && is_array($this->details)) {
                $payload = [
                    'movement_type'   => $this->movement_type,
                    'branchOffice_id' => $this->branchOffice_id,
                    'details'         => $this->details,
                ];
                foreach ($service->entradaPositionConflictErrors($payload, null) as $index => $message) {
                    $validator->errors()->add("details.$index.position_code", $message);
                }
            }

            if ($this->movement_type === 'SALIDA') {
                foreach ($this->details as $index => $detail) {
                    $row   = $service->findStockRowForDetail($detail, (int) $this->branchOffice_id);
                    $stock = $row ? (float) $row->stock : null;

                    if ($stock === null || $stock < $detail['quantity']) {
                        $validator->errors()->add("details.$index.quantity", 'Stock insuficiente para el producto en la sucursal, almacén, sección, lote y posición seleccionados. Disponible: ' . ($stock ?? 0));
                    }
                }
            }
        });
    }

}
