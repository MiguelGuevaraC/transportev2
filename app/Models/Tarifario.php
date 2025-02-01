<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarifario extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'tarifa',
        'description',
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
        'tarifa'      => '=',
        'unity_id'    => '=',
        'unity.name'  => 'like',
        'person_id'   => '=',
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
    public function unity()
    {
        return $this->belongsTo(Unity::class, 'unity_id');
    }
}
