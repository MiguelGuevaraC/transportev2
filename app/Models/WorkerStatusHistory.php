<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerStatusHistory extends Model
{
    protected $table = 'worker_status_histories';

    protected $fillable = [
        'worker_id',
        'action',
        'reason',
        'effective_date',
        'user_id',
    ];

    protected $casts = [
        'effective_date' => 'date',
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
