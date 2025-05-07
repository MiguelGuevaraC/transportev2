<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'type',
        'mode',
        'km',
        'status',
        'date_maintenance',
        'date_end',
        'vehicle_id',
        'taller_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'type'             => '=',
        'mode'             => '=',
        'km'               => '=',
        'date_maintenance' => 'date',
        'date_end'         => 'date',
        'vehicle_id'       => '=',
        'taller_id'        => '=',
        'status'           => 'like',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id' => 'desc',
    ];

    public function taller()
    {
        return $this->belongsTo(Taller::class, 'taller_id');
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function details()
    {
        return $this->hasMany(MaintenanceDetail::class);
    }
}
