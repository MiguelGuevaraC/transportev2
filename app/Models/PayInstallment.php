<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="PayInstallment",
 *     title="Pay Installment",
 *     description="Modelo de Cuota de Pago",
 *     required={"id", "number", "total", "paymentDate", "status", "state"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID de la cuota de pago"
 *     ),
 *     @OA\Property(
 *         property="number",
 *         type="integer",
 *         description="Número de la cuota"
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="number",
 *         format="float",
 *         description="Monto total de la cuota"
 *     ),
 *     @OA\Property(
 *         property="paymentDate",
 *         type="string",
 *         format="date",
 *         description="Fecha de pago de la cuota"
 *     ),
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         description="Comentario adicional sobre la cuota"
 *     ),
 *     @OA\Property(
 *         property="yape",
 *         type="number",
 *         format="float",
 *         description="Monto pagado vía Yape"
 *     ),
 *     @OA\Property(
 *         property="deposit",
 *         type="number",
 *         format="float",
 *         description="Monto pagado vía depósito"
 *     ),
 *     @OA\Property(
 *         property="cash",
 *         type="number",
 *         format="float",
 *         description="Monto pagado en efectivo"
 *     ),
 *     @OA\Property(
 *         property="card",
 *         type="number",
 *         format="float",
 *         description="Monto pagado con tarjeta"
 *     ),
 *     @OA\Property(
 *         property="plin",
 *         type="number",
 *         format="float",
 *         description="Monto pagado vía Plin"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Estado del pago (e.g., 'pagado', 'pendiente')"
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="boolean",
 *         description="Estado de la cuota de pago (activo o inactivo)"
 *     ),
 *     @OA\Property(
 *         property="installment_id",
 *         type="integer",
 *         description="ID de la cuota asociada"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de creación de la cuota de pago"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de actualización de la cuota de pago"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de eliminación de la cuota de pago"
 *     )
 * )
 */

 class PayInstallment extends Model
 {
     use SoftDeletes;
 
     protected $fillable = [
         'id',
         'number',  // Corregido de 'namber' a 'number'
         'total',
         'paymentDate',  // Corregido de 'datePay' a 'paymentDate'
         'comment',
         'nroOperacion',
         'is_detraction',
         'yape',
         'concept',
         'deposit',
         'cash',
         'card',
         'plin',
         'status',
         'type',
         'state',
         'bank_account_id',
         'installment_id',
         'moviment_id',
         'created_at',
         'updated_at',
         'bank_id'
         
     ];
     public function bank()
     {
         return $this->belongsTo(Bank::class, 'bank_id');
     }
     public function installment()
     {
         return $this->belongsTo(Installment::class, 'installment_id');
     }
     public function movements()
     {
         return $this->hasMany(Moviment::class, 'pay_installment_id');
     }
     public function movement()
     {
       return $this->hasOne(Moviment::class, 'pay_installment_id')->latest('id');
     }

     public function latest_bank_movement()
    {
        return $this->hasOne(BankMovement::class, 'pay_installment_id')->latestOfMany();
    }
    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
 }
 

