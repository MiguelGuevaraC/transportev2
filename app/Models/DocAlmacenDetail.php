<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocAlmacenDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'doc_almacen_id',
        'tire_id',
        'quantity',
        'unit_price',
        'total_value',
        'note',
        'created_at'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    const filters = [
        'doc_almacen_id' => '=',
        'tire_id' => '=',
        'quantity' => '=',
        'unit_price' => '=',
        'total_value' => '=',
        'note' => 'like',
    ];

    const sorts = [
        'id' => 'desc'
    ];

    public function docAlmacen()
    {
        return $this->belongsTo(DocAlmacen::class, 'doc_almacen_id');
    }
    public function tire()
    {
        return $this->belongsTo(Tire::class, 'tire_id');
    }
}