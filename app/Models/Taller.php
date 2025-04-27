<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taller extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'status',
        'type',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name'   => 'like',
        'status' => 'like',
        'address' => 'like',
        'type' => 'like'
    ];

    const sorts = [
        'id' => 'desc',
    ];
}
