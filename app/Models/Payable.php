<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Payable",
 *     required={"id", "number", "date", "total"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="number", type="string"),
 *     @OA\Property(property="days", type="integer"),
 *     @OA\Property(property="date", type="string", format="date"),
 *     @OA\Property(property="total", type="number", format="float"),
 *     @OA\Property(property="totalDebt", type="number", format="float"),
 *     @OA\Property(property="driver_expense_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */

class Payable extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'number',
        'days',
        'date',
        'total',
        'totalDebt',
        'status',

        'person_id',
        'correlativo_ref',
        'type_document_id',
        'type_payable',

        'driver_expense_id',
        'compra_moviment_id',
        'user_created_id',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
    const filters = [
        'number'                        => 'like',
        'days'                          => '=',
        'date'                          => 'between',
        'total'                         => '=',
        'totalDebt'                     => '=',
        'driver_expense_id'             => '=',
        'driver_expense.proveedor_id'   => '=',
        'driver_expense.programming_id' => '=',
        'status'                        => '=',
         'compra_moviment_id'=> '=',

        'person_id'                     => '=',
        'correlativo_ref'               => 'like',
        'type_document_id'              => '=',
        'type_payable'                  => 'like',
    ];

    const sorts = [
        'id' => 'desc',
    ];
    public function driver_expense()
    {
        return $this->belongsTo(DriverExpense::class, 'driver_expense_id');
    }
    public function compra_moviment()
    {
        return $this->belongsTo(CompraMoviment::class, 'compra_moviment_id');
    }

    public function payPayables()
    {
        return $this->hasMany(PayPayable::class);
    }
    public function user_created()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }
    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
    public function type_document()
    {
        return $this->belongsTo(Type_document::class, 'type_document_id');
    }

    public function updateMontos()
    {
        $this->totalDebt = $this->total - $this->payPayables->sum('total');

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

        // Guardar el modelo actualizado
        $this->save();
    }
}
