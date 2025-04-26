<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCargaDetail extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'quantity',
        'product_id',
        'almacen_id',
        'seccion_id',
        'document_carga_id',
        'branchOffice_id',
        'comment',
        'num_anexo',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'quantity'          => '=',
        'product_id'        => '=',
        'almacen_id'        => '=',
        'seccion_id'        => '=',
        'document_carga_id' => '=',
        'branchOffice_id'   => '=',
        'comment'           => 'like',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id' => 'desc',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }
    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }
    public function document_carga()
    {
        return $this->belongsTo(CargaDocument::class, 'document_carga_id');
    }
    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branchOffice_id');
    }
}
