<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelStation extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'ruc', 'address', 'phone', 'credit_days', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function purchases()
    {
        return $this->hasMany(FuelPurchase::class);
    }
}
