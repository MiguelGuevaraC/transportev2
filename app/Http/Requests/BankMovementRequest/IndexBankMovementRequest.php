<?php
namespace App\Http\Requests\BankMovementRequest;

use App\Http\Requests\IndexRequest;

/**
 * @OA\Schema(
 *     schema="BankMovementFilters",
 *     type="object",
 *     title="Bank Movement Filters",
 *     description="Parámetros de filtro para obtener movimientos bancarios",
 *     @OA\Property(property="type_moviment", type="string", nullable=true, description="Tipo de movimiento"),

 *     @OA\Property(property="from", type="string", nullable=true, format="date", description="Fecha de inicio para el filtro de movimiento"),
 *     @OA\Property(property="to", type="string", nullable=true, format="date", description="Fecha de fin para el filtro de movimiento"),
 *     @OA\Property(property="total_moviment", type="string", nullable=true, description="Monto total del movimiento"),
 *     @OA\Property(property="currency", type="string", nullable=true, description="Moneda del movimiento"),
 *     @OA\Property(property="comment", type="string", nullable=true, description="Comentario del movimiento"),
 *     @OA\Property(property="user_created_id", type="string", nullable=true, description="ID del usuario que creó el movimiento"),
 *     @OA\Property(property="bank_id", type="string", nullable=true, description="ID del banco asociado al movimiento"),
 *     @OA\Property(property="bank_account_id", type="string", nullable=true, description="ID de la cuenta bancaria asociada"),
 *     @OA\Property(property="transaction_concept_id", type="string", nullable=true, description="ID del concepto de transacción"),
 *     @OA\Property(property="person_id", type="string", nullable=true, description="ID de la persona asociada al movimiento"),
 *     @OA\Property(property="created_at", type="string", nullable=true, format="date-time", description="Fecha de creación del movimiento")
 * )
 */
class IndexBankMovementRequest extends IndexRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type_moviment'          => 'nullable|string',
            'from'                   => 'nullable|date',
            'to'                     => 'nullable|date|after_or_equal:from',
            'total_moviment'         => 'nullable|string',
            'currency'               => 'nullable|string',
            'comment'                => 'nullable|string',
            'user_created_id'        => 'nullable|string',
            'bank_id'                => 'nullable|string',
            'bank_account_id'        => 'nullable|string',

            'pay_installment_id'     => 'nullable|string',
            'driver_expense_id'      => 'nullable|string',

            'transaction_concept_id' => 'nullable|string',
            'person_id'              => 'nullable|string',
            'created_at'             => 'nullable|string',
        ];
    }
}
