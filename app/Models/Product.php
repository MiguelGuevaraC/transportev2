<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'description',
        'stock',
        'weight',
        'category',
        'codeproduct',
        'addressproduct',
        'unity_id',
        'person_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'codeproduct'    => 'like',
        'description'    => 'like',
        'person_id'    => '=',
        'addressproduct' => 'like',
        'category'       => 'like',
        'unity_id'       => '=',
        'weight'         => 'like',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id' => 'desc',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function unity()
    {
        return $this->belongsTo(Unity::class, 'unity_id');
    }

    public function branchOffices()
    {
        return $this->belongsToMany(BranchOffice::class, 'product_stock_by_branches', 'product_id', 'branchOffice_id')
            ->withPivot('stock', 'almacen_id', 'seccion_id') // Incluye las columnas 'almacen_id' y 'seccion_id'
            ->withTimestamps();
    }

 

}
