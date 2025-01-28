<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use SoftDeletes;

/**
 * @OA\Schema(
 *     schema="CreditNote",
 *     title="credit_note",
 *     description="Credit note Model",
 *     required={"id", "number", "reason", "moviment_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID of the contact info"
 *     ),
 *     @OA\Property(
 *         property="number",
 *         type="string",
 *         description="Number of the contact info"
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="number",
 *         format="decimal",
 *         description="Total",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="reason",
 *         type="string",
 *         description="Reason for the contact info"
 *     ),
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         description="Comment related to the contact info"
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="boolean",
 *         description="State of the contact info"
 *     ),
 *     @OA\Property(
 *         property="moviment_id",
 *         type="integer",
 *         description="ID of the related moviment"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation date of the contact info"
 *     ),

 *     @OA\Property(
 *         property="moviment",
 *         ref="#/components/schemas/MovimentRequest",
 *         description="ID de la venta"
 *     ),
 *     @OA\Property(
 *         property="branchOffice_id",
 *         type="integer",
 *         description="ID de la sucursal",
 *         nullable=true
 *     ),
 * )
 */
    protected $fillable = [
        'id',
        'number',
        'reason',
        'total',
        'totalReferido',
        'newDate',
        'newTotal',
        'totalAjuste',
        'fechaAjuste',

        'comment',
        'description',
        'productList',
        'status',
        'status_facturado',
        'getstatus_fact',
        'percentaje',
        'state',
        'moviment_id',
        'branchOffice_id',
        'created_at',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function moviment()
    {
        return $this->belongsTo(Moviment::class, 'moviment_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
}
