<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailWorker extends Model
{
    use SoftDeletes;
    /**
     * @OA\Schema(
     *     schema="DetailWorker",
     *     title="detail_worker",
     *     description="Detail Worker model",
     *     required={"id","description","worker_id","programming_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Detail Worker ID"
     *     ),
     *     @OA\Property(
     *         property="function",
     *         type="string",
     *         description="Function Worker"
     *     ),
     *     @OA\Property(
     *         property="worker_id",
     *         type="integer",
     *         description="Worker ID"
     *     ),
     *     @OA\Property(
     *         property="programming_id",
     *         type="integer",
     *         description="Programming ID"
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
     *     ),
     *           @OA\Property(
     *         property="worker",
     *         ref="#/components/schemas/Worker",
     *         description="Worker asociada al trabajador"
     *     ),
     *
     *           @OA\Property(
     *         property="programming",
     *         ref="#/components/schemas/Programming",
     *         description="Programming "
     *     ),
     * )
     */

    protected $fillable = [
        'id',

        'function',
        'programming_id',
        'worker_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function programming()
    {
        return $this->belongsTo(Programming::class, 'programming_id');
    }
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
}
