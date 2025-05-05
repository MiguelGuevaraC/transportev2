<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'type',
        'price',
        'price_total',
        'quantity',
        'maintenance_id',
        'repuesto_id',
        'service_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'           => '=',
        'type'           => '=',
        'price'          => '=',
        'price_total'    => '=',
        'quantity'       => '=',
        'maintenance_id' => '=',
        'repuesto_id'    => '=',
        'service_id'     => '=',
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
    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
