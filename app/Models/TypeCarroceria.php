<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCarroceria extends Model
{
  /**
     * @OA\Schema(
     *     schema="TypeCarroceria",
     *     title="TypeCarroceria",
     *     description="Type of Carroceria",
     *     required={"id", "description"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Type Carroceria ID"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Description of the Type Carroceria"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="string",
     *         example="1",
     *         description="State of the Type Carroceria"
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
     *     )
     * )
     */

    protected $fillable = [
        'id',
        'description',
        'state',
        'typecompany_id',
        'created_at',
        'updated_at',
 
    ];
    public function typeCompany()
    {
        return $this->belongsTo(TypeCompany::class,'typecompany_id');
    }
}
