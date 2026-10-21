<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelVehicleDriverAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = ['vehicle_id', 'worker_id', 'start_date', 'end_date', 'active'];

    protected $casts = ['start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d', 'active' => 'boolean'];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function worker() { return $this->belongsTo(Worker::class); }
    public function purchases() { return $this->hasMany(FuelPurchase::class, 'assignment_id'); }
}
