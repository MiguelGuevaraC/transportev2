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

        'lote_doc',
        'code_doc',
        'date_expiration',
        'num_anexo',
        'branchOffice_id',

        'movement_type',
        'stock_balance_before',
        'stock_balance_after',
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
        'description'     => 'like',
        'movement_date'   => 'between',
        'quantity'        => 'like',
        'unit_price'      => 'like',
        'total_cost'      => 'like',
        'weight'          => 'like',
        'movement_type'   => 'like',
        'stock_balance'   => 'like',
        'comment'         => 'like',
        'product_id'      => '=',
        'person_id'       => '=',
        'lote_doc'        => '=',
        'code_doc'        => '=',
        'date_expiration' => '=',
        'num_anexo'       => '=',
        'branchOffice_id' => '=',
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
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
}
