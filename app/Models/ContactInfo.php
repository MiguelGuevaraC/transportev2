<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactInfo extends Model
{
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="ContactInfo",
     *     title="contact_info",
     *     description="Contact Info Model",
     *     required={"id", "names", "fatherSurname", "motherSurname", "state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID of the contact info"
     *     ),
     *     @OA\Property(
     *         property="names",
     *         type="string",
     *         description="Names of the contact info"
     *     ),
     *     @OA\Property(
     *         property="fatherSurname",
     *         type="string",
     *         description="Father's surname of the contact info"
     *     ),
     *     @OA\Property(
     *         property="motherSurname",
     *         type="string",
     *         description="Mother's surname of the contact info"
     *     ),
     *     @OA\Property(
     *         property="address",
     *         type="string",
     *         nullable=true,
     *         description="Address of the contact info"
     *     ),
     *     @OA\Property(
     *         property="telephone",
     *         type="string",
     *         nullable=true,
     *         description="Telephone number of the contact info"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         nullable=true,
     *         description="Email address of the contact info"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="State of the contact info"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date of the contact info"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Last update date of the contact info"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         nullable=true,
     *         description="Deletion date of the contact info (if applicable)"
     *     ),
     *           @OA\Property(
     *         property="person",
     *          ref="#/components/schemas/Person",
     *         description="ID de la persona asociada al trabajador"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'typeofDocument',
        'documentNumber',
        'names',
        'fatherSurname',
        'motherSurname',
        'address',
        'telephone',
        'email',
        'person_id',

        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

}
