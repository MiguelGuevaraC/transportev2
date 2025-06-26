<!DOCTYPE html>
<html lang="en">
@php
    use App\Models\CarrierGuide;
    header('Access-Control-Allow-Origin: https://transportes-hernandez-dev.vercel.app');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit();
    }
    use Carbon\Carbon;

    // Aqu� contin�a tu l�gica para procesar la solicitud y generar la respuesta
    // por ejemplo:
    $data1 = ['mensaje' => 'Solicitud permitida por CORS'];
    echo json_encode($data1);
    $sumaPeso = 0;

    function namePerson($person)
    {
        if ($person == null) {
            return '-'; // Si $person es nulo, retornamos un valor predeterminado
        }

        $typeD = $person->typeofDocument ?? 'dni';
        $cadena = '';

        if (strtolower($typeD) === 'ruc') {
            $cadena = $person->businessName;
        } else {
            $cadena = $person->names . ' ' . $person->fatherSurname . ' ' . $person->motherSurname;
        }

        // Corregido el operador ternario y la concatenación
        return $cadena . ' ' . ($person->documentNumber == null ? '?' : $typeD . ' ' . $person->documentNumber);
    }
    $conductorName = '-';
    foreach ($object['workers'] as $worker) {
        if ($worker['occupation'] === 'Conductor') {
            $conductorName = namePerson($worker->person);
            break;
        }
    }

// Variables por defecto (no tercerizado)
    $driverName = $conductorName;
    $vehiclePlate = str_replace([' ', '-'], '', strtoupper(data_get($object, 'tract.currentPlate', '')));
    $companyName = '-';
    $monto = count($object['carrierGuides']);
    $isIgv = '-';

    // Si es tercerizado, sobrescribimos los valores
    if (!empty($object['is_tercerizar_programming']) && $object['is_tercerizar_programming'] == 1) {
        $dataTercerizada = $object['data_tercerizar_programming'] ?? null;

        if (is_string($dataTercerizada)) {
            $dataTercerizada = json_decode($dataTercerizada, true);
        }

        $driverName = $dataTercerizada['driver_names'] ?? $driverName;
        $vehiclePlate = $dataTercerizada['vehicle_plate'] ?? $vehiclePlate;
        $companyName = $dataTercerizada['company_names'] ?? '-';
        $monto = $dataTercerizada['monto'] ?? null;
        $isIgv = isset($dataTercerizada['is_igv']) ? ($dataTercerizada['is_igv'] ? 'Sí' : 'No') : '-';
    }
