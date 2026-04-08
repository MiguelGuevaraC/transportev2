<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequirementLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_requirement_id',
        'check_list_detail_id',
        'repuesto_id',
        'quantity_requested',
        'observation',
    ];

    public function productRequirement()
    {
        return $this->belongsTo(ProductRequirement::class, 'product_requirement_id');
    }

    public function checkListDetail()
    {
        return $this->belongsTo(CheckListDetails::class, 'check_list_detail_id');
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
}
