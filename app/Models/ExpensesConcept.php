<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpensesConcept extends Model
{
    /**
     * @OA\Schema(
     *     schema="ExpensesConcept",
     *     title="expenseConcept",
     *     description="ExpensesConcept model",
     *     required={"id","name","abbreviation","description","state","created_at","updated_at","deleted_at"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ExpensesConcept ID"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Name of the expenseConcept"
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="type of the expenseConcept"
     *     ),

     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="State of the expenseConcept"
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
        'name',
        'type',
        'typeConcept',
        'state',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    public function branchOffice()
    {
        return $this->hasMany(DriverExpense::class);
    }
}
