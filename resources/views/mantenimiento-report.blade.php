@php
use Carbon\Carbon;

$productos = $mantenimiento->details->filter(fn($item) => $item->type === 'PRODUCTO');
$servicios = $mantenimiento->details->filter(fn($item) => $item->type === 'SERVICIO');

$total_productos = $productos->sum('price_total');
$total_servicios = $servicios->sum('price_total');
$total_general = $total_productos + $total_servicios;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Detallado de Mantenimiento</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 20mm 15mm; color: #2c3e50; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; color: #34495e; margin-bottom: 4px; }
        .subtitle { font-size: 13px; color: #7f8c8d; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f4f6f8; }
        h4 { margin-bottom: 6px; color: #2c3e50; margin-top: 25px; font-size: 15px; }
        .section-text { margin-bottom: 15px; line-height: 1.5; text-align: justify; }
        .signature { margin-top: 60px; text-align: right; font-size: 12px; }
        .total { text-align: right; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">INFORME DETALLADO DE MANTENIMIENTO VEHICULAR</div>
        <div class="subtitle">Generado el {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="section-text">
        El presente informe detalla las actividades de mantenimiento realizadas al vehículo con placa 
        <strong>{{ $mantenimiento?->vehicle?->currentPlate ?? '' }}</strong>, efectuadas en el taller 
        <strong>{{ $mantenimiento?->taller?->name ?? '' }}</strong>.
    </div>

    <h4>1. Información General del Mantenimiento</h4>
    <table>
        <tbody>
            <tr>
                <th>Tipo</th>
                <td>{{ $mantenimiento->type }}</td>
                <th>Modo</th>
                <td>{{ $mantenimiento->mode }}</td>
            </tr>
            <tr>
                <th>Kilometraje</th>
                <td>{{ $mantenimiento->km }}</td>
                <th>Estado</th>
                <td>{{ $mantenimiento->status }}</td>
            </tr>
            <tr>
                <th>Fecha de Inicio</th>
                <td>
                    @if($mantenimiento->date_maintenance)
                        {{ Carbon::parse($mantenimiento->date_maintenance)->format('d/m/Y H:i') }}
                    @endif
                </td>
                <th>Fecha de Finalización</th>
                <td>
                    @if($mantenimiento->date_end)
                        {{ Carbon::parse($mantenimiento->date_end)->format('d/m/Y H:i') }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <h4>2. Servicios Realizados</h4>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción del Servicio</th>
                <th>Cantidad</th>
                <th>Subtotal (S/)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servicios as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detalle->name }}</td>
                    <td>{{ $detalle->quantity }} x S/ {{ number_format($detalle->price, 2, ',', '.') }}</td>
                    <td>S/ {{ number_format($detalle->price_total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">No se registraron servicios.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4>3. Productos Utilizados</h4>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción del Producto</th>
                <th>Cantidad</th>
                <th>Subtotal (S/)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detalle->name }}</td>
                    <td>{{ $detalle->quantity }} x S/ {{ number_format($detalle->price, 2, ',', '.') }}</td>
                    <td>S/ {{ number_format($detalle->price_total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">No se registraron productos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4>4. Resumen de Costos</h4>
    <table>
        <tbody>
            <tr>
                <th>Total Servicios</th>
                <td>S/ {{ number_format($total_servicios, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total Productos</th>
                <td>S/ {{ number_format($total_productos, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total General</th>
                <td><strong>S/ {{ number_format($total_general, 2, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        ___________________________<br>
        <strong>Responsable del Taller</strong>
    </div>

</body>
</html>
