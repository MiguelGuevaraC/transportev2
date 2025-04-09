<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayPayableResource extends JsonResource
{

/**
 * @OA\Schema(
 *     schema="PayPayable",
 *     title="PayPayable",
 *     description="Model representing a PayPayable",
 *     required={"id", "number", "total", "paymentDate", "comment", "nroOperacion", "yape", "concept", "deposit", "cash", "card", "plin", "status", "type", "state", "bank_account_id", "bank_movement_id", "payable_id", "user_created_id", "created_at"},
 *     @OA\Property(property="id", type="integer", description="Payable ID"),
 *     @OA\Property(property="number", type="string", description="Payable number"),
 *     @OA\Property(property="total", type="number", format="float", description="Total amount"),
 *     @OA\Property(property="paymentDate", type="string", format="date", description="Payment date"),
 *     @OA\Property(property="comment", type="string", description="Comment"),
 *     @OA\Property(property="nroOperacion", type="string", description="Operation number"),
 *     @OA\Property(property="yape", type="string", description="Yape transaction"),
 *     @OA\Property(property="concept", type="string", description="Concept of the payment"),
 *     @OA\Property(property="deposit", type="number", format="float", description="Deposit amount"),
 *     @OA\Property(property="cash", type="number", format="float", description="Cash payment"),
 *     @OA\Property(property="card", type="number", format="float", description="Card payment"),
 *     @OA\Property(property="plin", type="number", format="float", description="Plin payment"),
 *     @OA\Property(property="status", type="string", description="Payment status"),
 *     @OA\Property(property="type", type="string", description="Payment type"),
 *     @OA\Property(property="state", type="string", description="Payment state"),
 *     @OA\Property(property="bank_account_id", type="integer", description="Bank account ID"),
 *     @OA\Property(property="bank_movement_id", type="integer", description="Bank movement ID"),
 *     @OA\Property(property="payable_id", type="integer", description="Payable ID"),
 *     @OA\Property(property="user_created_id", type="integer", description="User created ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update date")
 * )
 */

    public function toArray($request): array
    {
        return [
            'id'               => $this->id ?? null,
            'number'           => $this->number ?? null,
            'total'            => $this->total ?? null,
            'paymentDate'      => $this->paymentDate ?? null,
            'comment'          => $this->comment ?? null,
            'nroOperacion'     => $this->nroOperacion ?? null,
            'yape'             => $this->yape ?? null,
            'concept'          => $this->concept ?? null,
            'deposit'          => $this->deposit ?? null,
            'cash'             => $this->cash ?? null,
            'card'             => $this->card ?? null,
            'plin'             => $this->plin ?? null,
            'status'           => $this->status ?? null,
            'type'             => $this->type ?? null,
            'state'            => $this->state ?? null,
            'latest_bank_movement' => $this->latest_bank_movement ?? null,
            'bank_account_id'  => $this->bank_account_id ?? null,
            'bank_account_number'  => $this?->bank_account?->account_number ?? null,
            'bank_movement_id' => $this->bank_movement_id ?? null,
            'payable_id'       => $this->payable_id ?? null,
            'person_id' => $this->payable->person_id ?? null,
            'anticipos_proveedor_con_saldo' => $this->payable->person->anticipos_proveedor_con_saldo ?? [],
            'user_created_id'  => $this->user_created_id ?? null,
            'created_at'       => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at'       => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

}
