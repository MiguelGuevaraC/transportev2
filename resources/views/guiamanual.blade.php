@php
use Carbon\Carbon;

        header('Access-Control-Allow-Origin: https://transportes-hernandez-dev.vercel.app');
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
    <title>CUADRE CAJA</title>
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
            margin-top: 35px;
            margin-left: 25px;
            margin-bottom: 10px;
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
            font-size: 30px;
            font-weight: bolder;
            text-align: center;
            color: #000000;
        }

        .black {
            color: #000000;
        }

        table {
            width: 100%;

            font-size: 14px;

        }



        .dataInfo {
            width: 100%;
            border-collapse: separate;

        }

        .dataInfo td,
        .dataInfo th {
            padding: 1px 8px;
            text-align: left;

        }

        .borderBlack {
            border: 2px solid black;
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
            padding: 2px;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .font-10 {
            font-size: 10px;
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

        .pl-50 {
            padding: 0px 50px;
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

        .dataTable {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            margin: 20px 0;

        }


        .dataTable th {
            background: #000000;
            color: white;
            font-weight: bold;
            border: 1px solid black;
        }

        .dataTable td {
            font-size: 14px;
            border: 1px solid black;
        }

        .dataTable tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .dataTable tr:hover {
            background-color: #f1f1f1;
        }

        .write-space {

            width: 100%;
            display: inline-block;
            height: 20px;
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


        <table class="dataInfo">
            <tbody class="section">
                <tr>
                    <td class="font-14 tdInfo">Ruta</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">F.Viaje</td>
                    <td class="font-12">__________________________</td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="font-14 tdInfo">Piloto</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">F.LLegada</td>
                    <td class="font-12">__________________________</td>

                </tr>
            </tbody>
            <tbody class="section">
                <tr>

                    <td class="font-14 tdInfo">Copiloto</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">Bolsa</td>
                    <td class="font-12">__________________________</td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="font-14 tdInfo">Km Inici</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">Vueltos</td>
                    <td class="font-12">__________________________</td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="font-14 tdInfo">Km Fin</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">Gastos</td>
                    <td class="font-12">__________________________</td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="font-14 tdInfo">Tracto</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">Diferencia</td>
                    <td class="font-12">__________________________</td>
                </tr>
            </tbody>
            <tbody class="section">
                <tr>
                    <td class="font-14 tdInfo">Carreta</td>
                    <td class="font-12">__________________________</td>

                    <td class="font-14"></td>
                    <td class="font-12"></td>

                    <td class="font-14 tdInfo">Observación</td>
                    <td class="font-12">__________________________</td>
                </tr>
            </tbody>
        </table>


        <table class="dataTable">
            <thead>
                <tr>
                    <th class="font-10">DATOS</th>
                    <th class="font-10">LUGAR</th>
                    <th class="font-10">KM</th>
                    <th class="font-10">FACT/RECIBO</th>
                    <th class="font-10">GALONES</th>
                    <th class="font-10">MONTO</th>
                </tr>
            </thead>
            <tbody class="section">
                <td class="font-10">Ultimo Tanqueo</td>
                <td class="pl-50"></td>
                <td style="padding:0px 20px"></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="font-10">Relleno de Ruta</td>
                <td class="pl-50"></td>
                <td style="padding:0px 20px"></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="font-10">Úlitmo Tanqueo</td>
                <td class="pl-50"></td>
                <td style="padding:0px 20px"></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>



        </table>


        <table class="dataTable">
            <thead>
                <tr>
                    <th class="font-10">N°</th>
                    <th class="font-10">FECHA</th>
                    <th class="font-10">G/T/T</th>
                    <th class="font-10">PESO</th>
                    <th class="font-10">CANT</th>
                    <th class="font-10">PRODUCTO</th>
                    <th class="font-10">REMITENTE</th>
                    <th class="font-10">DESTINATARIO</th>
                    <th class="font-10">PRECIO</th>
                </tr>
            </thead>
            <tbody class="section">
                <td class="right">1</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="right">2</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="right">3</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="right">4</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="right">5</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>
            <tbody class="section">
                <td class="right">6</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tbody>


        </table>


        <table class="dataTable">
            <thead>
                <tr>
                    <th class="font-10">G/RUTA</th>
                    <th class="font-10">CANT</th>
                    <th class="font-10">MONTO</th>
                </tr>
            </thead>
            <tbody class="section">
                <?php
                $categories = ['Peajes', 'Estiba', 'Desestiva', 'Viaticos - Alimentacion', 'Viaticos - Vicios', 'Balanza', 'Rep. Llanta', 'Hospedaje', 'Movilidad', 'Cochera', 'Lavado', 'Guardiania', 'Petroleo', 'Comosión', 'Otros'];
                
                foreach ($categories as $category) {
                    echo '<tr>';
                    echo "<td class='font-12'>$category</td>";
                    echo "<td class='font-12'><span class='write-space'></span></td>";
                    echo "<td class='font-12'><span class='write-space'></span></td>";
                    echo '</tr>';
                }
                
                // Add total row
                echo '<tr>';
                echo "<td class='font-12 bolder'>Total</td>";
                echo "<td class='font-12'><span class='write-space'></span></td>";
                echo "<td class='font-12'><span class='write-space'></span></td>";
                echo '</tr>';
                ?>
            </tbody>
        </table>





    </div>


    {{-- <img class="footerImage" src="{{ asset('storage/img/curvBotton.png') }}" alt="degraded"> --}}
</body>

</html>
