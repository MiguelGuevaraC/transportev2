<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
/**
 * @OA\Schema(
 *     schema="Route",
 *     type="object",
 *     required={"id", "placeStart", "placeEnd", "placeStart_id", "placeEnd_id", "state"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="placeStart",
 *         type="string",
 *         example="Chclayo"
 *     ),
 *     @OA\Property(
 *         property="placeEnd",
 *         type="string",
 *         example="Lima"
 *     ),
 *     @OA\Property(
 *         property="placeStart_id",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="placeEnd_id",
 *         type="integer",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-08-10T12:34:56Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-08-10T12:34:56Z"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         example="null"
 *     )
 * )
 */
    protected $fillable = [
        'id',
        'placeStart',
        'placeEnd',

        'placeStart_id',
        'placeEnd_id',
        'routeFather_id',

        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function routes()
    {
        return $this->hasMany(Route::class, 'routeFather_id');
    }
    public function placeStart()
    {
        return $this->belongsTo(Place::class, 'placeStart_id');
    }

    public function placeEnd()
    {
        return $this->belongsTo(Place::class, 'placeEnd_id');
    }
    public function routeFather()
    {
        return $this->belongsTo(Route::class, 'routeFather_id');
    }

}
