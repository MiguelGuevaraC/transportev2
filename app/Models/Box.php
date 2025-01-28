<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Box extends Model
{
    /**
     * @OA\Schema(
     *     schema="Box",
     *     title="box",
     *     description="Modelo de box",
     *     required={"id","name","serie","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID de la box"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nombre de la box"
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="boolean",
     *         description="Estado de la box"
     *     ),
     *      @OA\Property(
     *         property="branchOffice_id",
     *         type="boolean",
     *         description="Branch Office"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación de la box"
     *     ),
     * )
     */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'serie',
        'status',
        'state',
        'branchOffice_id',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }

}
