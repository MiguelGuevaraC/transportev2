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

        'distribuidor_id',

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
        'code_doc'        => '=',
        'person_id'       => '=',
        'product_id'      => 'in',
        'branchOffice_id' => '=',
        'quantity'        => 'like',
        'movement_type'   => 'like',
        'num_anexo'       => '=',
        'comment'         => 'like',

        'description'     => 'like',
        'movement_date'   => 'date',
        'weight'          => 'like',
        'lote_doc'        => '=',
        'date_expiration' => 'date',

        'unit_price'      => 'like',
        'total_cost'      => 'like',
        'stock_balance'   => 'like',
        'distribuidor_id' => '='
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

    public function distribuidor()
    {
        return $this->belongsTo(Person::class, 'distribuidor_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }

    public function details()
    {
        return $this->hasMany(DocumentCargaDetail::class,'document_carga_id');
    }
}
