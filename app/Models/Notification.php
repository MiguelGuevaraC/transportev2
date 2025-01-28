<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'record_id',
        'title',
        'message',
        'dueDate',
        'type',
        'table',
        'priority',
        'vehicle_id',
        'document_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
