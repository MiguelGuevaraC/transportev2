<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStockByBranch extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'product_id',
        'stock',
        'branchOffice_id',
        'seccion_id',
        'almacen_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'product_id' => '=',
        'stock' => '=',
        'branchOffice_id' => '=',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id'            => 'desc',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }
    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

}
