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
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;

            color: #2c3e50;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 13px;
            color: #7f8c8d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f4f6f8;
        }

        h4 {
            margin-bottom: 6px;
            color: #2c3e50;
            margin-top: 25px;
            font-size: 15px;
        }

        .section-text {
            margin-bottom: 15px;
            line-height: 1.5;
            text-align: justify;
        }

        .signature {
            margin-top: 60px;
            text-align: right;
            font-size: 12px;
        }

        .total {
            text-align: right;
            font-weight: bold;
        }

        .tableInfo {
            margin-top: 5px;
        }

        .contentImage {
            width: 100%;
            text-align: right;
        }

        .logoImage {
            width: auto;
            height: 90px;
        }

        .titlePresupuesto {

            font-size: 15px;
            font-weight: bolder;
            text-align: justify;
            /*margin-top: 20px;*/
            /*margin-bottom: 20px;*/
            color: rgb(126, 0, 0);
            ;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .tableInfo {
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <table class="tableInfo">
        <tr>
            <div class="contentImage">
                <img class="logoImage" src="{{$logoBase64}}" alt="logoTransporte">
            </div>



            <td class="center">
                <div style="border: 1px solid black; padding: 10px; display: inline-block; text-align: center;">
                    <div class="titlePresupuesto">MANTENIMIENTO</div>
                    <div class="numberPresupuesto" style="font-weight: bolder;">{{ $mantenimiento->mode }}</div>
                </div>
            </td>



        </tr>

    </table>

{{-- Sección introductoria --}}
<section style="margin-bottom: 25px; font-size: 14px;">
    <p>
        El presente informe detalla las actividades de mantenimiento realizadas al vehículo con placa
        <strong>{{ $mantenimiento?->vehicle?->currentPlate ?? '—' }}</strong>,
        efectuadas en el taller
        <strong>{{ $mantenimiento?->taller?->name ?? '—' }}</strong>.
    </p>
</section>

{{-- Sección 1: Información General del Mantenimiento --}}
<section style="margin-bottom: 30px;">
    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #ccc;">1. Información General del Mantenimiento</h4>
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr>
                <th style="text-align: left; background: #f0f0f0; padding: 8px;">Tipo</th>
                <td style="padding: 8px;">{{ $mantenimiento->type }}</td>
                <th style="text-align: left; background: #f0f0f0; padding: 8px;">Modo</th>
                <td style="padding: 8px;">{{ $mantenimiento->mode }}</td>
            </tr>
            <tr>
                <th style="text-align: left; background: #f0f0f0; padding: 8px;">Kilometraje</th>
                <td style="padding: 8px;">{{ $mantenimiento->km }}</td>
                <th style="text-align: left; background: #f0f0f0; padding: 8px;">Estado</th>
                <td style="padding: 8px;">{{ $mantenimiento->status }}</td>
            </tr>
          <tr>
    <th style="text-align: left; background: #f0f0f0; padding: 8px;">Fecha de Inicio</th>
    <td style="padding: 8px;">{{ $mantenimiento->date_maintenance ? \Carbon\Carbon::parse($mantenimiento->date_maintenance)->format('d/m/Y H:i') : '—' }}</td>
    <th style="text-align: left; background: #f0f0f0; padding: 8px;">Fecha de Finalización</th>
    <td style="padding: 8px;">{{ $mantenimiento->date_end ? \Carbon\Carbon::parse($mantenimiento->date_end)->format('d/m/Y H:i') : '—' }}</td>
</tr>

        </tbody>
    </table>
</section>

{{-- Sección 2: Servicios Realizados --}}
<section style="margin-bottom: 30px;">
    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #ccc;">2. Servicios Realizados</h4>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 8px; border: 1px solid #ccc;">#</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Descripción</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Cantidad</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Subtotal (S/)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($servicios as $index => $detalle)
                <tr>
                    <td style="padding: 8px; border: 1px solid #ccc;">{{ $index + 1 }}</td>
                    <td style="padding: 8px; border: 1px solid #ccc;">{{ $detalle->name }}</td>
                    <td style="padding: 8px; border: 1px solid #ccc;">
                        {{ $detalle->quantity }} x S/ {{ number_format($detalle->price, 2, ',', '.') }}
                    </td>
                    <td style="padding: 8px; border: 1px solid #ccc;">
                        S/ {{ number_format($detalle->price_total, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 10px;">No se registraron servicios.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

{{-- Sección 3: Productos Utilizados --}}
<section style="margin-bottom: 30px;">
    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #ccc;">3. Productos Utilizados</h4>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 8px; border: 1px solid #ccc;">#</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Descripción</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Cantidad</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Subtotal (S/)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $index => $detalle)
                <tr>
                    <td style="padding: 8px; border: 1px solid #ccc;">{{ $index + 1 }}</td>
                    <td style="padding: 8px; border: 1px solid #ccc;">{{ $detalle->name }}</td>
                    <td style="padding: 8px; border: 1px solid #ccc;">
                        {{ $detalle->quantity }} x S/ {{ number_format($detalle->price, 2, ',', '.') }}
                    </td>
                    <td style="padding: 8px; border: 1px solid #ccc;">
                        S/ {{ number_format($detalle->price_total, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 10px;">No se registraron productos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

{{-- Sección 4: Resumen de Costos --}}
<section style="margin-bottom: 40px;">
    <h4 style="margin-bottom: 10px; border-bottom: 1px solid #ccc;">4. Resumen de Costos</h4>
    <table style="width: 50%; border-collapse: collapse;">
        <tbody>
            <tr>
                <th style="text-align: left; background: #f0f0f0; padding: 8px;">Total Servicios</th>
                <td style="padding: 8px;">S/ {{ number_format($total_servicios, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th style="text-align: left; background: #f0f0f0; padding: 8px;">Total Productos</th>
                <td style="padding: 8px;">S/ {{ number_format($total_productos, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th style="text-align: left; background: #e8e8e8; padding: 8px;">Total General</th>
                <td style="padding: 8px;"><strong>S/ {{ number_format($total_general, 2, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
</section>

{{-- Firma --}}
<section style="text-align: center; margin-top: 60px;">
    ___________________________<br>
    <strong>Responsable del Taller</strong>
</section>



</body>

</html>