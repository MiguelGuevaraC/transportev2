<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocAlmacen extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'concept_id',
        'type',
        'movement_date',
        'reference_id',
        'reference_type',
        'user_id',
        'note',
        'created_at'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    const filters = [
        'name' => 'like',
        'concept_id' => '=',
        'type' => 'like',
        'movement_date' => 'between',
        'reference_id' => '=',
        'reference_type' => '=',
        'user_id' => '=',
        'note' => 'like',
        'created_at' => 'date'
    ];

    const sorts = [
        'id' => 'desc'
    ];

    public function concept()
    {
        return $this->belongsTo(ConceptTireOperation::class, 'concept_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(DocAlmacenDetail::class, 'doc_almacen_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}