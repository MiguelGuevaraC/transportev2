<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /**
     * @OA\Schema(
     *     schema="Document",
     *     title="Document",
     *     description="Modelo de Documento",
     *     required={"id", "description", "state", "vehicle_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del documento"
     *     ),
     *     @OA\Property(
     *         property="pathFile",
     *         type="string",
     *         description="Ruta del archivo relacionado con el documento"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Descripción del documento"
     *     ),
     *     @OA\Property(
     *         property="number",
     *         type="string",
     *         description="Número del documento"
     *     ),
     *     @OA\Property(
     *         property="dueDate",
     *         type="string",
     *         format="date",
     *         description="Fecha de vencimiento del documento"
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Estado del documento (e.g., activo, vencido)"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado booleano del documento"
     *     ),
     *     @OA\Property(
     *         property="vehicle_id",
     *         type="integer",
     *         description="ID del vehículo asociado al documento"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación del documento"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización del documento"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'pathFile',
        'description',
        'type',
        'number',
        'dueDate',
        'status',
        'state',
        'vehicle_id',
        'created_at',
        'updated_at',

    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class,'vehicle_id');
    }
}
