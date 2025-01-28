<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeCompany extends Model
{
    /**
     * @OA\Schema(
     *     schema="TypeCompany",
     *     title="TypeCompany",
     *     description="Type of Company",
     *     required={"id", "description"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Type Company ID"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Description of the Type Company"
     *     ),
     *       @OA\Property(
     *         property="state",
     *         type="string",
     *         example="1"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Creation date"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Update date"
     *     ),
     * )
     */
    protected $fillable = [
        'id',
        'description',
        'state',
        'created_at',
        'updated_at',

    ];
}
