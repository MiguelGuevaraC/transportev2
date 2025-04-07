<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Payable",
 *     required={"id", "number", "date", "total"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="number", type="string"),
 *     @OA\Property(property="days", type="integer"),
 *     @OA\Property(property="date", type="string", format="date"),
 *     @OA\Property(property="total", type="number", format="float"),
 *     @OA\Property(property="totalDebt", type="number", format="float"),
 *     @OA\Property(property="driver_expense_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */

class Payable extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'number',
        'days',
        'date',
        'total',
        'totalDebt',
        'driver_expense_id',
        'user_created_id',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'number'            => 'like',
        'days'              => '=',
        'date'              => 'between',
        'total'             => '=',
        'totalDebt'         => '=',
        'driver_expense_id' => '=',
        'driver_expense.proveedor_id' => '=',
        'driver_expense.programming_id' => '=',
    ];

    const sorts = [
        'id' => 'desc',
    ];
    public function driver_expense()
    {
        return $this->belongsTo(DriverExpense::class, 'driver_expense_id');
    }

    public function payPayables()
    {
        return $this->hasMany(PayPayable::class);
    }
    public function user_created()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }
}
