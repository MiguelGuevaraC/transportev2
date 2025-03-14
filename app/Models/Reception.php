<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reception extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="Reception",
     *     title="reception",
     *     description="Reception Model",
     *     required={"id","codeReception", "comment", "paymentAmount", "typeService", "typeDelivery", "user_id", "origin_id", "sender_id", "destination_id", "recipient_id", "pickupResponsible_id", "payResponsible_id", "seller_id","pointSender_id","pointDestination_id", "state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID Reception"
     *     ),
     *     @OA\Property(
     *         property="codeReception",
     *         type="string",
     *         description="Code of reception"
     *     ),
     *     @OA\Property(
     *         property="netWeight",
     *         type="number",
     *         format="decimal",
     *         description="Net weight of the reception"
     *     ),
     *     @OA\Property(
     *         property="paymentAmount",
     *         type="number",
     *         format="decimal",
     *         description="Amount paid for the reception"
     *     ),
     *     @OA\Property(
     *         property="debtAmount",
     *         type="number",
     *         format="decimal",
     *         description="Amount of debt for the reception"
     *     ),
     *  @OA\Property(
     *         property="conditionPay",
     *         type="string",
     *         description="Condition Pay for the reception"
     *     ),
     *     @OA\Property(
     *         property="typeService",
     *         type="string",
     *         description="Type of service for the reception"
     *     ),
     *     @OA\Property(
     *         property="type_responsiblePay",
     *         type="string",
     *         description="Type of Responsible Pay for the reception"
     *     ),
     *     @OA\Property(
     *         property="numberDays",
     *         type="integer",
     *         description="Number of days for the reception"
     *     ),
     *     @OA\Property(
     *         property="creditAmount",
     *         type="decimal",
     *         description="Amount for Credit"
     *     ),
     *     @OA\Property(
     *         property="typeDelivery",
     *         type="string",
     *         description="Type of delivery for the reception"
     *     ),
     *        @OA\Property(
     *         property="nro_sale",
     *         type="string",
     *         description="Tipo de documento",
     *         nullable=true
     *     ),
     * @OA\Property(
     *         property="receptionDate",
     *         type="date",
     *         description="receptionDate for the reception"
     *     ),
     * @OA\Property(
     *         property="transferLimitDate",
     *         type="date",
     *         description="transferLimitDate for the reception"
     *     ),
     * @OA\Property(
     *         property="transferStartDate",
     *         type="date",
     *         description="transferStartDate for the reception"
     *     ),
     * @OA\Property(
     *         property="estimatedDeliveryDate",
     *         type="date",
     *         description="estimatedDeliveryDate for the reception"
     *     ),
     *
     * @OA\Property(
     *         property="actualDeliveryDate",
     *         type="date",
     *         description="actualDeliveryDate for the reception"
     *     ),
     *
     *
     *     @OA\Property(
     *         property="user_id",
     *         type="integer",
     *         description="ID of the user associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="origin_id",
     *         type="integer",
     *         description="ID of the origin associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="sender_id",
     *         type="integer",
     *         description="ID of the sender associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="destination_id",
     *         type="integer",
     *         description="ID of the destination associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="recipient_id",
     *         type="integer",
     *         description="ID of the recipient associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="pickupResponsible_id",
     *         type="integer",
     *         description="ID of the pickup responsible associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="payResponsible_id",
     *         type="integer",
     *         description="ID of the payment responsible associated with the reception"
     *     ),
     *     @OA\Property(
     *         property="seller_id",
     *         type="integer",
     *         description="ID of the seller associated with the reception"
     *     ),
     *         @OA\Property(
     *         property="pointSender_id",
     *         type="integer",
     *         description="ID of the adress sender"
     *     ),
     *         @OA\Property(
     *         property="pointDestination_id",
     *         type="integer",
     *         description="ID of the point Destination"
     *     ),
     *     @OA\Property(
     *         property="comment",
     *         type="string",
     *         description="Comment of the reception"
     *     ),
     *        @OA\Property(
     *         property="tokenResponsible",
     *         type="string",
     *         description="Token Responsible"
     *     ),
     *        @OA\Property(
     *         property="address",
     *         type="string",
     *         description="Token Responsible"
     *     ),
     *      @OA\Property(
     *         property="state",
     *         type="string",
     *         description="State of the reception"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date of the reception"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Last update date of the reception"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         nullable=true,
     *         description="Deletion date of the reception (if applicable)"
     *     ),
     *        @OA\Property(
     *         property="user",
     *          ref="#/components/schemas/User",
     *         description="ID user performed the  reception"
     *     ),
     *          @OA\Property(
     *         property="origin",
     *          ref="#/components/schemas/Place",
     *         description="Place Origin"
     *     ),
     *          @OA\Property(
     *         property="sender",
     *          ref="#/components/schemas/Person",
     *         description="Person who send"
     *     ),
     *          @OA\Property(
     *         property="destination",
     *          ref="#/components/schemas/Place",
     *         description="Place Destination"
     *     ),
     *          @OA\Property(
     *         property="recipient",
     *          ref="#/components/schemas/Person",
     *         description="Person who receiving"
     *     ),
     *          @OA\Property(
     *         property="pickupResponsible",
     *          ref="#/components/schemas/ContactInfo",
     *         description="ID user performed the  reception"
     *     ),
     *          @OA\Property(
     *         property="payResponsible",
     *          ref="#/components/schemas/Person",
     *         description="Person who pay"
     *     ),
     *         @OA\Property(
     *         property="seller",
     *          ref="#/components/schemas/Worker",
     *         description="Seller Responsible"
     *     ),
     *              @OA\Property(
     *         property="pointSender",
     *          ref="#/components/schemas/Address",
     *         description="Seller Responsible"
     *     ),
     *              @OA\Property(
     *         property="pointDestination",
     *          ref="#/components/schemas/Address",
     *         description="Seller Responsible"
     *     ),
     *              @OA\Property(
     *         property="branch_office",
     *          ref="#/components/schemas/BranchOffice",
     *         description="BranchOffice"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'codeReception',
        'netWeight',
        'paymentAmount',
        'debtAmount',
        'conditionPay',
        'typeService',
        'type_responsiblePay',

        'numberDays',
        'creditAmount',
        'bultosTicket',
        'nro_sale',
        'typeDelivery',
        'receptionDate',
        'transferLimitDate',
        'transferStartDate',
        'estimatedDeliveryDate',
        'actualDeliveryDate',

        'user_id',
        'origin_id',
        'sender_id',
        'destination_id',
        'recipient_id',
        'pickupResponsible_id',
        'payResponsible_id',
        'seller_id',

        'pointSender_id',
        'pointDestination_id',
        'branchOffice_id',
        'office_id',

        'comment',
        'tokenResponsible',

        'address',
        'status',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function origin()
    {
        return $this->belongsTo(Place::class, 'origin_id');
    }

    public function sender()
    {
        return $this->belongsTo(Person::class, 'sender_id');
    }

    public function destination()
    {
        return $this->belongsTo(Place::class, 'destination_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Person::class, 'recipient_id');
    }

    public function pickupResponsible()
    {
        return $this->belongsTo(ContactInfo::class, 'pickupResponsible_id');
    }

    public function payResponsible()
    {
        return $this->belongsTo(Person::class, 'payResponsible_id');
    }

    public function seller()
    {
        return $this->belongsTo(Worker::class, 'seller_id');
    }

    public function pointSender()
    {
        return $this->belongsTo(Address::class, 'pointSender_id');
    }

    public function pointDestination()
    {
        return $this->belongsTo(Address::class, 'pointDestination_id');
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }

    public function office()
    {
        return $this->belongsTo(BranchOffice::class, 'office_id');
    }

    public function details()
    {
        return $this->hasMany(DetailReception::class);
    }
    public function cargos()
    {
        return $this->hasMany(Cargos::class);
    }
    public function storeWeight()
    {
        $totalWeight     = $this->details()->sum('weight');
        $this->netWeight = $totalWeight;
        $this->save();
    }

    public function carrierGuides()
    {
        return $this->hasMany(CarrierGuide::class);
    }
    public function firstCarrierGuide()
    {
        return $this->hasOne(CarrierGuide::class)
            ->where('status_facturado', '!=', 'Anulada')
            ->latestOfMany()
            ->with(['districtStart.province.department',
                'districtEnd.province.department',
                'motive', 'sender', 'recipient', 'payResponsible',
                'programming',
                'programming.tract',
                'programming.platform',
                'programming.origin',
                'programming.destination',
            ]);
    }
    public function moviment()
    {
        return $this->belongsTo(Moviment::class)
            ->where(function ($query) {
                // Si tiene Credit Note y el total es diferente, priorizarlo
                $query->whereHas('creditNote', function ($subquery) {
                    $subquery->whereColumn('credit_notes.total', '!=', 'moviments.total');
                })
                // Si no tiene Credit Note, aplicar los filtros de status
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotIn('status', ['Anulada por Nota', 'Anulada'])
                            ->where('status_facturado', '!=', 'Anulado');
                    });
            })
            ->latest('id');
    }

    // public function moviments()
    // {
    //     return $this->hasMany(Moviment::class, 'reception_id');
    // }

}
