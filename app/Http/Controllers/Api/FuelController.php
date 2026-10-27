<?php

namespace App\Http\Controllers\Api;

use App\Exports\FuelPurchasesExport;
use App\Http\Controllers\Controller;
use App\Models\FuelPurchase;
use App\Models\FuelStation;
use App\Models\FuelVehicleDriverAssignment;
use App\Models\Payable;
use App\Models\PayPayable;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class FuelController extends Controller
{
    public function stations(Request $request)
    {
        return response()->json(FuelStation::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')->paginate($request->integer('per_page', 20)));
    }

    public function storeStation(Request $request)
    {
        $data = $this->validateData($request, [
            'name' => 'required|string|max:150',
            'ruc' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'credit_days' => 'nullable|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);

        return response()->json(FuelStation::create($data), 201);
    }

    public function updateStation(Request $request, $id)
    {
        $station = FuelStation::find($id);
        if (! $station) return response()->json(['message' => 'Grifo no encontrado'], 404);

        $data = $this->validateData($request, [
            'name' => 'sometimes|string|max:150',
            'ruc' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'credit_days' => 'nullable|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);
        $station->update($data);
        return response()->json($station);
    }

    public function deleteStation($id)
    {
        $station = FuelStation::find($id);
        if (! $station) return response()->json(['message' => 'Grifo no encontrado'], 404);
        if ($station->purchases()->exists()) return response()->json(['message' => 'No se puede eliminar un grifo con abastecimientos registrados'], 422);
        $station->delete();
        return response()->json(['message' => 'Grifo eliminado correctamente']);
    }

    public function assignments(Request $request)
    {
        $query = FuelVehicleDriverAssignment::with(['vehicle:id,currentPlate,numberMtc', 'worker.person:id,names,fatherSurname,motherSurname,businessName'])
            ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->filled('worker_id'), fn ($q) => $q->where('worker_id', $request->worker_id))
            ->orderByDesc('active')->orderByDesc('start_date');

        return response()->json($query->paginate($request->integer('per_page', 20)));
    }

    public function storeAssignment(Request $request)
    {
        $data = $this->validateData($request, [
            'vehicle_id' => 'required|exists:vehicles,id',
            'worker_id' => 'required|exists:workers,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'active' => 'sometimes|boolean',
        ]);

        $overlap = FuelVehicleDriverAssignment::where('vehicle_id', $data['vehicle_id'])
            ->where('active', true)
            ->where(function ($q) use ($data) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $data['start_date']);
            })->where('start_date', '<=', $data['end_date'] ?? '9999-12-31')->exists();

        if ($overlap) return response()->json(['error' => 'El vehículo ya tiene una asignación activa en ese periodo.'], 422);

        return response()->json(FuelVehicleDriverAssignment::create($data), 201);
    }

    public function updateAssignment(Request $request, $id)
    {
        $assignment = FuelVehicleDriverAssignment::find($id);
        if (! $assignment) return response()->json(['message' => 'Asignación no encontrada'], 404);
        $data = $this->validateData($request, [
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'worker_id' => 'sometimes|exists:workers,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'active' => 'sometimes|boolean',
        ]);
        $assignment->update($data);
        return response()->json($assignment->load('vehicle', 'worker.person'));
    }

    public function deleteAssignment($id)
    {
        $assignment = FuelVehicleDriverAssignment::find($id);
        if (! $assignment) return response()->json(['message' => 'Asignación no encontrada'], 404);
        if ($assignment->purchases()->exists()) return response()->json(['message' => 'No se puede eliminar una asignación con abastecimientos registrados'], 422);
        $assignment->delete();
        return response()->json(['message' => 'Asignación eliminada correctamente']);
    }

    public function purchases(Request $request)
    {
        $query = $this->purchaseQuery($request);
        $result = $query->paginate($request->integer('per_page', 20));
        return response()->json([
            'total' => $result->total(),
            'data' => $result->items(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'per_page' => $result->perPage(),
        ]);
    }

    public function storePurchase(Request $request)
    {
        $data = $this->validateData($request, [
            'fuel_station_id' => 'required|exists:fuel_stations,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'worker_id' => 'required|exists:workers,id',
            'assignment_id' => 'required|exists:fuel_vehicle_driver_assignments,id',
            'fuel_date' => 'required|date',
            'odometer_km' => 'required|numeric|min:0',
            'fuel_type' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'required|numeric|min:0',
            'payment_condition' => 'required|in:Contado,Crédito',
            'payment_method' => 'required_if:payment_condition,Contado|nullable|string|max:50',
            'payment_operation_number' => 'nullable|string|max:100',
            'invoice_number' => 'required|string|max:100',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'observation' => 'nullable|string',
            'odometer_image' => 'required|file|image|max:10240',
            'pump_start_image' => 'required|file|image|max:10240',
            'pump_end_image' => 'required|file|image|max:10240',
        ]);

        $assignment = FuelVehicleDriverAssignment::find($data['assignment_id']);
        if ((int) $assignment->vehicle_id !== (int) $data['vehicle_id'] || (int) $assignment->worker_id !== (int) $data['worker_id']) {
            return response()->json(['error' => 'La asignación no corresponde al vehículo y chofer seleccionados.'], 422);
        }
        if (! $assignment->active || $data['fuel_date'] < $assignment->start_date->format('Y-m-d') || ($assignment->end_date && $data['fuel_date'] > $assignment->end_date->format('Y-m-d'))) {
            return response()->json(['error' => 'La asignación no está vigente para la fecha del abastecimiento.'], 422);
        }

        $station = FuelStation::find($data['fuel_station_id']);
        if (! $station->active) return response()->json(['error' => 'El grifo está inactivo.'], 422);

        $data['total'] = round((float) $data['quantity'] * (float) $data['unit_price'], 2);
        $data['assignment_id'] = $assignment->id;
        $data['user_id'] = auth()->id();
        $data['fuel_type'] = $data['fuel_type'] ?? 'Petróleo';

        foreach (['odometer_image', 'pump_start_image', 'pump_end_image'] as $field) {
            $data[$field] = Storage::url($request->file($field)->store('public/fuel-control'));
        }

        $purchase = DB::transaction(function () use ($data, $station) {
            $purchase = FuelPurchase::create($data);

            if ($purchase->payment_condition === 'Crédito') {
                $dueDate = $purchase->due_date ?: Carbon::parse($purchase->invoice_date ?: $purchase->fuel_date)->addDays($station->credit_days);
                $number = 'CF-' . str_pad((string) ($purchase->id), 8, '0', STR_PAD_LEFT);
                Payable::create([
                    'number' => $number,
                    'date' => $dueDate->toDateString(),
                    'total' => $purchase->total,
                    'totalDebt' => $purchase->total,
                    'status' => 'Pendiente',
                    'person_id' => null,
                    'fuel_purchase_id' => $purchase->id,
                    'type_payable' => 'COMBUSTIBLE',
                    'correlativo_ref' => $purchase->invoice_number,
                    'user_created_id' => auth()->id(),
                ]);
                $purchase->update(['due_date' => $dueDate->toDateString(), 'status' => 'Pendiente']);
            } else {
                $purchase->update([
                    'status' => 'Pagado',
                    'paid_at' => $purchase->fuel_date,
                    'payment_operation_number' => $purchase->payment_operation_number,
                ]);
            }

            return $purchase;
        });

        return response()->json($purchase->load('station', 'vehicle', 'worker.person', 'payable'), 201);
    }

    public function showPurchase($id)
    {
        $purchase = FuelPurchase::with('station', 'vehicle', 'worker.person', 'assignment', 'payable.payPayables')->find($id);
        return $purchase ? response()->json($purchase) : response()->json(['message' => 'Abastecimiento no encontrado'], 404);
    }

    public function deletePurchase($id)
    {
        $purchase = FuelPurchase::find($id);
        if (! $purchase) return response()->json(['message' => 'Abastecimiento no encontrado'], 404);
        if ($purchase->payable?->payPayables()->exists()) return response()->json(['error' => 'No se puede eliminar un abastecimiento con pagos registrados.'], 422);
        $purchase->delete();
        return response()->json(['message' => 'Abastecimiento eliminado correctamente']);
    }

    public function payCredit(Request $request, $id)
    {
        $purchase = FuelPurchase::with('payable')->find($id);
        if (! $purchase || ! $purchase->payable) return response()->json(['error' => 'No existe una cuenta por pagar para este abastecimiento.'], 404);

        $data = $this->validateData($request, [
            'paymentDate' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'operation_number' => 'nullable|string|max:100',
            'comment' => 'nullable|string',
        ]);
        $payable = $purchase->payable;
        if ((float) $data['amount'] > (float) $payable->totalDebt) return response()->json(['error' => 'El pago excede la deuda pendiente.'], 422);

        DB::transaction(function () use ($data, $payable, $purchase) {
            PayPayable::create([
                'number' => 'PC-' . str_pad((string) ($payable->id), 8, '0', STR_PAD_LEFT) . '-' . now()->format('His'),
                'paymentDate' => $data['paymentDate'],
                'total' => $data['amount'],
                'cash' => strtoupper($data['payment_method']) === 'EFECTIVO' ? $data['amount'] : 0,
                'deposit' => strtoupper($data['payment_method']) === 'DEPOSITO' ? $data['amount'] : 0,
                'comment' => $data['comment'] ?? null,
                'nroOperacion' => $data['operation_number'] ?? null,
                'status' => 'Pagado',
                'type' => 'COMBUSTIBLE',
                'payable_id' => $payable->id,
                'user_created_id' => auth()->id(),
            ]);
            $payable->totalDebt = max(0, (float) $payable->totalDebt - (float) $data['amount']);
            $payable->status = $payable->totalDebt == 0 ? 'Pagado' : 'Pendiente';
            $payable->save();
            $purchase->update(['status' => $payable->status]);
        });

        return response()->json($purchase->fresh()->load('payable.payPayables'));
    }

    public function payables(Request $request)
    {
        $query = FuelPurchase::with(['station', 'vehicle', 'worker.person', 'payable'])
            ->where('payment_condition', 'Crédito')
            ->whereHas('payable', function ($q) use ($request) {
                if ($request->filled('status')) {
                    $q->where('status', $request->input('status'));
                } else {
                    $q->where('status', '!=', 'Pagado');
                }
            })->orderBy('due_date');

        $result = $query->paginate($request->integer('per_page', 20));
        $result->getCollection()->transform(function ($purchase) {
            if ($purchase->payable && $purchase->payable->status !== 'Pagado' && $purchase->due_date?->isPast()) {
                $purchase->status = 'Vencido';
                $purchase->payable->status = 'Vencido';
            }
            return $purchase;
        });

        return response()->json($result);
    }

    public function exportPurchases(Request $request)
    {
        return Excel::download(new FuelPurchasesExport($this->purchaseQuery($request)->get()), 'reporte_compras_combustible_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportPurchasesPdf(Request $request)
    {
        $purchases = $this->purchaseQuery($request)->get();
        $totals = [
            'amount' => $purchases->sum(fn ($purchase) => (float) $purchase->total),
            'paid' => $purchases->filter(fn ($purchase) => $purchase->reportStatus() === 'Pagado')->sum(fn ($purchase) => (float) $purchase->total),
            'debt' => $purchases->filter(fn ($purchase) => in_array($purchase->reportStatus(), ['En deuda', 'Vencido'], true))->sum(fn ($purchase) => (float) ($purchase->payable?->totalDebt ?? $purchase->total)),
        ];

        return Pdf::loadView('fuel-purchases-report', [
            'purchases' => $purchases,
            'totals' => $totals,
            'filters' => $request->only(['date_start', 'date_end', 'plate', 'vehicle_id', 'fuel_station', 'fuel_station_id', 'status']),
        ])->setPaper('a4', 'landscape')
            ->download('reporte_compras_combustible_' . now()->format('Ymd_His') . '.pdf');
    }

    protected function purchaseQuery(Request $request)
    {
        return FuelPurchase::with(['station', 'vehicle', 'worker.person', 'payable'])
            ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->filled('plate'), fn ($q) => $q->whereHas('vehicle', fn ($vehicle) => $vehicle->where('currentPlate', 'like', '%' . $request->plate . '%')))
            ->when($request->filled('worker_id'), fn ($q) => $q->where('worker_id', $request->worker_id))
            ->when($request->filled('fuel_station_id'), fn ($q) => $q->where('fuel_station_id', $request->fuel_station_id))
            ->when($request->filled('fuel_station'), fn ($q) => $q->whereHas('station', fn ($station) => $station->where('name', 'like', '%' . $request->fuel_station . '%')))
            ->when($request->filled('date_start'), fn ($q) => $q->whereDate('fuel_date', '>=', $request->date_start))
            ->when($request->filled('date_end'), fn ($q) => $q->whereDate('fuel_date', '<=', $request->date_end))
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = strtolower(trim((string) $request->status));

                if ($status === 'pagado') {
                    $q->where(function ($query) {
                        $query->where('fuel_purchases.payment_condition', 'Contado')
                            ->orWhere('fuel_purchases.status', 'Pagado')
                            ->orWhereHas('payable', fn ($payable) => $payable->where('status', 'Pagado')->orWhere('totalDebt', '<=', 0));
                    });
                } elseif (in_array($status, ['deuda', 'en deuda', 'pendiente'], true)) {
                    $q->whereHas('payable', fn ($payable) => $payable->where('totalDebt', '>', 0));
                } elseif ($status === 'vencido') {
                    $q->whereHas('payable', fn ($payable) => $payable->where('totalDebt', '>', 0))
                        ->whereDate('fuel_purchases.due_date', '<', today());
                }
            })
            ->orderByDesc('fuel_date')->orderByDesc('id');
    }

    protected function validateData(Request $request, array $rules): array
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) abort(response()->json(['error' => $validator->errors()->first()], 422));
        return $validator->validated();
    }
}
