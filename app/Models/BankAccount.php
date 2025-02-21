<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'bank_id',
        'account_number',
        'account_type',
        'currency',
        'balance',
        'holder_name',
        'holder_id',
        'status',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'bank_id'=> '=',
        'account_number'=> 'like',
        'account_type'=> 'like',
        'currency'=> 'like',
        'balance'=> 'like',
        'holder_name'=> 'like',
        'holder_id'=> '=',
        'status'=> 'like',
    ];
    const sorts = [
        'id'            => 'desc',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    public function holder()
    {
        return $this->belongsTo(Person::class, 'holder_id');
    }

}
