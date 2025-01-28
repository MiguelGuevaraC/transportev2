<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{

    /**
     * @OA\Schema(
     *     schema="Area",
     *     title="area",
     *     description="Modelo de Área",
     *     required={"id","name","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del área"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nombre del área"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado del área"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación del área"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización del área"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de eliminación del área"
     *     )
     * )
     */

    use SoftDeletes;
    protected $fillable = [
        'id',
        'name',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
