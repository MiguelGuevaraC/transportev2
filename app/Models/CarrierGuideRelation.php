<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarrierGuideRelation extends Model
{
    protected $fillable = [
        'parent_carrier_guide_id',
        'child_carrier_guide_id',
        'relation_type',
    ];

    public function parentGuide()
    {
        return $this->belongsTo(CarrierGuide::class, 'parent_carrier_guide_id');
    }

    public function childGuide()
    {
        return $this->belongsTo(CarrierGuide::class, 'child_carrier_guide_id');
    }
}
