<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'   => 'like',
        'status' => 'like',
        'address' => 'like'
    ];

    const sorts = [
        'id' => 'desc',
    ];

    public function repuestos()
    {
        return $this->hasMany(Repuesto::class);
    }
}
