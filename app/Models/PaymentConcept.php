<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentConcept extends Model
{
    /**
     * @OA\Schema(
     *     schema="PaymentConcept",
     *     title="payment_concept",
     *     description="Modelo de Concepto de Pago",
     *     required={"id","name", "type","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID del concepto de pago"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nombre del concepto de pago"
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="Tipo del concepto de pago"
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="boolean",
     *         description="Estado del concepto de pago"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación del concepto de pago"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de actualización del concepto de pago"
     *     ),
     *     @OA\Property(
     *         property="deleted_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de eliminación del concepto de pago"
     *     )
     * )
     */

    use SoftDeletes;
    protected $fillable = [
        'id',
        'name',
        'type',
        'state',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
