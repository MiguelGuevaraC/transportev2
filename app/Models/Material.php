<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'state', 'created_at'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    const filters = [
        'name'=> 'like',
        'state'=> '=',
        'created_at'=> 'date'
    ];

    const sorts = [
        'id' => 'desc'
    ];
}