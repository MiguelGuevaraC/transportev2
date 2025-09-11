@php
    use Carbon\Carbon;
    header('Access-Control-Allow-Origin: https://transportes-hernandez-mrsoft.vercel.app/');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit();
    }

    // Aqu� contin�a tu l�gica para procesar la solicitud y generar la respuesta
    // por ejemplo:
    $data1 = ['mensaje' => 'Solicitud permitida por CORS'];
    echo json_encode($data1);
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUIA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'poppins', sans-serif;
            letter-spacing: 0.5px;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        .hr-delgada {
            border: none;
            border-top: 1px solid #cccccc;
            /* Color y grosor de la línea */
            margin: 10px 0;
            /* Margen arriba y abajo para separación */
        }

        body {
            padding-top: 60px;
            padding-bottom: 30px;
        }

        .headerImage {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        .footerImage {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        .content {
            padding-left: 90px;
            padding-right: 90px;
        }

        .contentImage {
            margin-top: 45px;
            margin-left: 25px;
            width: 100%;
            text-align: center;
        }

        .logoImage {
            width: 150px;
        }

        .locationImage {
            vertical-align: middle;
            margin-right: 10px;
            width: 15px
        }

        .title {
            font-size: 40px;
            font-weight: bolder;
            text-align: center;
            color: #000000;
        }

        .black {
            color: #000000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }


        .places {
            margin-top: 40px;
        }

        .dataInfo {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 60px;
        }

        .dataInfo td,
        .dataInfo th {

            padding: 10px;
            /* Ajustar el padding según sea necesario */
            text-align: left;
        }

        .dataInfo .tdInfo {

            background-color: black;
            color: white;
        }

        .dataInfo .font-12 {
            font-size: 12px;
        }

        .section {
            display: table-row-group;
            /* Agrupar elementos en secciones */
        }




        .p10 {
            padding: 10px;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .font-12 {
            font-size: 12px;
        }

        .font-14 {
            font-size: 14px;
        }

        .font-16 {
            font-size: 16px;
        }


        .bolder {
            font-weight: bolder;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table.places,
        table.dataInfo {
            text-align: center
        }


        .w50 {
            width: 50%;
        }

        .w40 {
            width: 40%;
        }

        .w20 {
            width: 20%;
        }





        .borderTop {
            border-top: 1px solid #3b3b3b;
        }
    </style>
</head>

<body>

    {{-- <img class="headerImage" src="{{ asset('storage/img/curvTop.png') }}" alt="degraded"> --}}
    <div class="content">
        <div class="contentImage">
            <img class="logoImage" src="{{ asset('storage/img/logo.png') }}" alt="logoTransporte">
        </div>
        <table class="tableInfo">
            <tr>
                <td class="left">

                </td>
                <td>
                    <div class="title right">GUÍA</div>
                </td>
            </tr>
        </table>


        <table class="places">
            <tr>
                <td class="bolder font-14"><img class="locationImage" src="{{ asset('storage/img/location.png') }}"
                        alt="location">PUNTO PARTIDA</td>
                <td class="bolder font-14"><img class="locationImage" src="{{ asset('storage/img/location.png') }}"
                        alt="location">PUNTO LLEGADA</td>
            </tr>
            <tr>
                <td>
                    <div class="place-info">

                        <span class="font-12">{{ $object->origin->name ?? '-' }}</span>
                    </div>
                </td>
                <td>

                    <span class="font-12">{{ $object->destination->name ?? '-' }}</span>
                </td>
            </tr>
        </table>


        <table class="dataInfo">
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Remitente</td>
                    <td class="font-12">
                        @if ($object->sender->typeofDocument == 'ruc')
                            {{ $object->sender->businessName }}
                        @else
                            {{ $object->sender->nombres . ' ' . $object->sender->fatherSurname }}
                        @endif
                    </td>

                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Destinatario</td>
                    <td class="font-12">
                        @if ($object->recipient->typeofDocument == 'ruc')
                            {{ $object->recipient->businessName }}
                        @else
                            {{ $object->recipient->nombres . ' ' . $object->recipient->fatherSurname }}
                        @endif
                    </td>
                    <td class="tdInfo font-14">Telefono</td>
                    <td class="font-12">{{ $object->recipient->telephone ?? '-' }}</td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Cliente</td>
                    <td class="font-12">
                        @if ($object->payResponsible->typeofDocument == 'ruc')
                            {{ $object->payResponsible->businessName }}
                        @else
                            {{ $object->payResponsible->nombres . ' ' . $object->payResponsible->fatherSurname }}
                        @endif
                    </td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Documento</td>
                    <td class="font-12">{{ $object->document ?? '-' }}</td>
                    <td class="tdInfo font-14">Subcontrata</td>
                    <td class="font-12"> {{ $object->subcontract->name ?? '-' }}</td>


                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Fecha Inicio</td>
                    @if (!empty($object->transferDateEstimated))
                        <td class="font-12">
                            {{ \Carbon\Carbon::parse($object->transferStartDate)->format('d-m-Y') }}</td>
                    @else
                        <td class="font-12"></td>
                    @endif
                    <td class="tdInfo font-14">Fecha Traslado</td>
                    @if (!empty($object->transferDateEstimated))
                        <td class="font-12">
                            {{ \Carbon\Carbon::parse($object->transferDateEstimated)->format('d-m-Y') }}</td>
                    @else
                        <td class="font-12"></td>
                    @endif

                </tr>
            </tbody>
        </table>
        <br>
        <hr class="hr-delgada">

        <table class="places">
            <tbody class="section">
                <tr>
                    <td class="left  font-14"><b>CONDUCTOR:</b>
                        {{ $object->driver ? $object->driver->person->nombres . ' ' . $object->driver->person->fatherSurname : '-' }}
                    </td>
                    </td>

                    <td class="left font-14">
                        <b>COPILOTO:</b>{{ $object->copilot ? $object->copilot->person->nombres . ' ' . $object->copilot->person->fatherSurname : '-' }}
                    </td>


                </tr>
            </tbody>
        </table>


        <table class="dataInfo">
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Vehículo</td>
                    <td class="font-12">{{ $object->tract->currentPlate ?? '-' }}</td>
                    <td class="tdInfo font-14">N° MTC</td>
                    <td class="font-12">
                        {{ $object->tract ? $object->tract->numberMtc : '' }}
                    </td>

                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="tdInfo font-14">Carreta</td>
                    <td class="font-12">{{ $object->platform->currentPlate ?? '-' }}</td>

                    <td class="tdInfo font-14">N° MTC C</td>
                    <td class="font-12">{{ $object->platform->numberMtc ?? '-' }}</td>
                </tr>
            </tbody>


        </table>


    </div>


    {{-- <img class="footerImage" src="{{ asset('storage/img/curvBotton.png') }}" alt="degraded"> --}}
</body>

</html>
