<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TireOperation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'operation_type', // Tipo de operación
        'vehicle_id',     // Vehículo asignado
        'position',       // Posición del neumático (1 a 12)
        'vehicle_km',     // Kilometraje del vehículo
        'operation_date', // Fecha de la operación
        'comment',        // Comentario adicional
        'driver_id',      // Conductor asignado
        'user_id',        // Usuario responsable
        'tire_id',        // Neumático relacionado
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const filters = [
        'operation_type' => 'like',
        'vehicle_id' => '=',
        'position' => '=',
        'vehicle_km' => '=',
        'operation_date' => 'date',
        'driver_id' => '=',
        'user_id' => '=',
        'tire_id' => '=',
        'created_at' => 'date',
    ];

    const sorts = [
        'id' => 'desc',
        'operation_date' => 'desc',
        'vehicle_km' => 'desc',
    ];

    // Relaciones
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Worker::class, 'driver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tire()
    {
        return $this->belongsTo(Tire::class);
    }
}
