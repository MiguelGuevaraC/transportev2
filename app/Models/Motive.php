<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motive extends Model
{
      /**
     * @OA\Schema(
     *     schema="Motive",
     *     title="motive",
     *     description="Functional model",
     *     required={"id","name","abbreviation","description","state","created_at","updated_at","deleted_at"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Functional model ID"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Name of the functional model"
     *     ),
         *     @OA\Property(
     *         property="code",
     *         type="string",
     *         description="Code motive"
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
        'name',
'code',
        'created_at',
        'updated_at',

    ];
}
