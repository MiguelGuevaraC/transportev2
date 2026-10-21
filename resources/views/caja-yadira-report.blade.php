<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .summary { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #777; padding: 4px; }
        th { background: #e8e8e8; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>RENDICIÓN CAJA YADIRA</h2>
    <div class="summary">
        Ingresos: S/ {{ number_format($summary['ingresos'], 2) }} &nbsp;&nbsp;
        Egresos: S/ {{ number_format($summary['egresos'], 2) }} &nbsp;&nbsp;
        Saldo: S/ {{ number_format($summary['saldo'], 2) }}
        @if($dateStart || $dateEnd)
            &nbsp;&nbsp; Periodo: {{ $dateStart ?: 'Inicio' }} - {{ $dateEnd ?: 'Actual' }}
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th><th>Hora</th><th>Tipo</th><th>N° operación</th>
                <th>Monto</th><th>Persona</th><th>Concepto</th><th>Observación</th>
            </tr>
        </thead>
        <tbody>
        @foreach($movements as $movement)
            <tr>
                <td>{{ optional($movement->movement_date)->format('d/m/Y') }}</td>
                <td>{{ $movement->movement_time }}</td>
                <td>{{ $movement->type }}</td>
                <td>{{ $movement->operation_number }}</td>
                <td class="right">S/ {{ number_format($movement->amount, 2) }}</td>
                <td>{{ strtoupper((string) $movement->person?->typeofDocument) === 'RUC'
                    ? ($movement->person?->businessName ?? '')
                    : trim(implode(' ', array_filter([
                        $movement->person?->names,
                        $movement->person?->fatherSurname,
                        $movement->person?->motherSurname,
                    ]))) }}</td>
                <td>{{ $movement->concept }}</td>
                <td>{{ $movement->observation }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
