<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcontract extends Model
{
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="Subcontract",
     *     title="Subcontract",
     *     description="Subcontract schema for OpenAPI documentation",
     *     required={"id", "name"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Subcontract ID"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Name"
     *     ),
     *     @OA\Property(
     *         property="typeofDocument",
     *         type="string",
     *         description="Type of document"
     *     ),
     *     @OA\Property(
     *         property="documentNumber",
     *         type="string",
     *         description="Document number"
     *     ),
     *     @OA\Property(
     *         property="address",
     *         type="string",
     *         description="Address"
     *     ),
     *     @OA\Property(
     *         property="comercialName",
     *         type="string",
     *         description="Commercial name"
     *     ),
     *     @OA\Property(
     *         property="representativePersonDni",
     *         type="string",
     *         description="Representative person's DNI"
     *     ),
     *     @OA\Property(
     *         property="representativePersonName",
     *         type="string",
     *         description="Representative person's name"
     *     ),
     *     @OA\Property(
     *         property="telephone",
     *         type="string",
     *         description="Telephone"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Update date"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Deletion date"
     *     )
     * )
     */
    protected $fillable = [
        'id',

        'typeofDocument',
        'documentNumber',
        'address',
        'comercialName',
        'representativePersonDni',
        'representativePersonName',
        'telephone',

        'created_at',
        'updated_at',
        'deleted_at',

    ];

    public function guides()
    {
        return $this->hasMany(CarrierGuide::class, 'subcontract_id');
    }
}
