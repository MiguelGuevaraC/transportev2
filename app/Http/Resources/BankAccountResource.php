<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{

/**
 * @OA\Schema(
 *     schema="BankAccount",
 *     title="Bank Account",
 *     description="Model representing a Bank Account",
 *     required={"id", "bank_id", "account_number", "account_type", "currency", "balance", "holder_name", "status"},
 *
 *     @OA\Property(property="id", type="integer", description="Bank Account ID"),
 *     @OA\Property(property="bank_id", type="integer", description="ID of the bank"),
 *     @OA\Property(property="account_number", type="string", description="Unique account number"),
 *     @OA\Property(property="account_type", type="string", description="Type of account (e.g., savings, checking)"),
 *     @OA\Property(property="currency", type="string", description="Currency of the account (e.g., USD, EUR)"),
 *     @OA\Property(property="balance", type="number", format="float", description="Current balance in the account"),
 *     @OA\Property(property="holder_name", type="string", description="Name of the account holder"),
 *     @OA\Property(property="holder_id", type="integer", nullable=true, description="ID of the account holder (if applicable)"),
 *     @OA\Property(property="status", type="string", description="Account status (e.g., active, inactive)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the account was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the account was last updated")
 * )
 */

    public function toArray($request): array
    {
        return [
            'id'             => $this->id ?? null,
            'bank_id'        => $this->bank_id ?? null,
            'bank'           => $this->bank ? new BankResource($this->bank) : null,
            'account_number' => $this->account_number ?? null,
            'account_type'   => $this->account_type ?? null,
            'currency'       => $this->currency ?? null,
            'balance'        => $this->balance ?? null,
            'holder_name'    => $this->holder_name ?? null,
            'holder_id'      => $this->holder_id ?? null,
            'holder'         => $this->holder ? $this->holder : null,
            'status'         => $this->status ?? null,
        ];
    }

}
