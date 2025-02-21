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
        'tarifa_camp',
        'limitweight_min',
        'limitweight_max',
        'destination_id',
        'origin_id',

        'person_id',
        'created_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'tarifa' => 'like',
        'description' => 'like',
        'origin_id' => '=',
        'destination_id' => '=',
        'person_id' => '=',
        'unity_id' => '=',
        'limitweight_min' => 'like',
        'limitweight_max' => 'like',
        'created_at' => 'date',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id' => 'desc',
    ];
    public function origin()
    {
        return $this->belongsTo(Place::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Place::class, 'destination_id');
    }
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function unity()
    {
        return $this->belongsTo(Unity::class, 'unity_id');
    }
}
