<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'description',
        'stock',
        'weight',
        'category',
        'codeproduct',
        'addressproduct',
        'unity_id',
        'person_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'description' => 'like',
        'stock' => 'like',
        'weight' => 'like',
        'category' => 'like',
        'unity' => 'like',
        'category_id' => '=',
        'unity_id' => '=',
        'codeproduct'=> 'like',
        'addressproduct'=> 'like',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id'            => 'desc',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function unity()
    {
        return $this->belongsTo(Unity::class, 'unity_id');
    }
}
