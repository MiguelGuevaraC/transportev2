@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Check List {{ $data->numero }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #2c3e50;
            margin: 20mm 15mm;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 60px;
            margin: 0 10px;
        }

        .title {
            font-size: 23px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
            color: #34495e;
        }

        .subtitle {
            font-size: 13px;
            margin-bottom: 10px;
            color: #555;
            font-weight: bold;
        }

        .section {
            margin-top: 20px;
        }

        .section strong {
            display: inline-block;
            width: 140px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 11px;
        }

        table th,
        table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        table th {
            background-color: #f4f6f8;
            color: #2c3e50;
        }

        .content {
            margin-top: 20px;
            padding-left: 30px;
            padding-right: 30px;
        }

        .hora {
            font-size: 8px;
        }

        .contentImage {
            width: 100%;
            text-align: right;
        }

        .logoImage {
            width: auto;
            height: 90px;
        }

        /* Estilos para filas seleccionadas y no seleccionadas */
        .tr-selected {
            background-color: #e3f8e6; /* verde suave */
        }

        .tr-unselected {
            background-color: #ffffff; /* rojo suave */
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">Reporte de Check List</div>
        <div class="subtitle">N° {{ $data->numero }}</div>
        <div class="hora"> Generado el {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="section">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{ Carbon::parse($data->date_check_list)->format('d/m/Y') }}</td>
                <td><strong>Hora:</strong></td>
                <td>{{ Carbon::parse($data->date_check_list)->format('H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Vehículo (Placa):</strong></td>
                <td>{{ $data->vehicle->currentPlate ?? 'No disponible' }}</td>
                <td><strong>Estado:</strong></td>
                <td>{{ $data->status }}</td>
            </tr>
            <tr>
                <td><strong>Observación General:</strong></td>
                <td colspan="3">{{ $data->observation ?? 'Ninguna' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h4 style="margin-bottom: 5px; color: #34495e;">Detalles Revisión</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Ítem</th>
                    <th style="width: 90px;">Estado</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->checkListItems as $index => $item)
                    <tr class="{{ $item->pivot->is_selected ? 'tr-selected' : 'tr-unselected' }}">
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td style="text-align: center;">
                            {{ $item->pivot->is_selected ? '✔ Sí' : '✘ No' }}
                        </td>
                        <td>{{ $item->pivot->observation ?? 'Sin observación' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>

</html>
