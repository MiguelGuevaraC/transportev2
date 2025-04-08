<?php
namespace App\Services;

use App\Models\DriverExpense;
use App\Models\Payable;
use App\Models\Programming;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverExpenseService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getDriverExpenseById(int $id): ?DriverExpense
    {
        return DriverExpense::find($id);
    }

    public function createDriverExpense(array $data): DriverExpense
    {
        $proyect = DriverExpense::create($data);
        return $proyect;
    }

    public function updateDriverExpense(DriverExpense $proyect, array $data): DriverExpense
    {
        $proyect->update($data);
        return $proyect;
    }
    public function transferDriverExpense(array $data): array
    {
        $programming_in   = Programming::find($data['programming_in_id']);
        $driverExpenseOut = DriverExpense::create([
            'worker_id'          => $data['driver_id'],
            'expensesConcept_id' => 23,
            'programming_id'     => $data['programming_out_id'],
            'type'               => 'EGRESO',
            'amount'             => ($data['amount']), // Aseguramos que sea negativo
            'total'              => ($data['amount']),
            'selectTypePay'      => "Efectivo",
            'date_expense'       => Carbon::now(),
            'comment'            => 'Transferencia de saldo a programación ' . $programming_in->numero,
        ]);

        $programming_out = Programming::find($data['programming_out_id']);
        $driverExpenseIn = DriverExpense::create([
            'worker_id'          => $data['driver_id'],
            'expensesConcept_id' => 22,
            'programming_id'     => $data['programming_in_id'],
            'type'               => 'INGRESO',
            'amount'             => ($data['amount']), // Aseguramos que sea positivo
            'total'              => ($data['amount']),
            'selectTypePay'      => "Efectivo",
            'date_expense'       => Carbon::now(),
            'comment'            => 'Transferencia de saldo desde programación ' . $programming_out->numero,
        ]);

        return [
            "driver_expense_out" => $driverExpenseOut,
            "driver_expense_in"  => $driverExpenseIn,
        ];

    }

    public function montos_programacion($programming)
    {
        $totalIngreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Ingreso');
        })->sum('total');

        $totalEgreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Egreso');
        })->sum('total');
        $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');
        return [
            'total_ingreso' => number_format($totalIngreso, 2, '.', ''),
            'total_egreso'  => number_format($totalEgreso, 2, '.', ''),
            'saldo'         => number_format((float) $saldo, 2, '.', ''),
        ];
    }

    public function destroyById($id)
    {
        return DriverExpense::find($id)?->delete() ?? false;
    }

    public function generate_credit_payments($id, $dias = 0)
    {
        $expense = DriverExpense::find($id);

        if (! $expense) {
            return false; // Si no se encuentra el gasto, retornamos falso
        }
        $tipo         = 'CP01';
        $siguienteNum = DB::table('payables')
            ->whereRaw('SUBSTRING_INDEX(number, "-", 1) = ?', [$tipo])
            ->max(DB::raw('CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)')) + 1;

        // Crear la cuenta por pagar asociada
        $payableData = [
            'number'            => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'days'              => $dias,
            'date'              => now()->addDays($dias),
            'total'             => $expense->total,  // Total de la deuda (basado en el gasto)
            'totalDebt'         => $expense->total,  // Monto de la deuda (puedes ajustarlo si es necesario)
            'driver_expense_id' => $expense->id,     // Relacionamos la cuenta por pagar con el gasto
            'user_created_id'   => Auth::user()->id, // Relacionamos la cuenta por pagar con el gasto

            'person_id'         => $expense?->proveedor_id ?? null,
            'correlativo_ref'   => $expense?->operationNumber,
            'type_document_id'  => $expense?->type_document_id,
            'type_payable'      => "GASTO CONDUCTOR",
        ];

        $payable = Payable::create($payableData);

        return $payable ? true : false; // Retornar verdadero si la cuenta por pagar se creó correctamente
    }

}
