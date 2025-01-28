<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarrierByProgramming extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'id',
        'carrier_guide_id',

        'programming_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function programming()
    {
        return $this->belongsTo(Programming::class, 'programming_id');
    }
    public function carrier()
    {
        return $this->belongsTo(CarrierGuide::class, 'carrierGuide_id');
    }
}
