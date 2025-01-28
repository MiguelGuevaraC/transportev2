<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="Place",
     *     title="place",
     *     description="Place Model",
     *     required={"id", "name", "state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID of the place"
     *     ),
     *     @OA\Property(
     *         property="ubigeo",
     *         type="string",
     *         description="ubigeo of the place"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Name of the place"
     *     ),     
     *     @OA\Property(
     *         property="district_id",
     *         type="string",
     *         description="District ubigeo"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="State of the place"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date of the place"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Last update date of the place"
     *     ),

     * )
     */

    protected $fillable = [
        'id',
        'ubigeo',
        'name',
        'address',
        'state',
        'district_id',
        'created_at',
        'updated_at',

    ];
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
