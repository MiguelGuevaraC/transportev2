<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repuesto extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'price_compra',
        'stock',
        'status',
        'category_id',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'=> 'like',
        'code'=> 'like',
        'price_compra'=> '=',
        'stock'=> '=',
        'status'=> 'like',
        'category_id'=> '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
