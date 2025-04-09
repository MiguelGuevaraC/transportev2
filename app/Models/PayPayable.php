<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="PayPayable",
 *     required={"id", "number", "paymentDate", "total"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="number", type="string"),
 *     @OA\Property(property="total", type="number", format="float"),
 *     @OA\Property(property="paymentDate", type="string", format="date"),
 *     @OA\Property(property="comment", type="string"),
 *     @OA\Property(property="nroOperacion", type="string"),
 *     @OA\Property(property="yape", type="string"),
 *     @OA\Property(property="concept", type="string"),
 *     @OA\Property(property="deposit", type="number", format="float"),
 *     @OA\Property(property="cash", type="number", format="float"),
 *     @OA\Property(property="card", type="number", format="float"),
 *     @OA\Property(property="plin", type="number", format="float"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="state", type="string"),
 *     @OA\Property(property="bank_account_id", type="integer"),
 *     @OA\Property(property="bank_movement_id", type="integer"),
 *     @OA\Property(property="payable_id", type="integer"),
 *     @OA\Property(property="user_created_id", type="integer"),
 *     @OA\Property(property="bank_id", type="integer")
 * )
 */

class PayPayable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'number', // Corregido de 'namber' a 'number'
        'total',
        'paymentDate', // Corregido de 'datePay' a 'paymentDate'
        'comment',
        'nroOperacion',
        'yape',
        'concept',
        'deposit',
        'cash',
        'card',
        'plin',
        'status',
        'type',
        'state',
        'bank_account_id',
        'bank_movement_id',
        'payable_id',
        'user_created_id',
        'created_at',
        'updated_at',
        'bank_id',

    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    public function payable()
    {
        return $this->belongsTo(Payable::class, 'payable_id');
    }
    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
    public function user_created()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    
    public function latest_bank_movement_anticipo()
    {
        return $this->belongsTo(BankMovement::class, 'bank_movement_id');
    }
    

    public function latest_bank_movement()
    {
        if ($this->bank_movement_id === null) {
            return $this->latest_bank_movement_transaction();
        } else {
            return $this->latest_bank_movement_anticipo();
        }
    }

    public function latest_bank_movement_transaction()
    {
        return $this->hasOne(BankMovement::class, 'pay_installment_id')->latestOfMany();
    }


}
