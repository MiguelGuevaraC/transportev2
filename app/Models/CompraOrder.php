<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'number',
        'date_movement',
        'branchOffice_id',
        'person_id',
        'proveedor_id',
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
        'branchOffice_id' => '=',
        'person_id'       => '=',
        'proveedor_id'    => '=',
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
     public function details()
    {
        return $this->hasMany(CompraOrderDetail::class);
    }
}
