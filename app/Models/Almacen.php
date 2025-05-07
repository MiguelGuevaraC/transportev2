<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Almacen extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'    => 'like',
        'address' => 'like',
        'status'  => '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];
    public function seccions()
    {
        return $this->hasMany(Seccion::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_stock_by_branches', 'almacen_id', 'product_id')
            ->withPivot('stock', 'branchOffice_id', 'seccion_id') // Incluye otros campos de la tabla pivote si los necesitas
            ->wherePivot('stock', '>', 0)
            ->withTimestamps();
    }
}
