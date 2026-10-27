<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px 20px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 10px; }
        .summary { margin: 8px 0 12px; }
        .summary span { display: inline-block; margin-right: 22px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1f4e78; color: white; font-weight: bold; }
        th, td { border: 1px solid #c7c7c7; padding: 4px; }
        td.number { text-align: right; }
        .paid { color: #187a3d; font-weight: bold; }
        .debt, .overdue { color: #b42318; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte de compras de combustible</h1>
    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }}
        @if(!empty($filters['date_start']) || !empty($filters['date_end']))
            | Rango: {{ $filters['date_start'] ?? '—' }} al {{ $filters['date_end'] ?? '—' }}
        @endif
        @if(!empty($filters['plate'])) | Placa: {{ $filters['plate'] }} @endif
        @if(!empty($filters['fuel_station'])) | Grifo: {{ $filters['fuel_station'] }} @endif
        @if(!empty($filters['status'])) | Estado: {{ $filters['status'] }} @endif
    </div>
    <div class="summary">
        <span>Registros: {{ $purchases->count() }}</span>
        <span>Total compras: S/ {{ number_format($totals['amount'], 2) }}</span>
        <span>Pagado: S/ {{ number_format($totals['paid'], 2) }}</span>
        <span>En deuda: S/ {{ number_format($totals['debt'], 2) }}</span>
    </div>
    <table>
        <thead><tr>
            <th>Fecha</th><th>Grifo</th><th>Placa</th><th>Chofer</th><th>Producto</th>
            <th>Cantidad</th><th>Precio unit.</th><th>Total</th><th>Factura</th>
            <th>Condición</th><th>Vencimiento</th><th>Situación</th>
        </tr></thead>
        <tbody>
        @forelse($purchases as $purchase)
            @php
                $person = $purchase->worker?->person;
                $driver = trim(implode(' ', array_filter([$person?->names, $person?->fatherSurname, $person?->motherSurname])));
                $status = $purchase->reportStatus();
            @endphp
            <tr>
                <td>{{ optional($purchase->fuel_date)->format('d/m/Y') }}</td>
                <td>{{ $purchase->station?->name }}</td>
                <td>{{ $purchase->vehicle?->currentPlate }}</td>
                <td>{{ $driver }}</td>
                <td>{{ $purchase->fuel_type }}</td>
                <td class="number">{{ number_format((float) $purchase->quantity, 3) }}</td>
                <td class="number">S/ {{ number_format((float) $purchase->unit_price, 4) }}</td>
                <td class="number">S/ {{ number_format((float) $purchase->total, 2) }}</td>
                <td>{{ $purchase->invoice_number }}</td>
                <td>{{ $purchase->payment_condition }}</td>
                <td>{{ optional($purchase->due_date)->format('d/m/Y') }}</td>
                <td class="{{ $status === 'Pagado' ? 'paid' : ($status === 'Vencido' ? 'overdue' : 'debt') }}">{{ $status }}</td>
            </tr>
        @empty
            <tr><td colspan="12">No se encontraron abastecimientos con los filtros indicados.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
