<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CargaDocument extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'movement_date',
        'quantity',
        'unit_price',
        'total_cost',
        'weight',
        'movement_type',
        'stock_balance',
        'comment',
        'product_id',
        'person_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'description'   => 'like',
        'movement_date' => 'between',
        'quantity'      => 'like',
        'unit_price'    => 'like',
        'total_cost'    => 'like',
        'weight'        => 'like',
        'movement_type' => 'like',
        'stock_balance' => 'like',
        'comment'       => 'like',
        'product_id'    => '=',
        'person_id'     => '=',
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
