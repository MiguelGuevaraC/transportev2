<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelFunctional extends Model
{
    use SoftDeletes;

    /**
     * @OA\Schema(
     *     schema="Model_functional",
     *     title="model_functional",
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
     *         property="abbreviation",
     *         type="string",
     *         description="Abbreviation of the functional model"
     *     ),
     *     @OA\Property(
     *         property="reference",
     *         type="string",
     *         description="Description of the functional model"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="State of the functional model"
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
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Deletion date"
     *     )
     * )
     */

    protected $fillable = [
        'id',
        'name',
        'abbreviation',
        'reference',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
