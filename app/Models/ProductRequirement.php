<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequirement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'check_list_id',
        'branch_office_id',
        'status',
        'observation',
    ];

    const filters = [
        'check_list_id'      => '=',
        'branch_office_id'   => '=',
        'status'             => 'like',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function checkList()
    {
        return $this->belongsTo(CheckList::class, 'check_list_id');
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_office_id');
    }

    public function lines()
    {
        return $this->hasMany(ProductRequirementLine::class, 'product_requirement_id');
    }

    public function purchaseQuotations()
    {
        return $this->hasMany(PurchaseQuotation::class, 'product_requirement_id');
    }
}
