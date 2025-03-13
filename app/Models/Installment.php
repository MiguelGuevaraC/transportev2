<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'number',
        'days',
        'date',
        'total',
        'totalDebt',
        'moviment_id',
        'created_at',
    ];
    /**
     * @OA\Schema(
     *     schema="Installment",
     *     title="installment",
     *     description="Modelo de installment",
     *     required={"id","name","serie","state"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID de la installment"
     *     ),
     *     @OA\Property(
     *         property="number",
     *         type="string",
     *         description="Nombre de la installment"
     *     ),

     *     @OA\Property(
     *         property="date",
     *         type="date",
     *         description="Estado de la installment"
     *     ),
     *
     *     @OA\Property(
     *
     *         property="total",
     *         type="integer",
     *         description="Nombre de la installment"
     *     ),
     *      @OA\Property(
     *         property="moviment_id",
     *         type="boolean",
     *         description="Branch Office"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Fecha de creación de la installment"
     *     ),
     * )
     */

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    public function moviment()
    {
        return $this->belongsTo(Moviment::class, 'moviment_id');
    }

    public function payInstallments()
    {
        return $this->hasMany(PayInstallment::class);
    }
    public function resumenPagos()
    {
        $pagos = $this->payInstallments()->get();
    
        if ($pagos->isEmpty()) {
            return "No tiene Pagos Realizados";
        }
    
        return $pagos->map(function ($pago) {
            // Si el tipo es "Nota Credito", usa comment como número de operación; de lo contrario, usa nroOperacion
            $operacion = ($pago->type === "Nota Credito") 
                ? "N° Operación: {$pago->comment} | " 
                : ($pago->nroOperacion != "0" ? "N° Operación: {$pago->nroOperacion} | " : "");
    
            // Banco, si está disponible
            $banco = $pago?->bank?->name ? "Banco: {$pago?->bank?->name} | " : "";
    
            return "{$operacion}Tipo: {$pago->type} | {$banco}Monto: S/ {$pago->total} | Fecha de Pago: {$pago->paymentDate}";
        })->implode("\n");
    }
    
    
    
    

    public function updateMontos()
    {
        $this->totalDebt = $this->total - $this->payInstallments->sum('total');

        // Obtener la fecha actual y la fecha de vencimiento
        $today = now()->toDateString();

        // Asegurarse de que $this->date sea una fecha válida
        $dueDate = \Carbon\Carbon::parse($this->date)->toDateString();

// Determinar el estado basado en totalDebt y la fecha de vencimiento
        if ($this->totalDebt == 0) {
            $this->status = 'Pagado'; // Asignar 'Pagado' si la deuda es 0
        } elseif ($dueDate < $today) {
            $this->status = 'Vencido'; // Asignar 'Vencido' si la fecha de vencimiento ya pasó y aún hay deuda
        } else {
            $this->status = 'Pendiente'; // Asignar 'Pendiente' si hay deuda y no está vencida
        }

        // Obtener la venta relacionada
        $venta                = Moviment::find($this->moviment_id);
        $payInstallmentsTotal = $this->payInstallments->sum('total');

        // Validar si el total de payInstallments cubre el total de venta
        if ($venta && $payInstallmentsTotal == $venta->total) {
            $venta->status = "Pagado";
        } else {
            $venta->status = "Pendiente";
        }
        $venta->saldo = $this->totalDebt;
        // Guardar el estado actualizado de la venta
        $venta->save();

        // Guardar el modelo actualizado
        $this->save();
    }

}
