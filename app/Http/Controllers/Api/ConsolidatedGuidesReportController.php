<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarrierGuide;
use App\Models\Installment;
use App\Models\Reception;
use App\Exports\ConsolidatedGuidesExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidatedGuidesReportController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->all();

        return Excel::download(
            new ConsolidatedGuidesExport(
                $this->receptions($filters),
                $this->guides($filters),
                $this->receivables($filters),
            ),
            'reporte_consolidado_guias_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    private function receptions(array $filters): array
    {
        $query = Reception::with([
            'origin', 'destination', 'sender', 'recipient', 'payResponsible',
            'details', 'moviment.installments', 'firstCarrierGuide',
        ])->when($filters['branch_office_id'] ?? null, fn ($q, $value) => $q->where('branchOffice_id', $value));

        $this->applyDateRange($query, 'receptionDate', $filters['dateStart'] ?? null, $filters['dateEnd'] ?? null);

        $query->when($filters['codeReception'] ?? null, fn ($q, $value) => $q->where('codeReception', 'like', "%{$value}%"));
        $query->when($filters['numberVenta'] ?? null, fn ($q, $value) => $q->where(function ($sub) use ($value) {
            $sub->where('nro_sale', 'like', "%{$value}%")
                ->orWhereHas('moviment', fn ($movement) => $movement->where('sequentialNumber', 'like', "%{$value}%"));
        }));
        $query->when($filters['numberGuia'] ?? null, fn ($q, $value) => $q->whereHas('firstCarrierGuide', fn ($guide) => $guide->where('numero', 'like', "%{$value}%")));
        $query->when($filters['documentGuia'] ?? null, fn ($q, $value) => $q->whereHas('firstCarrierGuide', fn ($guide) => $guide->where('document', 'like', "%{$value}%")));
        $query->when($filters['glosa'] ?? ($filters['observacion'] ?? null), fn ($q, $value) => $q->where(function ($sub) use ($value) {
            $term = '%' . mb_strtolower($value) . '%';
            $sub->whereRaw('LOWER(comment) LIKE ?', [$term])
                ->orWhereHas('details', fn ($detail) => $detail->whereRaw('LOWER(description) LIKE ?', [$term]));
        }));

        return $query->orderByDesc('id')->limit(3000)->get()->map(function ($reception) {
            $guide = $reception->firstCarrierGuide;
            $movement = $reception->moviment;

            return [
                $reception->codeReception,
                $reception->receptionDate,
                $guide?->numero ?? 'Sin guía',
                $reception->nro_sale ?? $movement?->sequentialNumber,
                $this->personName($reception->sender),
                $this->personName($reception->recipient),
                $reception->origin?->name,
                $reception->destination?->name,
                $this->personName($reception->payResponsible),
                $reception->paymentAmount,
                $reception->debtAmount,
                $reception->details->pluck('description')->implode(', '),
                $reception->netWeight,
                $this->invoiceStatus($movement),
                $guide ? ($guide->programming ? 'Programado' : 'Sin Programar') : 'Sin Guía',
            ];
        })->values()->all();
    }

    private function guides(array $filters): array
    {
        $query = CarrierGuide::with(['sender', 'recipient', 'origin', 'destination', 'tract', 'driver.person']);
        $query->when($filters['branch_office_id'] ?? null, fn ($q, $value) => $q->where('branchOffice_id', $value));
        $query->when($filters['numero'] ?? ($filters['numberGuia'] ?? null), fn ($q, $value) => $q->where('numero', 'like', "%{$value}%"));
        $query->when($filters['document'] ?? ($filters['documentGuia'] ?? null), fn ($q, $value) => $q->where('document', 'like', "%{$value}%"));
        $query->when($filters['typeGuia'] ?? null, fn ($q, $value) => $q->where('type', $value));
        $query->when($filters['statusGuia'] ?? null, fn ($q, $value) => $q->where('status', $value));
        $query->when($filters['statusFacturado'] ?? null, fn ($q, $value) => $q->where('status_facturado', $value));
        $query->when($filters['observation'] ?? ($filters['glosa'] ?? null), fn ($q, $value) => $q->where('observation', 'like', "%{$value}%"));

        $dateType = strtolower($filters['dateType'] ?? 'guide');
        if (in_array($dateType, ['invoice', 'factura'], true)) {
            $query->whereHas('reception.moviment', function ($movement) use ($filters) {
                $this->applyDateRange($movement, 'paymentDate', $filters['startDate'] ?? ($filters['dateStart'] ?? null), $filters['endDate'] ?? null);
            });
        } else {
            $this->applyDateRange($query, 'created_at', $filters['startDate'] ?? ($filters['dateStart'] ?? null), $filters['endDate'] ?? ($filters['dateEnd'] ?? null));
        }

        return $query->orderByDesc('id')->limit(3000)->get()->map(fn ($guide) => [
            $guide->id,
            $guide->numero,
            $guide->type,
            $guide->status,
            strtolower((string) $guide->type) === 'manual' ? 'Enviado' : $guide->status_facturado,
            $guide->created_at,
            $guide->date_cargo,
            $guide->date_est_facturacion,
            $this->personName($guide->sender),
            $this->personName($guide->recipient),
            $guide->origin?->name,
            $guide->destination?->name,
            $guide->tract?->currentPlate,
            $this->personName($guide->driver?->person),
            $guide->document,
            $guide->observation,
        ])->values()->all();
    }

    private function receivables(array $filters): array
    {
        $query = Installment::with(['moviment.person', 'payInstallments'])
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['person_id'] ?? null, fn ($q, $value) => $q->whereHas('moviment', fn ($movement) => $movement->where('person_id', $value)))
            ->when($filters['branch_office_id'] ?? ($filters['branchOffice_id'] ?? null), fn ($q, $value) => $q->whereHas('moviment', fn ($movement) => $movement->where('branchOffice_id', $value)))
            ->when($filters['sequentialNumber'] ?? null, fn ($q, $value) => $q->whereHas('moviment', fn ($movement) => $movement->where('sequentialNumber', 'like', "%{$value}%")));

        $start = $filters['start'] ?? ($filters['dateStart'] ?? null);
        $end = $filters['end'] ?? ($filters['dateEnd'] ?? null);
        $query->when($start, fn ($q) => $q->whereHas('moviment', fn ($movement) => $movement->where('paymentDate', '>=', $start)))
            ->when($end, fn ($q) => $q->whereHas('moviment', fn ($movement) => $movement->where('paymentDate', '<=', $end)));

        return $query->orderByDesc('id')->limit(3000)->get()->map(function ($item, $index) {
            $movement = $item->moviment;
            $person = $movement?->person;
            $total = (float) ($item->total ?? 0);
            $debt = (float) ($item->totalDebt ?? 0);
            $detraction = $movement?->codeDetraction === '027' ? round($total * .04) : ($movement?->codeDetraction === '021' ? round($total * .10) : 0);

            return [
                $index + 1,
                $person?->documentNumber,
                $this->personName($person),
                $movement?->paymentDate,
                $item->date,
                $item->date ? Carbon::parse($item->date)->diffInDays(Carbon::today(), false) : '',
                $movement?->sequentialNumber,
                $total,
                $debt,
                $detraction,
                $total - $detraction,
                $item->status,
                method_exists($item, 'resumenPagos') ? $item->resumenPagos() : '',
            ];
        })->values()->all();
    }

    private function applyDateRange($query, string $column, ?string $start, ?string $end): void
    {
        $query->when($start, fn ($q) => $q->where($column, '>=', $start));
        $query->when($end, fn ($q) => $q->where($column, '<', Carbon::parse($end)->addDay()->startOfDay()));
    }

    private function personName($person): string
    {
        if (! $person) return '';
        return $person->businessName ?: trim(($person->names ?? '') . ' ' . ($person->fatherSurname ?? '') . ' ' . ($person->motherSurname ?? ''));
    }

    private function invoiceStatus($movement): string
    {
        if (! $movement) return 'Sin Factura';
        if ($movement->status === 'Pagado' || (float) ($movement->saldo ?? 0) <= 0) return 'Pagado';
        return $movement->installments->contains(fn ($item) => (float) ($item->totalDebt ?? 0) > 0 && $item->date && Carbon::parse($item->date)->isPast()) ? 'Vencido' : 'Pendiente';
    }
}
