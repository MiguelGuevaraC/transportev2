<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailReception extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="DetailReception",
     *     title="detail_reception",
     *     description="Detail reception model",
     *     required={"id","description","weight","paymentAmount","debtAmount","comment","reception_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Detail reception ID"
     *     ),
     *     @OA\Property(
     *         property="numero",
     *         type="string",
     *         description="Numero"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Description"
     *     ),
     *     @OA\Property(
     *         property="weight",
     *         type="number",
     *         format="float",
     *         description="Weight"
     *     ),
     *     @OA\Property(
     *         property="paymentAmount",
     *         type="number",
     *         format="float",
     *         description="Payment amount"
     *     ),
     *     @OA\Property(
     *         property="debtAmount",
     *         type="number",
     *         format="float",
     *         description="Debt amount"
     *     ),
     *     @OA\Property(
     *         property="comissionAmount",
     *         type="number",
     *         format="float",
     *         description="Commission amount"
     *     ),
     *     @OA\Property(
     *         property="costLoad",
     *         type="number",
     *         format="float",
     *         description="Load cost"
     *     ),
     *           @OA\Property(
     *         property="unit",
     *         type="number",
     *         format="string",
     *         description="Load cost"
     *     ),
     *           @OA\Property(
     *         property="cant",
     *         type="number",
     *         format="float",
     *         description="Cant"
     *     ),
     *     @OA\Property(
     *         property="costDownload",
     *         type="number",
     *         format="float",
     *         description="Download cost"
     *     ),
     *     @OA\Property(
     *         property="comment",
     *         type="string",
     *         description="Comment"
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Status"
     *     ),
     *     @OA\Property(
     *         property="comissionAgent_id",
     *         type="integer",
     *         description="Commission agent ID"
     *     ),
     *     @OA\Property(
     *         property="reception_id",
     *         type="integer",
     *         description="Reception ID"
     *     ),
     *     @OA\Property(
     *         property="product_id",
     *         type="integer",
     *         description="Product ID"
     *     ),
     *     @OA\Property(
     *         property="tarifa_id",
     *         type="integer",
     *         description="Tarifa ID"
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
     *         property="reception",
     *         ref="#/components/schemas/Reception",
     *         description="Reception"
     *     ),
     *
     *           @OA\Property(
     *         property="carrier_guide",
     *         ref="#/components/schemas/CarrierGuide",
     *         description="CarrierGuide"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'numero',
        'description',
        'weight',
        'cant',
        'unit',

        'paymentAmount',
        'debtAmount',
        'comissionAmount',
        'costLoad',
        'costDownload',
        'comment',
        'status',
        'comissionAgent_id',
        'reception_id',
        'programming_id',
        'product_id',
        'tarifa_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function comissionAgent()
    {
        return $this->belongsTo(CommisionAgent::class, 'comissionAgent_id');
    }
    public function reception()
    {
        return $this->belongsTo(Reception::class, 'reception_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function tarifa()
    {
        return $this->belongsTo(Tarifario::class, 'tarifa_id');
    }
}
