<?php

namespace App\Http\Controllers\Api;

use App\Exports\CajaYadiraExport;
use App\Http\Controllers\Controller;
use App\Models\CajaYadiraMovement;
use App\Models\Person;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CajaYadiraController extends Controller
{
    public function paymentMethod()
    {
        return response()->json([
            'code' => 'CAJA_YADIRA',
            'name' => 'Caja Yadira',
        ]);
    }

    public function index(Request $request)
    {
        $query = CajaYadiraMovement::with('person:id,names,fatherSurname,motherSurname,businessName,typeofDocument')
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        $this->applyFilters($query, $request);

        $movements = $query->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'total' => $movements->total(),
            'data' => $movements->items(),
            'current_page' => $movements->currentPage(),
            'last_page' => $movements->lastPage(),
            'per_page' => $movements->perPage(),
            'summary' => $this->summary($request),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movement_date' => 'required|date',
            'movement_time' => 'nullable|date_format:H:i',
            'type' => 'required|in:Ingreso,Egreso',
            'operation_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'person_id' => 'nullable|exists:people,id',
            'concept' => 'required|string|max:255',
            'observation' => 'nullable|string',
            'image' => 'nullable|file|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $movement = new CajaYadiraMovement($validator->validated());
        $movement->user_id = auth()->id();

        if ($request->hasFile('image')) {
            $movement->voucher_path = Storage::url(
                $request->file('image')->store('public/cajaYadira')
            );
        }

        $movement->save();

        return response()->json(
            $movement->load('person:id,names,fatherSurname,motherSurname,businessName,typeofDocument'),
            201
        );
    }

    public function show($id)
    {
        $movement = CajaYadiraMovement::with('person', 'user')->find($id);

        if (! $movement) {
            return response()->json(['message' => 'Movimiento de Caja Yadira no encontrado'], 404);
        }

        return response()->json($movement);
    }

    public function destroy($id)
    {
        $movement = CajaYadiraMovement::find($id);

        if (! $movement) {
            return response()->json(['message' => 'Movimiento de Caja Yadira no encontrado'], 404);
        }

        $movement->delete();

        return response()->json(['message' => 'Movimiento eliminado correctamente']);
    }

    public function exportExcel(Request $request)
    {
        $movements = $this->filteredQuery($request)->get();

        return Excel::download(
            new CajaYadiraExport($movements),
            'rendicion_caja_yadira_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $movements = $this->filteredQuery($request)->get();
        $summary = $this->summary($request);

        return Pdf::loadView('caja-yadira-report', [
            'movements' => $movements,
            'summary' => $summary,
            'dateStart' => $request->input('date_start'),
            'dateEnd' => $request->input('date_end'),
        ])->download('rendicion_caja_yadira_' . now()->format('Ymd_His') . '.pdf');
    }

    protected function filteredQuery(Request $request)
    {
        $query = CajaYadiraMovement::with('person:id,names,fatherSurname,motherSurname,businessName,typeofDocument')
            ->orderBy('movement_date')
            ->orderBy('id');

        return $this->applyFilters($query, $request);
    }

    protected function applyFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('date_start'), fn ($q) => $q->whereDate('movement_date', '>=', $request->input('date_start')))
            ->when($request->filled('date_end'), fn ($q) => $q->whereDate('movement_date', '<=', $request->input('date_end')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('concept'), fn ($q) => $q->where('concept', 'like', '%' . $request->input('concept') . '%'));
    }

    protected function summary(Request $request): array
    {
        $summary = $this->filteredQuery($request)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'Ingreso' THEN amount ELSE 0 END), 0) as ingresos")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'Egreso' THEN amount ELSE 0 END), 0) as egresos")
            ->first();

        return [
            'ingresos' => (float) $summary->ingresos,
            'egresos' => (float) $summary->egresos,
            'saldo' => (float) $summary->ingresos - (float) $summary->egresos,
        ];
    }
}
