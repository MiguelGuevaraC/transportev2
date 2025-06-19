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
            background-color: #eafaf1;
            /* verde claro */
        }

        .tr-unselected {
            background-color: #fdfdfd;
            /* fondo blanco */
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
                    <div class="titlePresupuesto">CHECK LIST</div>
                    <div class="numberPresupuesto" style="font-weight: bolder;">{{ $data->numero }}</div>
                </div>
            </td>



        </tr>

    </table>



  <div class="section">
    <!-- INFORMACIÓN GENERAL -->
    <div class="section-title">Información General del Informe</div>
    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px;">
        <tbody>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 8px; font-weight: bold; width: 180px; border: 1px solid #ccc;">Fecha de Revisión:</td>
                <td style="padding: 8px; border: 1px solid #ccc;">{{ Carbon::parse($data->date_check_list)->format('d/m/Y') }}</td>
                <td style="padding: 8px; font-weight: bold; border: 1px solid #ccc;">Hora de Revisión:</td>
                <td style="padding: 8px; border: 1px solid #ccc;">{{ Carbon::parse($data->date_check_list)->format('H:i') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold; background-color: #f9f9f9; border: 1px solid #ccc;">Vehículo (Placa):</td>
                <td style="padding: 8px; border: 1px solid #ccc;">{{ $data->vehicle->currentPlate ?? 'No disponible' }}</td>
                <td style="padding: 8px; font-weight: bold; background-color: #f9f9f9; border: 1px solid #ccc;">Estado del Informe:</td>
                <td style="padding: 8px; border: 1px solid #ccc;">{{ $data->status }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; font-weight: bold; background-color: #f9f9f9; border: 1px solid #ccc;">Observación General:</td>
                <td colspan="3" style="padding: 8px; border: 1px solid #ccc; font-style: italic;">{{ $data->observation ?? 'Ninguna' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- DETALLES DE LA REVISIÓN -->
    <div class="section-title" style="margin-top: 35px;">Detalles de la Revisión del Vehículo</div>
    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px;">
        <thead>
            <tr style="background-color: #e5e8ea; color: #2c3e50;">
                <th style="padding: 8px; border: 1px solid #ccc; width: 30px;">#</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Ítem de Revisión</th>
                <th style="padding: 8px; border: 1px solid #ccc; width: 110px;">Estado</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->checkListItems as $index => $item)
                <tr style="background-color: {{ $index % 2 === 0 ? '#fdfdfd' : '#f5f8fa' }};">
                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">{{ $index + 1 }}</td>
                    <td style="padding: 8px; border: 1px solid #ccc;">{{ $item->name }}</td>
                    <td style="padding: 8px; border: 1px solid #ccc; text-align: center; font-weight: bold; color: {{ $item->pivot->is_selected ? '#2e7d32' : '#c62828' }};">
                        {{ $item->pivot->is_selected ? '✔ Aprobado' : '✘ No Aprobado' }}
                    </td>
                    <td style="padding: 8px; border: 1px solid #ccc;">{{ $item->pivot->observation ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- RESUMEN -->
    <div class="section-title" style="margin-top: 35px;">Resumen del Estado de Revisión</div>
    <p style="margin-bottom: 10px; font-size: 12px;">
        Este resumen refleja el estado general del vehículo revisado. Cada ítem fue verificado conforme a los criterios establecidos para su correcta operación.
    </p>
    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr style="background-color: #e5e8ea; color: #2c3e50;">
                <th style="padding: 8px; border: 1px solid #ccc;">Total de Ítems Evaluados</th>
                <th style="padding: 8px; border: 1px solid #ccc; color: #2e7d32;">Total Aprobados</th>
                <th style="padding: 8px; border: 1px solid #ccc; color: #c62828;">Total No Aprobados</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #fdfdfd;">
                <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">{{ count($data->checkListItems) }}</td>
                <td style="padding: 8px; border: 1px solid #ccc; text-align: center; font-weight: bold;">{{ $data->checkListItems->where('pivot.is_selected', true)->count() }}</td>
                <td style="padding: 8px; border: 1px solid #ccc; text-align: center; font-weight: bold;">{{ $data->checkListItems->where('pivot.is_selected', false)->count() }}</td>
            </tr>
        </tbody>
    </table>
</div>




</body>

</html>