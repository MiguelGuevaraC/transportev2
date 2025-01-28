<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailMoviment extends Model
{

    protected $fillable = [
        'id',

        'product',
        'quantity',
        'weight',
        'price',

        'moviment_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function moviment()
    {
        return $this->belongsTo(Moviment::class, 'moviment_id');
    }
}
