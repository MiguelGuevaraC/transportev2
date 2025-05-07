<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seccion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'almacen_id',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'=> 'like',
        'almacen_id'=> '=',
        'status'=> '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function products()
{
    return $this->belongsToMany(Product::class, 'product_stock_by_branches', 'seccion_id', 'product_id')
        ->withPivot('stock', 'almacen_id', 'branchOffice_id')
        ->wherePivot('stock', '>', 0)
        ->withTimestamps();
}
}
