@php 
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Orden de Compra {{ $data->number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #2c3e50;
            margin: 20mm 15mm;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1a2a37;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 16px;
            font-weight: 600;
            color: #4a4a4a;
        }

        .hora {
            font-size: 11px;
            color: #7a7a7a;
            margin-top: 5px;
        }

        .section {
            margin-top: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 10px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }

        table th {
            background-color: #f4f6f8;
            font-weight: bold;
            color: #2c3e50;
        }

        .info-table {
            border: none;
        }

        .info-table td {
            border: none;
            padding: 6px 8px;
        }

        .info-table td:first-child {
            font-weight: bold;
            width: 160px;
        }

    </style>
</head>
<body>

    <div class="header">
        <div class="title">ORDEN DE COMPRA</div>
        <div class="subtitle">Número: {{ $data->number }}</div>
        <div class="hora">Fecha de Generación: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Información General</div>
        <table class="info-table">
            <tr>
                <td>Fecha de Movimiento:</td>
                <td>{{ Carbon::parse($data->date_movement)->format('d/m/Y') }}</td>
                <td><b>Sucursal:</b></td>
                <td>{{ $data->branchOffice->name ?? 'No disponible' }}</td>
            </tr>
            <tr>
                <td>RUC Proveedor:</td>
                <td>{{ $data?->proveedor?->documentNumber ?? 'No disponible' }}</td>
                <td><b>Proveedor:</b></td>
                <td>
                    @if ($data?->proveedor?->businessName)
                        {{ $data->proveedor->businessName }}
                    @else
                        {{ $data->proveedor->names ?? '' }} {{ $data->proveedor->fatherSurname ?? '' }} {{ $data->proveedor->motherSurname ?? '' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>Comentario:</td>
                <td colspan="3">{{ $data->comment ?? 'Ninguno' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalles de la Orden</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Repuesto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($data->details as $index => $detail)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $detail->repuesto->name ?? 'No especificado' }}</td>
                        <td style="text-align: right;">{{ $detail->quantity }}</td>
                        <td style="text-align: right;">S/ {{ number_format($detail->unit_price, 2) }}</td>
                        <td style="text-align: right;">S/ {{ number_format($detail->subtotal, 2) }}</td>
                    </tr>
                    @php $total += $detail->subtotal; @endphp
                @endforeach
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Total</td>
                    <td style="text-align: right; font-weight: bold;">S/ {{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
