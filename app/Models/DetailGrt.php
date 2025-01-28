<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailGrt extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="DetailGrt",
     *     title="detail_grt",
     *     description="Detail reception model",
     *     required={"id","description","weight","paymentAmount","debtAmount","comment","reception_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Detail reception ID"
     *     ),
     *     @OA\Property(
     *         property="carrierGuide_id",
     *         type="integer",
     *         description="Carrier Guide ID"
     *     ),
     *     @OA\Property(
     *         property="detailReception_id",
     *         type="integer",
     *         description="Detail Reception ID"
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
     *     ),
     *           @OA\Property(
     *         property="comission_Agent",
     *         ref="#/components/schemas/Comission_Agent",
     *         description="Comission_Agent asociada al trabajador"
     *     ),
     *
     *           @OA\Property(
     *         property="carrier_guide",
     *         ref="#/components/schemas/CarrierGuide",
     *         description="CarrierGuide asociada al trabajador"
     *     ),
     * )
     */

    protected $fillable = [
        'id',

        'carrierGuide_id',
        'detailReception_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function carrierGuide()
    {
        return $this->belongsTo(CarrierGuide::class, 'carrierGuide_id');
    }
    public function detailReception()
    {
        return $this->belongsTo(DetailReception::class, 'detailReception_id');
    }
}
