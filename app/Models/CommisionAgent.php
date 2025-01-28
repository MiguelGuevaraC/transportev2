<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommisionAgent extends Model
{
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="Comission_Agent",
     *     title="commision_agent",
     *     description="Model representing commission agents",
     *     required={"paymentComission", "state", "person_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID of the commission agent"
     *     ),
     *     @OA\Property(
     *         property="paymentComission",
     *         type="string",
     *         description="Payment commission of the agent"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="State of the commission agent"
     *     ),
     *         @OA\Property(
     *         property="person_id",
     *         type="integer",
     *         description="User person"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date of the commission agent"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Update date of the commission agent"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Deletion date of the commission agent"
     *     ),
     *        @OA\Property(
     *         property="person",
     *         ref="#/components/schemas/Person",
     *         description="Sucursal asociada al trabajador"
     *     )
     * )
     */

    protected $fillable = [
        'id',
        'paymentComission',
        'state',
        'person_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

}
