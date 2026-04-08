<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseQuotationLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_quotation_id',
        'repuesto_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function purchaseQuotation()
    {
        return $this->belongsTo(PurchaseQuotation::class, 'purchase_quotation_id');
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
}
