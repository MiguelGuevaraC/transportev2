<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CajaYadiraMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'movement_date',
        'movement_time',
        'type',
        'operation_number',
        'amount',
        'person_id',
        'concept',
        'observation',
        'voucher_path',
        'driver_expense_id',
        'user_id',
    ];

    protected $casts = [
        'movement_date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function driverExpense()
    {
        return $this->belongsTo(DriverExpense::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
