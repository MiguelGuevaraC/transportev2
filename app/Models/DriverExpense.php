<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverExpense extends Model
{

    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="DriverExpense",
     *     title="driver_expense",
     *     description="Driver Expense Schema",
     *     required={"id", "programming_id", "expensesConcept_id", "total", "amount", "quantity", "place", "km", "routeFact", "gallons", "expenseDate"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="DriverExpense ID"
     *     ),
     *     @OA\Property(
     *         property="programming_id",
     *         type="integer",
     *         description="ID of the programming"
     *     ),
     *     @OA\Property(
     *         property="expensesConcept_id",
     *         type="integer",
     *         description="ID of the expenses concept"
     *     ),
     *     @OA\Property(
     *         property="amount",
     *         type="number",
     *         format="float",
     *         description="Amount of the expense"
     *     ),
     *     @OA\Property(
     *         property="quantity",
     *         type="integer",
     *         description="Quantity of items"
     *     ),
     *     @OA\Property(
     *         property="place",
     *         type="string",
     *         description="Place where the expense occurred"
     *     ),
     *     @OA\Property(
     *         property="km",
     *         type="integer",
     *         description="Kilometers at the time of expense"
     *     ),
     *     @OA\Property(
     *         property="routeFact",
     *         type="string",
     *         description="Route factored"
     *     ),
     *     @OA\Property(
     *         property="gallons",
     *         type="number",
     *         format="float",
     *         description="Gallons used"
     *     ),
     *     @OA\Property(
     *         property="date_expense",
     *         type="string",
     *         format="date",
     *         description="Date of the expense"
     *     ),
     *     @OA\Property(
     *         property="comment",
     *         type="string",
     *         description="Additional comments about the expense"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date"
     *     ),
     * )
     */

    protected $fillable = [
        'id',
        'place',
        'date_expense',
        'operationNumber',
        'igv',
        'gravado',
        'exonerado',
        'selectTypePay',
        
        'total',
        'routeFact',
        'gallons',
        'amount',
        'quantity',
        'km',
        'isMovimentCaja',
        'comment',
        'programming_id',
        'bank_id',
        'worker_id',
        'expensesConcept_id',
        'proveedor_id',
        'created_at',

    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function moviment()
    {
        return $this->hasOne(Moviment::class, 'driverExpense_id');
    }

    public function programming()
    {
        return $this->belongsTo(Programming::class, 'programming_id');
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Person::class, 'proveedor_id');
    }
    public function expensesConcept()
    {
        return $this->belongsTo(ExpensesConcept::class, 'expensesConcept_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
}
