<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceptionBySale extends Model
{
    protected $fillable = [
        'id',
        'reception_id',
        'status',
        'moviment_id',
        'created_at',
        'updated_at',
    ];
    public function moviment()
    {
        return $this->belongsTo(Moviment::class, 'moviment_id');
    }
    public function reception()
    {
        return $this->belongsTo(Reception::class, 'reception_id');
    }
}
