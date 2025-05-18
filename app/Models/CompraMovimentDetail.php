<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraMovimentDetail extends Model
{
 use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'compra_moviment_id',
        'repuesto_id',
        'quantity',
        'unit_price',
        'subtotal',
        'comment',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [

        'compra_moviment_id' => '=',
        'repuesto_id'     => '=',
        'quantity'        => 'like',
        'unit_price'      => 'like',
        'subtotal'        => 'like',
        'comment'         => 'like',
    ];

    const sorts = [
        'id' => 'desc',
    ];
    public function compra_moviment()
    {
        return $this->belongsTo(CompraMoviment::class, 'compra_moviment_id');
    }
    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
}
