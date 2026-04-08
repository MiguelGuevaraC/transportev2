<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraPartialReceiptGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_office_id',
        'proveedor_id',
        'invoice_compra_moviment_id',
        'observation',
    ];

    const filters = [
        'branch_office_id' => '=',
        'proveedor_id'     => '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_office_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Person::class, 'proveedor_id');
    }

    public function invoiceCompraMoviment()
    {
        return $this->belongsTo(CompraMoviment::class, 'invoice_compra_moviment_id');
    }

    public function partialMoviments()
    {
        return $this->hasMany(CompraMoviment::class, 'partial_receipt_group_id');
    }
}
