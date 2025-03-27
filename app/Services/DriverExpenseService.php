<?php
namespace App\Services;

use App\Models\DriverExpense;
use App\Models\Programming;
use Carbon\Carbon;

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

}
