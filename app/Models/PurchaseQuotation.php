<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseQuotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_requirement_id',
        'proveedor_id',
        'is_winner',
        'status',
        'comment',
    ];

    protected $casts = [
        'is_winner' => 'boolean',
    ];

    const filters = [
        'product_requirement_id' => '=',
        'proveedor_id'           => '=',
        'is_winner'              => '=',
        'status'                 => 'like',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function productRequirement()
    {
        return $this->belongsTo(ProductRequirement::class, 'product_requirement_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Person::class, 'proveedor_id');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseQuotationLine::class, 'purchase_quotation_id');
    }
}
