<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerAttendanceEvent extends Model
{
    protected $fillable = [
        'worker_id',
        'attendance_date',
        'checked_in_at',
        'checked_out_at',
        'source',
        'biometric_device_id',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'checked_in_at'   => 'datetime',
        'checked_out_at'  => 'datetime',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
}
