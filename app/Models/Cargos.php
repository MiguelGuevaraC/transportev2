<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargos extends Model
{
     /**
     * @OA\Schema(
     *     schema="Cargos",
     *     title="Cargos",
     *     description="Modelo de Cargos",
     *     required={"id", "description", "state", "vehicle_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del reception"
     *     ),
     *     @OA\Property(
     *         property="pathFile",
     *         type="string",
     *         description="Ruta del archivo relacionado con el reception"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Descripción del reception"
     *     ),

     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Estado del reception (e.g., activo, vencido)"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado booleano del reception"
     *     ),
     *     @OA\Property(
     *         property="reception_id",
     *         type="integer",
     *         description="ID de la recepción asociado"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación del reception"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización del reception"
     *     ),
     * )
     */
    use SoftDeletes;
     protected $fillable = [
        'id',
        'pathFile',
        'description',
        'status',
        'state',
        'reception_id',
        'created_at',
        'updated_at',

    ];
    public function reception()
    {
        return $this->belongsTo(Reception::class,'reception_id');
    }
}
