<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerAbsence extends Model
{
    protected $fillable = [
        'worker_id',
        'absence_date',
        'absence_type',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'absence_date' => 'date',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
