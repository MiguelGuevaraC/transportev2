<?php
namespace App\Http\Requests\ConceptTransactionRequest;

use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="TransactionConceptRequest",
 *     title="TransactionConceptRequest",
 *     description="Request model for Bank information with filters and sorting",
 *     required={"id", "name","type"},
 *     @OA\Property(property="name", type="string", nullable=true, description="PAGO DE MES"),
 *     @OA\Property(property="type", type="string", nullable=true, description="INGRESO"),
 * )
 */


class StoreConceptTransactionRequest extends StoreRequest
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
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('transaction_concepts', 'name')->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(['INGRESO', 'EGRESO'])], // Mejora la validación con Rule::in
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'El nombre del concepto es obligatorio.',
            'name.unique'   => 'El nombre del concepto ya está en uso.',
            'type.required' => 'El tipo del concepto es obligatorio.',
            'type.in'       => 'El tipo debe ser INGRESO o EGRESO.', // Mensaje de error para 'type'
        ];
    }
    
    
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */

}
