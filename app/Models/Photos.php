<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Photo",
 *     title="photo",
 *     description="Modelo de Foto",
 *     required={"id","name","vehicle_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID de la foto"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre de la foto"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción de la foto"
 *     ),
 *     @OA\Property(
 *         property="vehicle_id",
 *         type="integer",
 *         description="ID del vehículo asociado"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de creación de la foto"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de actualización de la foto"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de eliminación de la foto"
 *     )
 * )
 */
class Photos extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'vehicle_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the vehicle that owns the photo.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
