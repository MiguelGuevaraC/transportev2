<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarrierGuide extends Model
{
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="CarrierGuide",
     *     title="carrier_guide",
     *     description="Carrier guide model",
     *     required={"id","status","document","subContract","transferStartDate","tract_id","platform_id","origin_id","destination_id","sender_id","recipient_id","payResponsible_id","driver_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Carrier guide ID"
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Status of the carrier guide"
     *     ),

     *     @OA\Property(
     *         property="numero",
     *         type="string",
     *         description="numero of the carrier guide"
     *     ),
     *     @OA\Property(
     *         property="subContract",
     *         type="string",
     *         description="Subcontract of the carrier guide"
     *     ),
     *          @OA\Property(
     *         property="motivo",
     *         type="string",
     *         description="Motivo of the carrier guide"
     *     ),
     *          @OA\Property(
     *         property="codemotivo",
     *         type="string",
     *         description="Subcontract of the carrier guide"
     *     ),
     *     @OA\Property(
     *         property="transferStartDate",
     *         type="string",
     *         format="date-time",
     *         description="Transfer start date"
     *     ), @OA\Property(
     *         property="transferDateEstimated",
     *         type="string",
     *         format="date-time",
     *         description="Transfer start date"
     *     ),

     *     @OA\Property(
     *         property="tract_id",
     *         type="integer",
     *         description="ID of the tract"
     *     ),
     *     @OA\Property(
     *         property="platform_id",
     *         type="integer",
     *         description="ID of the platform"
     *     ),
     *     @OA\Property(
     *         property="origin_id",
     *         type="integer",
     *         description="ID of the origin"
     *     ),
     *     @OA\Property(
     *         property="destination_id",
     *         type="integer",
     *         description="ID of the destination"
     *     ),
     *     @OA\Property(
     *         property="sender_id",
     *         type="integer",
     *         description="ID of the sender"
     *     ),
     *     @OA\Property(
     *         property="recipient_id",
     *         type="integer",
     *         description="ID of the recipient"
     *     ),
     *     @OA\Property(
     *         property="payResponsible_id",
     *         type="integer",
     *         description="ID of the pay responsible"
     *     ),
     *     @OA\Property(
     *         property="driver_id",
     *         type="integer",
     *         description="ID of the driver"
     *     ),
     *      @OA\Property(
     *         property="copilot_id",
     *         type="integer",
     *         description="ID of the copilot"
     *     ),
     *      @OA\Property(
     *         property="subcontract_id",
     *         type="integer",
     *         description="ID of Subcontract"
     *     ),@OA\Property(
     *         property="reception_id",
     *         type="integer",
     *         description="ID of Reception"
     *     ),
     * @OA\Property(
     *         property="created_at",
     *         type="integer",
     *         description="created_at"
     *     ),
     * @OA\Property(
     *         property="updated_at",
     *         type="integer",
     *         description="updated_at"
     *     ),
     * @OA\Property(
     *         property="deleted_at",
     *         type="integer",
     *         description="deleted_at"
     *     ),
     *           @OA\Property(
     *         property="origin",
     *         ref="#/components/schemas/Place",
     *         description="Place Origin"
     *     ),
     *           @OA\Property(
     *         property="destination",
     *         ref="#/components/schemas/Place",
     *         description="Place Destination"
     *     ),
     *           @OA\Property(
     *         property="tract",
     *         ref="#/components/schemas/Vehicle",
     *         description="Vehicle Tract"
     *     ),
     *           @OA\Property(
     *         property="platform",
     *         ref="#/components/schemas/Vehicle",
     *         description="Vehicle Platform"
     *     ),
     *
     *           @OA\Property(
     *         property="sender",
     *         ref="#/components/schemas/Person",
     *         description="sender person"
     *     ),
     *           @OA\Property(
     *         property="recipient",
     *         ref="#/components/schemas/Person",
     *         description="recipient person"
     *     ),
     *           @OA\Property(
     *         property="payResponsible",
     *         ref="#/components/schemas/Person",
     *         description="payResponsible person"
     *     ),
     *           @OA\Property(
     *         property="driver",
     *         ref="#/components/schemas/Worker",
     *         description="Worker driver"
     *     ),     @OA\Property(
     *         property="copilot",
     *         ref="#/components/schemas/Worker",
     *         description="Worker copilot"
     *     ),   @OA\Property(
     *         property="subcontract",
     *         ref="#/components/schemas/Subcontract",
     *         description="Subcontract"
     *     ),
     *              @OA\Property(
     *         property="branch_office",
     *          ref="#/components/schemas/BranchOffice",
     *         description="BranchOffice"
     *     ),
     *          @OA\Property(
     *         property="reception",
     *          ref="#/components/schemas/Reception",
     *         description="Reception"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'status',
        'document',
        'numero',
        'observation',
        'type',
        'modalidad',
        'number',
        'serie',
        'motivo',
        'status_facturado',
        'codemotivo',
        'placa',

        'transferStartDate',
        'transferDateEstimated',

        'tract_id',
        'platform_id',
        'origin_id',
        'destination_id',
        'sender_id',
        'recipient_id',
        'programming_id',

        'districtStart_id',
        'districtEnd_id',

        'reception_id',
        'payResponsible_id',
        'driver_id',
        'copilot_id',
        'subcontract_id',
        'costsubcontract',
        'datasubcontract',

        'branchOffice_id',

        'ubigeoStart',
        'ubigeoEnd',
        'addressStart',
        'addressEnd',
        'motive_id',

        'user_edited_id',
        'user_deleted_id',
        'user_created_id',
        'user_factured_id',
        
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function tract()
    {
        return $this->belongsTo(Vehicle::class, 'tract_id');
    }
    public function platform()
    {
        return $this->belongsTo(Vehicle::class, 'platform_id');
    }

    public function origin()
    {
        return $this->belongsTo(Place::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Place::class, 'destination_id');
    }

    public function sender()
    {
        return $this->belongsTo(Person::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Person::class, 'recipient_id');
    }

    public function payResponsible()
    {
        return $this->belongsTo(Person::class, 'payResponsible_id');
    }

    public function driver()
    {
        return $this->belongsTo(Worker::class, 'driver_id');
    }
    public function programming()
    {
        return $this->belongsTo(Programming::class, 'programming_id');
    }
    public function copilot()
    {
        return $this->belongsTo(Worker::class, 'copilot_id');
    }

    public function subcontract()
    {
        return $this->belongsTo(Subcontract::class, 'subcontract_id');
    }

    public function reception()
    {
        return $this->belongsTo(Reception::class, 'reception_id');
    }
    public function districtStart()
    {
        return $this->belongsTo(District::class, 'districtStart_id');
    }
    public function districtEnd()
    {
        return $this->belongsTo(District::class, 'districtEnd_id');
    }
    public function motive()
    {
        return $this->belongsTo(Motive::class, 'motive_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
    public function programmings()
    {
        return $this->belongsToMany(Programming::class, 'carrier_by_programmings', 'carrier_guide_id', 'programming_id');
    }
    
}
