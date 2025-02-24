<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankMovementResource extends JsonResource
{

/**
 * @OA\Schema(
 *     schema="BankMovement",
 *     title="Bank Movement",
 *     description="Model representing a Bank Movement",
 *     required={"id", "type_moviment", "date_moviment", "total_moviment", "currency", "bank_id", "bank_account_id", "transaction_concept_id"},
 *
 *     @OA\Property(property="id", type="integer", description="Unique identifier for the bank movement"),
 *     @OA\Property(property="type_moviment", type="string", enum={"Ingreso", "Salida"}, description="Type of movement: Ingreso (income) or Salida (expense)"),
 *     @OA\Property(property="date_moviment", type="string", format="date", description="Date when the movement was registered"),
 *     @OA\Property(property="total_moviment", type="number", format="float", description="Total amount of the movement"),
 *     @OA\Property(property="currency", type="string", description="Currency of the transaction"),
 *     @OA\Property(property="user_created_id", type="integer", nullable=true, description="ID of the user who registered the movement"),
 *     @OA\Property(property="bank_id", type="integer", description="ID of the bank involved in the movement"),
 *     @OA\Property(property="bank", ref="#/components/schemas/Bank", nullable=true, description="Bank details"),
 *     @OA\Property(property="bank_account_id", type="integer", description="ID of the associated bank account"),
 *     @OA\Property(property="bank_account", ref="#/components/schemas/BankAccount", nullable=true, description="Bank account details"),
 *     @OA\Property(property="transaction_concept_id", type="integer", description="ID of the transaction concept"),
 *     @OA\Property(property="transaction_concept", ref="#/components/schemas/TransactionConcept", nullable=true, description="Transaction concept details"),
 *     @OA\Property(property="person_id", type="integer", nullable=true, description="ID of the person associated with the movement, if applicable"),
 *     @OA\Property(property="person", ref="#/components/schemas/Person", nullable=true, description="Person details"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the movement was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the movement was last updated")
 * )
 */
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id ?? null,
            'type_moviment'          => $this->type_moviment ?? null,
            'date_moviment'          => $this->date_moviment ?? null,
            'comment'                => $this->comment ?? null,
            'total_moviment'         => $this->total_moviment ?? null,
            'currency'               => $this->currency ?? null,
            'user_created_id'        => $this->user_created_id ?? null,
            'bank_id'                => $this->bank_id ?? null,
            'bank'                   => $this->bank ? new BankResource($this->bank) : null,
            'bank_account_id'        => $this->bank_account_id ?? null,
            'bank_account'           => $this->bank_account ? new BankAccountResource($this->bank_account) : null,
            'transaction_concept_id' => $this->transaction_concept_id ?? null,
            'transaction_concept'    => $this->transaction_concept ? new TransactionConceptResource($this->transaction_concept) : null,
            'person_id'              => $this->person_id ?? null,
            'person'                 => $this->person ? $this->person : null,
            'created_at'             => $this->created_at ?? null,
            'updated_at'             => $this->updated_at ?? null,
        ];
    }

}
