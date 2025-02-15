<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchOffice extends Model
{
    /**
     * @OA\Schema(
     *     schema="BranchOffice",
     *     title="branch_office",
     *     description="Modelo de Sucursal",
     *     required={"id","name", "location", "address","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nombre de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="location",
     *         type="string",
     *         description="Ubicación de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="address",
     *         type="string",
     *         description="Dirección de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización de la sucursal"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de eliminación de la sucursal"
     *     )
     * )
     */

    use SoftDeletes;
    protected $fillable = [
        'id',
        'name',
        'location',
        'address',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function boxes()
    {
        return $this->hasMany(Box::class);
    }
    public function people()
    {
        return $this->hasMany(Person::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'ProductStockByBranch')
            ->withPivot('stock')
            ->withTimestamps();
    }
}
