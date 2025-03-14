<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankMovement extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'id',
        'type_moviment',
        'date_moviment',
        'total_moviment',
        'currency',
        'comment',

        'pay_installment_id',
        'driver_expense_id',

        'user_created_id',
        'bank_id',
        'bank_account_id',
        'transaction_concept_id',
        'person_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'type_moviment'          => 'like',
        'date_moviment'          => 'between',
        'total_moviment'         => '=',
        'currency'               => 'like',
        'comment'                => 'like',
        'user_created_id'        => '=',
        'bank_id'                => '=',
        'bank_account_id'        => '=',
        'transaction_concept_id' => '=',
        'person_id'              => '=',
        'created_at'             => '=',
        'pay_installment_id'=> '=',
        'driver_expense_id'=> '=',
    ];
    const sorts = [
        'id' => 'desc',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($movement) {
            $movement->bank_account->updateBalance();
        });
        static::updated(function ($movement) {
            $movement->bank_account->updateBalance();
        });
        static::deleted(function ($movement) {
            $movement->bank_account->updateBalance();
        });
    }

    public function user_created()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
    public function transaction_concept()
    {
        return $this->belongsTo(TransactionConcept::class, 'transaction_concept_id');
    }
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function pay_installment()
    {
        return $this->belongsTo(PayInstallment::class, 'pay_installment_id');
    }
    public function driver_expense()
    {
        return $this->belongsTo(DriverExpense::class, 'driver_expense_id');
    }
}
