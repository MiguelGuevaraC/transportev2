<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Bank",
 *     title="Bank",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Bank 1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-22 00:41:38")
 * )
 *
 * @OA\Schema(
 *     schema="BankRequest",
 *     title="BankRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Bank 1")
 * )
 */
class Bank extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'name' => 'like',
    ];

    /**
     * Campos de ordenación disponibles.
     */
    const sorts = [
        'id'            => 'desc',
    ];
}
