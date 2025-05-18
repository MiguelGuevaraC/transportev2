<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraMoviment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'number',
        'date_movement',
        'document_type',
        'branchOffice_id',
        'person_id',
        'proveedor_id',
        'compra_order_id',
        'payment_method',
        'comment',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [

        'number'          => 'like',
        'date_movement'   => 'between',
        'document_type'   => 'like',
        'branchOffice_id' => '=',
        'person_id'       => '=',
        'proveedor_id'    => '=',
        'compra_order_id' => '=',
        'payment_method'  => 'like',
        'comment'         => 'like',
        'status'          => 'like',
    ];

    const sorts = [
        'id' => 'desc',
    ];
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Person::class, 'proveedor_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
    public function compra_order()
    {
        return $this->belongsTo(CompraOrder::class, 'compra_order_id');
    }

    public function details()
    {
        return $this->hasMany(CompraMovimentDetail::class);
    }
}
