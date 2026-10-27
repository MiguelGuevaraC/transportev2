<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelPurchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fuel_station_id', 'vehicle_id', 'worker_id', 'assignment_id', 'fuel_date',
        'odometer_km', 'fuel_type', 'quantity', 'unit_price', 'total',
        'payment_condition', 'payment_method', 'invoice_number', 'invoice_date',
        'paid_at', 'payment_operation_number', 'due_date', 'odometer_image', 'pump_start_image', 'pump_end_image',
        'observation', 'status', 'user_id',
    ];

    protected $casts = [
        'fuel_date' => 'date:Y-m-d',
        'invoice_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'paid_at' => 'date:Y-m-d',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'total' => 'decimal:2',
        'odometer_km' => 'decimal:2',
    ];

    public function station() { return $this->belongsTo(FuelStation::class, 'fuel_station_id'); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function worker() { return $this->belongsTo(Worker::class); }
    public function assignment() { return $this->belongsTo(FuelVehicleDriverAssignment::class, 'assignment_id'); }
    public function payable() { return $this->hasOne(Payable::class, 'fuel_purchase_id'); }

    /**
     * Situación calculada para reportes: Pagado, En deuda o Vencido.
     * No modifica el estado almacenado y considera también el saldo real de la cuenta por pagar.
     */
    public function reportStatus(): string
    {
        if ($this->payment_condition === 'Contado'
            || $this->status === 'Pagado'
            || ($this->payable && $this->payable->status === 'Pagado')
            || ($this->payable && (float) $this->payable->totalDebt <= 0)) {
            return 'Pagado';
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return 'Vencido';
        }

        return 'En deuda';
    }
}
