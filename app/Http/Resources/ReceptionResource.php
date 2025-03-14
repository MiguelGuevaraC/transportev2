<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ReceptionResource",
 *     title="ReceptionResource",
 *     description="Model representing a Reception",
 *     required={"id"},
 *     @OA\Property(property="id", type="integer", description="Reception ID"),
 *     @OA\Property(property="codeReception", type="string", nullable=true, description="Reception code"),
 *     @OA\Property(property="netWeight", type="number", format="float", nullable=true, description="Net weight of the reception"),
 *     @OA\Property(property="paymentAmount", type="number", format="float", nullable=true, description="Amount paid"),
 *     @OA\Property(property="debtAmount", type="number", format="float", nullable=true, description="Debt amount"),
 *     @OA\Property(property="conditionPay", type="string", nullable=true, description="Payment condition"),
 *     @OA\Property(property="typeService", type="string", nullable=true, description="Service type"),
 *     @OA\Property(property="type_responsiblePay", type="string", nullable=true, description="Type of responsible pay"),
 *     @OA\Property(property="numberDays", type="integer", nullable=true, description="Number of days"),
 *     @OA\Property(property="creditAmount", type="number", format="float", nullable=true, description="Credit amount"),
 *     @OA\Property(property="bultosTicket", type="string", nullable=true, description="Bultos ticket"),
 *     @OA\Property(property="status", type="string", nullable=true, description="Status"),
 *     @OA\Property(property="state", type="string", nullable=true, description="State"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true, description="Creation date"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, description="Last update date"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion date (soft delete)"),
 *     @OA\Property(property="user", type="object", nullable=true, description="User associated"),
 *     @OA\Property(property="origin", type="object", nullable=true, description="Origin details"),
 *     @OA\Property(property="sender", type="object", nullable=true, description="Sender details"),
 *     @OA\Property(property="destination", type="object", nullable=true, description="Destination details"),
 *     @OA\Property(property="recipient", type="object", nullable=true, description="Recipient details"),
 *     @OA\Property(property="pickupResponsible", type="object", nullable=true, description="Pickup responsible"),
 *     @OA\Property(property="payResponsible", type="object", nullable=true, description="Pay responsible"),
 *     @OA\Property(property="seller", type="object", nullable=true, description="Seller details"),
 *     @OA\Property(property="pointSender", type="object", nullable=true, description="Point sender details"),
 *     @OA\Property(property="pointDestination", type="object", nullable=true, description="Point destination details"),
 *     @OA\Property(property="branchOffice", type="object", nullable=true, description="Branch office details"),
 *     @OA\Property(property="details", type="object", nullable=true, description="Details of the reception"),
 *     @OA\Property(property="firstCarrierGuide", type="object", nullable=true, description="First carrier guide details"),
 *     @OA\Property(property="moviment", type="object", nullable=true, description="Moviment details"),
 *     @OA\Property(property="cargos", type="object", nullable=true, description="Cargos details")
 * )
 */
class ReceptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id ?? null,
            'codeReception'         => $this->codeReception ?? null,
            'netWeight'             => $this->netWeight ?? null,
            'paymentAmount'         => $this->paymentAmount ?? null,
            'debtAmount'            => $this->debtAmount ?? null,
            'conditionPay'          => $this->conditionPay ?? null,
            'typeService'           => $this->typeService ?? null,
            'type_responsiblePay'   => $this->type_responsiblePay ?? null,
            'numberDays'            => $this->numberDays ?? null,
            'creditAmount'          => $this->creditAmount ?? null,
            'bultosTicket'          => $this->bultosTicket ?? null,
            'nro_sale'              => $this->nro_sale ?? null,
            'typeDelivery'          => $this->typeDelivery ?? null,
            'receptionDate'         => $this->receptionDate ?? null,
            'transferLimitDate'     => $this->transferLimitDate ?? null,
            'transferStartDate'     => $this->transferStartDate ?? null,
            'estimatedDeliveryDate' => $this->estimatedDeliveryDate ?? null,
            'actualDeliveryDate'    => $this->actualDeliveryDate ?? null,

            'user_id'               => $this->user_id ?? null,
            'user'                  => $this->user ?? null,
            'origin_id'             => $this->origin_id ?? null,
            'origin'                => $this->origin ?? null,
            'sender_id'             => $this->sender_id ?? null,
            'sender'                => $this->sender ?? null,
            'destination_id'        => $this->destination_id ?? null,
            'destination'           => $this->destination ?? null,
            'recipient_id'          => $this->recipient_id ?? null,
            'recipient'             => $this->recipient ?? null,
            'pickupResponsible_id'  => $this->pickupResponsible_id ?? null,
            'pickupResponsible'     => $this->pickupResponsible ?? null,
            'payResponsible_id'     => $this->payResponsible_id ?? null,
            'payResponsible'        => $this->payResponsible ?? null,
            'seller_id'             => $this->seller_id ?? null,
            'seller'                => $this->seller ?? null,
            'pointSender_id'        => $this->pointSender_id ?? null,
            'pointSender'           => $this->pointSender ?? null,
            'pointDestination_id'   => $this->pointDestination_id ?? null,
            'pointDestination'      => $this->pointDestination ?? null,
            'branchOffice_id'       => $this->branchOffice_id ?? null,
            'branchOffice'          => $this->branchOffice ?? null,
            'office_id'             => $this->office_id ?? null,

            'comment'               => $this->comment ?? null,
            'tokenResponsible'      => $this->tokenResponsible ?? null,
            'address'               => $this->address ?? null,
            'status'                => $this->status ?? null,
            'state'                 => $this->state ?? null,
            'created_at'            => $this->created_at ?? null,

            'details'               => $this->details ?? null,
            'firstCarrierGuide'     => $this->firstCarrierGuide? new CarrierGuideResource($this->firstCarrierGuide) : null,
            'moviment'              => $this->moviment ?? null,
            'cargos'                => $this->cargos ?? null,
        ];
    }
}
