<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleMoviment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'description',
        'placa',
        'guia',
        'os',
        'cantidad',
        'tract_id',
        'reception_id',
        'carrier_guide_id',
        'precioCompra',
        'precioVenta',
        'moviment_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    public function carrier_guide()
    {
        return $this->belongsTo(CarrierGuide::class, 'carrier_guide_id');
    }
    public function moviment()
    {
        return $this->belongsTo(Moviment::class, 'moviment_id');
    }
    public function tract()
    {
        return $this->belongsTo(Vehicle::class, 'tract_id');
    }
}
