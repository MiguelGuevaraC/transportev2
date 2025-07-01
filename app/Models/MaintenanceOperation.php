<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceOperation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'type_moviment',
        'name',
        'quantity',
        'unity',
        'maintenance_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'type_moviment' => '=',
        'name' => '=',
        'quantity' => '=',
        'unity' => '=',
        'maintenance_id' => '=',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id' => 'desc',
    ];

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }
}
