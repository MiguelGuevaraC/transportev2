<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{

    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="Address",
     *     title="address",
     *     description="address Model",
     *     required={"id", "name", "reference", "state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID of the address"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Name of the address"
     *     ),
     *     @OA\Property(
     *         property="reference",
     *         type="string",
     *         description="Reference of the address"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="string",
     *         description="State of the address"
     *     ),
     *     @OA\Property(
     *         property="client_id",
     *         type="integer",
     *         description="ID of the adress client"
     *     ),
     *     @OA\Property(
     *         property="place_id",
     *         type="integer",
     *         description="ID of the place"
     *     ),
     *      @OA\Property(
     *         property="contact_info_id",
     *         type="integer",
     *         description="ID of the contact"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date of the address"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Last update date of the address"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         nullable=true,
     *         description="Deletion date of the address (if applicable)"
     *     ),
     *    @OA\Property(
     *         property="client",
     *          ref="#/components/schemas/Person",
     *         description="Person who send"
     *     ),
     *    @OA\Property(
     *         property="place",
     *          ref="#/components/schemas/Place",
     *         description="Place where send"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'name',
        'reference',
        'state',
        'client_id',
	    'place_id',
        'contact_info_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function client()
    {
        return $this->belongsTo(Person::class, 'client_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function contact_info()
    {
        return $this->belongsTo(ContactInfo::class, 'contact_info_id');
    }

    public function receptionsAsSender()
    {
        return $this->hasMany(Reception::class, 'pointSender_id');
    }

    // Recepciones que usan esta dirección como punto de destino
    public function receptionsAsDestination()
    {
        return $this->hasMany(Reception::class, 'pointDestination_id');
    }
}