@endphp

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reporte Transporte</title>
    <link rel="stylesheet" href="estilos.css" />

    <style>
        body {
            padding: 1px;
            width: 98%;
            height: 98%;
            margin: 1px;
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        .font-12 {
            font-size: 12px;
            text-align: left
        }

        .font-14 {
            font-size: 14px;
        }

        .dataInfo {
            margin-top: 14px;
            width: 100%;
            border-collapse: separate;
            border-collapse: collapse;
        }


        .dataInfo td,
        .dataInfo th {
            padding: 5px 0px;

        }

        body {
            font-family: "Inter", sans-serif;
        }

        .container {
            width: 100%;
            padding-left: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
        }

        .bg-color {
            background-color: rgb(243, 242, 240);
            width: 92%;
            text-align: center;
            padding: 0.8rem;
        }

        .border {
            border-top: 1px solid;
            border-bottom: 1px solid;
            border-color: rgb(179, 112, 30);
        }


        .header {
            align-items: left;
            justify-content: left;
            position: relative;
        }

        .header img {
            position: absolute;
            left: 0;
        }

        .pt {
            padding-top: 1.5rem;
        }


        .conBordetd {
            border: 1px solid rgb(179, 112, 30);
            padding: 1px;
            text-align: center;
        }

        .conBordetd.total {
            color: white;
            background: #b4583c;
        }


        table {
            border-collapse: collapse;
            width: 95%;
        }

        .tablaGrt .conBorde th,
        .tablaGrt .conBorde td {
            border: 1px solid rgb(179, 112, 30);
            padding: 1px;
            text-align: center;
            font-size: 10px;
        }

        .dataInfo td {
            text-align: center
        }


        .tdInfo {
            padding: 8px;
        }

        .section {
            display: table-row-group;

        }

        th {
            background-color: #f2f2f2;
            font-size: 0.8rem;
            color: rgb(179, 112, 30);
            font-weight: 1000;

        }

        td {
            font-size: 0.7rem;

        }

        .right {
            text-align: right;

        }

        .left {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="bg-color">
            <div class="border">
                <div class="header">
                    <img src="{{ asset('storage/img/transportes.bmp') }}" width="130px" alt="alt" />
                    <h3 class="pt">{{ $titulo }}</h3>
                    <p class="" style="font-size: 11px">Fecha Impresión:
                        {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

                </div>
                <table class="dataInfo">
                    <tbody class="section">
                        <tr>
                            <td class="font-12 tdInfo"><b>CÓDIGO VIAJE:</b></td>
                            <td class="font-12 left">{{ $object['numero'] }}</td>

                            <td class="font-12 tdInfo"><b>TRACTO:</b></td>
                            <td class="font-12 left">
                                {{ $vehiclePlate }}
                            </td>




                            <td class="font-12 tdInfo"><b>FECHA VIAJE:</b></td>
                            <td class="font-12 left">
                                {{ Carbon::parse($object['estimatedArrivalDate'])->format('d/m/Y H:i') }}</td>



                        </tr>
                    </tbody>
                    <tbody class="section">
                        <tr>
                            <td class="font-12 tdInfo"><b>CONDUCTOR:</b></td>
                            <td class="font-12 left">{{ $conductorName }}</td>

                            <td class="font-12 tdInfo"><b>PLATAFORMA:</b></td>
                            <td class="font-12 left">
                                {{ str_replace([' ', '-'], '', strtoupper(data_get($object, 'platform.currentPlate', ''))) }}
                            </td>


                            <td class="font-12 tdInfo"><b>TOTAL GRT:</b></td>
                            <td class="font-12 left"{{>$monto}}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>


        <br>
        <div>
            <table class='tablaGrt'>
                <thead>
                    <tr>
                        <td class="left" colspan="8">
                            <img src="{{ asset('storage/img/pin-mapa.png') }}" width="15px" alt="minimap" />
                            {{ $object['origin']['name'] . ' - ' . $object['destination']['name'] }}
                        </td>
                        <td class="right" colspan="4">
                            <strong>VENDEDOR:</strong> Transporte Hernandez
                        </td>
                    </tr>
                    <tr class="conBorde">
                        <th>N°</th>
                        <th>C. MATERIAL</th>
                        <th>REMIT.</th>
                        <th>DESTI.</th>
                        <th>PUNTO PARTIDA</th>
                        <th>PUNTO LLEGADA</th>
                        <th>DOC. ANEXOS</th>
                        <th>GUÍA GRT</th>
                        <th>TOTAL PESO</th>

                        <th>TOTAL FLETE</th>

                        <th>SALDO</th>
                        <th>COND. PAGO</th>
                        <th>ESTADO ENTREGA</th>
                    </tr>
                </thead>
                <?php 
                $object['carrierGuides']= $object['carrierGuides']
                                ->filter(function ($item) {
                                    return $item->status_facturado !== 'Anulada';
                                });
                ?>
                @if (count($object['carrierGuides']) > 0)
                    <tbody>
                        @php
                            $index = 1;
                            $sumFlete = 0;
                            $sumaPeso = 0;
                            $sumaSaldo = 0;

                            // Ordenar carrierGuides por punto de partida y luego por punto de llegada
                            $carrierGuidesSorted = $object['carrierGuides']
                                
                                ->sortBy(function ($item) {
                                    return [$item->reception->origin->name, $item->reception->destination->name];
                                });

                        @endphp
                        @foreach ($carrierGuidesSorted as $item)
                            {{-- Usa la colección ordenada --}}
                            @php
                                $guiagrt = CarrierGuide::find($item->id);
                                $reception = $item->reception ?? null;
                                $details = $reception ? $reception->details()->pluck('description')->toArray() : [];
                                $flete = 0;

                                if ($reception) {
                                    $flete =
                                        $reception->conditionPay == 'Credito'
                                            ? $reception->creditAmount ?? 0
                                            : $reception->paymentAmount ?? 0;

                                    if ($flete != -1) {
                                        $sumFlete += $flete;
                                        $sumaSaldo += $reception->debtAmount;
                                        $sumaPeso += $reception->netWeight ?? 0;
                                    } else {
                                        $flete = '';
                                        $sumaPeso += $reception->netWeight ?? 0;
                                    }
                                }
                            @endphp
                            <tr class="conBorde">
                                <td width="1%">{{ $index++ }}</td>
                                <td class="col-detalles" style="max-width: 400px; word-break: break-word; font-size: 10px;text-align:center">
                                    <span title="{{ $details ? implode(', ', $details) : ($titulo === 'MANIFIESTO DE CARGA CONDUCTOR' ? 'Manifiesto sin Detalles' : 'Sin detalles') }}">
                                        {{ $details ? implode(', ', $details) : ($titulo === 'MANIFIESTO DE CARGA CONDUCTOR' ? 'Manifiesto sin Detalles' : 'Sin detalles') }}
                                    </span>
                                </td>
                                
                                
                                <td width="10%" style="text-align: center">
                                    {{ $guiagrt ? namePerson($guiagrt?->sender) : '-' }}</td>
                                <td width="15%" style="text-align: center">
                                    {{ $guiagrt ? namePerson($guiagrt?->recipient) : '-' }}</td>
                                <td width="5%">{{ $guiagrt ? $guiagrt?->origin?->name : '-' }}</td>
                                <td width="5%">{{ $guiagrt ? $guiagrt?->destination?->name : '-' }}</td>
                                <td width="15%">{{ $guiagrt->document ?? '-' }}</td>
                                <td width="10%">{{ $guiagrt?->numero ?? '-' }}</td>
                                <td width="5%">{{ $reception->netWeight ?? 0 }}</td>
                                @if ($titulo !== 'MANIFIESTO DE CARGA CONDUCTOR')
                                    <td width="5%">{{ $flete }}</td>
                                @else
                                    <td width="5%">{{ $flete }}</td>
                                @endif
                                <td width="5%">{{ $reception->debtAmount == -1 ? '' : $reception->debtAmount }}
                                </td>
                                <td width="5%">{{ $reception->conditionPay ?? '-' }}</td>
                                <td width="5%">{{ $item->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7"></td>
                            <td class="conBordetd total">TOTAL</td>
                            <td class="conBordetd">{{ $sumaPeso }}</td>
                            {{-- @if ($titulo !== 'MANIFIESTO DE CARGA CONDUCTOR') --}}
                            <td class="conBordetd">{{ $sumFlete }}</td>
                            {{-- @endif --}}
                            <td class="conBordetd">{{ $sumaSaldo }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @else
                    <tbody>
                        <tr>
                            <td colspan="12" class="text-center" style="font-size: small; color: gray;">
                                No hay detalles en este manifiesto
                            </td>
                        </tr>
                    </tbody>
                @endif
            </table>








        </div>

    </div>
</body>

</html>
