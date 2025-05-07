@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe Detallado de Revisión - Check List {{ $data->numero }}</title>
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
            font-size: 26px;
            font-weight: bold;
            color: #1a2a37;
            margin-bottom: 10px;
            text-transform: uppercase;
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
            margin-top: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 15px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
            color: #2c3e50;
            font-weight: 600;
        }

        .info-table {
            border: none;
        }

        .info-table td {
            border: none;
            padding: 6px 8px;
        }

        .info-table tr td:first-child {
            font-weight: bold;
            color: #2c3e50;
            width: 150px;
        }

        .tr-selected {
            background-color: #eafaf1; /* verde claro */
        }

        .tr-unselected {
            background-color: #fdfdfd; /* fondo blanco */
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">Informe Detallado de Revisión: Check List</div>
        <div class="subtitle">Número de Informe: {{ $data->numero }}</div>
        <div class="hora">Fecha de Generación: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="section">
        <table class="info-table">
            <tr>
                <td>Fecha de Revisión:</td>
                <td>{{ Carbon::parse($data->date_check_list)->format('d/m/Y') }}</td>
                <td>Hora de Revisión:</td>
                <td>{{ Carbon::parse($data->date_check_list)->format('H:i') }}</td>
            </tr>
            <tr>
                <td>Vehículo (Placa):</td>
                <td>{{ $data->vehicle->currentPlate ?? 'No disponible' }}</td>
                <td>Estado del Informe:</td>
                <td>{{ $data->status }}</td>
            </tr>
            <tr>
                <td>Observación General:</td>
                <td colspan="3">{{ $data->observation ?? 'Ninguna' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalles de la Revisión del Vehículo</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Ítem de Revisión</th>
                    <th style="width: 100px;">Estado</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->checkListItems as $index => $item)
                    <tr class="{{ $item->pivot->is_selected ? 'tr-selected' : 'tr-unselected' }}">
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td style="text-align: center;">
                            {{ $item->pivot->is_selected ? '✔ Aprobado' : '✘ No Aprobado' }}
                        </td>
                        <td>{{ $item->pivot->observation ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Resumen del Estado de Revisión</div>
        <p>El siguiente resumen indica el estado general del vehículo revisado según los ítems evaluados durante el check list. Cada ítem ha sido verificado bajo los criterios establecidos para garantizar que el vehículo se encuentra en condiciones óptimas para su operación.</p>
        <table>
            <thead>
                <tr>
                    <th>Total de Ítems Evaluados</th>
                    <th>Total Aprobados</th>
                    <th>Total No Aprobados</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ count($data->checkListItems) }}</td>
                    <td>{{ $data->checkListItems->where('pivot.is_selected', true)->count() }}</td>
                    <td>{{ $data->checkListItems->where('pivot.is_selected', false)->count() }}</td>
                </tr>
            </tbody>
        </table>
    </div>

  

</body>

</html>
