@php
    use Carbon\Carbon;

    // Guardar el SVG como un archivo temporal

    // Eliminar el archivo temporal SVG

@endphp
<!DOCTYPE html>
<html lang="es">

@php
    function getArchivosDocument($idventa, $typeDocument)
    {
        $funcion = 'buscarNumeroSolicitud';
        $url =
            'https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=' .
            $funcion .
            '&typeDocument=' .
            $typeDocument;

        // Parámetros para la solicitud
        $params = http_build_query(['idventa' => $idventa]);

        // Inicializamos cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '&' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutamos la solicitud y obtenemos la respuesta
        $response = curl_exec($ch);

        // Cerramos cURL
        curl_close($ch);

        // Verificamos si la respuesta es válida
        if ($response !== false) {
            // Decodificamos la respuesta JSON
            $data = json_decode($response, true);

            // Verificamos si la respuesta contiene la información del archivo PNG
            if (isset($data['png'])) {
                $pngFile = $data['png'];

                // Aquí podrías agregar el código para mostrar la imagen en una etiqueta <img>
                echo '<img src="https://develop.garzasoft.com:81/transporteFacturadorZip/ficheros/' .
                    $pngFile .
                    '" alt="Imagen PNG">';
            } else {
                echo '-';
            }
        } else {
            echo 'Error en la solicitud.';
        }
    }
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCUMENTO DE PAGO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.5px;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        body {
            padding-top: 5px;
            padding-bottom: 30px;
        }

        td,
        th {
            padding: 2px;
        }

        .headerImage {
            position: absolute;
            top: -20;
            left: 0;
            width: 100%;
        }


        .footerImage {
            position: absolute;
            bottom: -20;
            left: 0;
            width: 100%;
        }

        .content {
            margin-top: 20px;
            padding-left: 30px;

            padding-right: 30px;
        }

        .contentImage {
            width: 100%;
            text-align: right;
        }

        .logoImage {
            width: auto;
            height: 90px;
        }

        .logoImageQr {
            width: auto;
            height: 100px;
            text-align: right
        }

        .containerQr {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .titlePresupuesto {

            font-size: 15px;
            font-weight: bolder;
            text-align: center;
            /*margin-top: 20px;*/
            /*margin-bottom: 20px;*/
            color: rgb(126, 0, 0);


        }

        .numberPresupuesto {
            font-size: 15px;

            text-align: center;
            /*margin-top: 20px;*/
            /*margin-bottom: 20px;*/
            color: black;
        }

        .blue {
            color: black;
        }

        .strong {
            font-weight: bolder;
        }

        .gris {
            color: #3b3b3b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .tableInfo {
            margin-top: 5px;
        }

        .tablePeople {
            margin-top: 30px;
            font-size: 16px;
            border: 1px solid rgb(0, 0, 0);

        }

        .tablePeople td,
        .tablePeople th {
            padding: 5px 7px
        }

        .tablePeople th {
            background-color: rgb(255, 255, 255);
            color: rgb(0, 0, 0);
            text-align: left;

        }

        .tableDetail {
            margin-top: 30px;
        }

        .detTabla {
            float: right;
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

        .margin20 {
            margin-top: 20px;
        }

        .bolder {
            font-weight: bolder;
        }

        .tablePeople td.left {
            padding: 2px;
        }

        .border {
            border: 1px solid black;
            padding: 5px;
            text-align: center
        }

        .tablePeople td.right {
            padding: 2px;
        }

        .tableDetail th {
            background-color: rgb(126, 0, 0);
            color: white;
            padding: 10px;
            font-weight: bolder;
        }

        .tableDetail td {
            border-bottom: 1px solid #3b3b3b;
        }

        .id {

            text-align: center;
        }

        .description {
            width: 50%;
        }

        .unit {
            width: 10%;
            text-align: left;
        }

        .quantity {
            width: 10%;
            text-align: left;
        }

        .unitPrice {
            width: 10%;
            text-align: right;
        }

        .sailPrice {
            width: 15%;
            text-align: right;
        }

        .sailTotal {
            width: 15%;
            text-align: right;
        }

        .tableTotal {
            margin-top: 30px;
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

        .totalInfo {
            border-collapse: collapse;
            font-size: 16px;
            background-color: #f2f2f2;
        }

        .observaciones {
            margin-top: 30px;
        }

        .listaObservaciones {
            padding-left: 20px;
            color: #3b3b3b;
        }

        .listaObservaciones li {
            margin-top: 2px;
            text-align: justify;
        }

        .tableFirmas {
            margin-top: 100px;
        }

        .borderTop {
            border-top: 1px solid #3b3b3b;
        }

        .text-sm {
            font-size: 9px;
        }

        .w40 {
            width: 40%;
        }

        .w25 {
            width: 25%;
        }

        .w10 {
            width: 10%;
        }

        .w30 {
            width: 30%;
        }

        .detTabla tr:last-child {
            border-top: 1px solid #000;
        }
    </style>
</head>

<body>

    {{-- <img class="headerImage" src="{{ asset('storage/img/curvTop.png') }}" alt="degraded"> --}}

    <div class="content">


        <table class="tableInfo">
            <tr>
                <div class="contentImage">
                    <img class="logoImage" src="{{ asset('storage/img/logoTransportes.jpeg') }}" alt="logoTransporte">
                </div>




                <td class="right">
                    <div style="border: 1px solid black; padding: 10px; display: inline-block; text-align: center;">
                        <div class="titlePresupuesto">NOTA DE CRÉDITO</div>
                        <div class="titlePresupuesto">ELECTRÓNICA</div>

                        <div class="numberPresupuesto">RUC:20605597484</div>
                        <div class="numberPresupuesto" style="font-weight: bolder;">{{ $numeroNotaCredito }}</div>

                    </div>
                </td>


            </tr>

        </table>

        <table>


            <tr>
                <td class="w10 blue left">
                    <b> OPERACIONES LOGISTICAS HERNANDEZ S.A.C.</b>
                </td>
            </tr>
            <tr>
                <td class="w10 blue left">
                    MZA. 38 LOTE. 4A INT. 302 P.J. CHOSICA DEL NORTE LAMBAYEQUE CHICLAYO LA VICTORIA
                </td>
            </tr>
        </table>

        <table class="tablePeople font-14">
            <tr>
                <th class="w10 blue">
                    Fecha Emision:
                </th>
                <td class="w50">
                    {{ $fechaemision }}
                </td>
            </tr>

            <tr>
                <th class="w20 blue">
                    Señor(es):
                </th>
                <td class="w20">
                    {{ $cliente }}
                </td>
            </tr>

            <tr>
                <th class="w20 blue">
                    RUC/DNI:
                </th>
                <td class="w20">
                    {{ $ruc_dni }}
                </td>
            </tr>

            <tr>
                <th class="w20 blue">
                    Direccion:
                </th>
                <td class="w20">
                    {{ $direccion }}
                </td>
            </tr>

            <tr>
                <th class="w20 blue">
                    Moneda:
                </th>
                <td class="w20">
                    PEN
                </td>
            </tr>

            @php
                // Obtener el nombre del motivo basado en el motive_id
                $motive = collect([
                    ['id' => 1, 'name' => 'Anulacion de la Operación'],
                    ['id' => 2, 'name' => 'Anulacion por error en el RUC'],
                    ['id' => 3, 'name' => 'Correción por error en la descripción'],
                    ['id' => 4, 'name' => 'Descuento global'],
                    ['id' => 5, 'name' => 'Descuento por item'],
                    ['id' => 6, 'name' => 'Devolución total'],
                    ['id' => 7, 'name' => 'Devolución por ítem'],
                    ['id' => 8, 'name' => 'Bonificación'],
                    ['id' => 9, 'name' => 'Disminución en el valor'],
                    ['id' => 13, 'name' => 'Ajustes Monto y Fecha'],
                    ['id' => 10, 'name' => 'Otros conceptos'],
                ])->firstWhere('id', $motive); // Buscamos el motivo por su ID
            @endphp

            <tr>
                <th class="w20 blue">
                    Motivo:
                </th>
                <td class="w20">
                    {{ $motive['name'] ?? 'Motivo no encontrado' }} <!-- Mostrar el nombre o un mensaje por defecto -->
                </td>
            </tr>

            <tr>
                <th class="w20 blue">
                    Nro Referencia:
                </th>
                <td class="w20">
                    {{ $nroReferencia }}
                </td>
            </tr>


        </table>



        <table class="tableDetail font-12">
            <tr>

                <th class="item">Item</th>
                <th class="quantity">Guia</th>
                <th class="unitPrice">Placa</th>
                <th class="sailPrice">OS</th>
                <th class="description">Descripción</th>
                <th class="sailPrice">UM</th>
                <th class="sailPrice">Cant.</th>
                <th class="sailPrice">V.U.</th>
                <th class="sailPrice">P.U.</th>
                <th class="sailPrice">Valor Venta</th>


            </tr>
            <?php
              $totalDetalle = $totalNota;
              $subtotal = $totalPagado;
            foreach ($detalles as $detHab) :
                // $subtotal = $detHab['precioventaunitarioxitem'] * $detHab['cantidad'];
                // $totalDetalle += $subtotal;
          
            
            ?>

            <tr>
                <td class="center"><?php echo 1; ?></td>


                {{-- <td class="center"> {{ $guia }}</td>
                <td class="center"> {{ $placa }}</td> --}}

                <td class="center font-10"> <?php echo $detHab['guia']; ?> </td>
                <td class="center font-10"> <?php echo $detHab['placaVehiculo']; ?> </td>
                <td class="center font-10">
                    <?php echo isset($detHab['os']) ? $detHab['os'] : '-'; ?>
                </td>

                <td class="justifiy font-10" style="text-align: center">
                    <?php echo $detHab['descripcion']; ?></td>
                <td class="center  font-10"><?php echo 'NIU'; ?></td>
                <td class="center font-10"><?php echo $detHab['cantidad']; ?></td>
                {{-- <td class="center font-10"><?php echo number_format($totalDetalle / 1.18, 2); ?></td> --}}
                {{-- <td class="center font-10"><?php echo number_format($subtotal, 2); ?></td> --}}
                {{-- <td class="center font-10"><?php echo number_format($totalDetalle / 1.18, 2); ?></td> --}}
                <td class="center font-10"><?php echo number_format($detHab['precioventaunitarioxitem'] / 1.18, 2); ?></td>
                <td class="center font-10"><?php echo number_format($detHab['precioventaunitarioxitem'], 2); ?></td>
                <td class="center font-10"><?php echo number_format($detHab['precioventaunitarioxitem'] / 1.18, 2); ?></td>
            </tr>

            <?php endforeach; ?>


        </table>



        <table class="detTabla" style="width:200px; margin:10px;">
            <?php
            
            if ($linkRevisarFact) {
                echo '
                                                                                                                                                                                                                    <tr>
                                                                                                                                                                                                                        <td style="text-align: left;">
                                                                                                                                                                                                                            <b>Op. Gravada:</b>
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                        <td style="text-align: right;">
                                                                                                                                                                                                                            ' .
                    number_format($totalDetalle / 1.18, 2) .
                    '
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                    </tr>
                                                                                                                                                                                                                    <tr>
                                                                                                                                                                                                                        <td style="text-align: left;">
                                                                                                                                                                                                                            <label for="igv"><b>I.G.V.(18%):</b>
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                        <td style="text-align: right;">
                                                                                                                                                                                                                            <label for="igv">' .
                    number_format($totalDetalle - $totalDetalle / 1.18, 2) .
                    '
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                    </tr>
                                                                                                                                                                                                                    <tr>
                                                                                                                                                                                                                        <td style="text-align: left;">
                                                                                                                                                                                                                            <label for="opInafecta"><b>Op. Inafecta:</b>
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                        <td style="text-align: right;">
                                                                                                                                                                                                                            <label for="opInafecta">0.00
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                    </tr>
                                                                                                                                                                                                                    <tr>
                                                                                                                                                                                                                        <td style="text-align: left;">
                                                                                                                                                                                                                            <label for="opExonerada"><b>Op. Exonerada:</b>
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                        <td style="text-align: right;">
                                                                                                                                                                                                                            <label for="opExonerada">0.00
                                                                                                                                                                                                                        </td>
                                                                                                                                                                                                                    </tr>
                                                                                                                                                                                                                    <tr>
                                                                                                                                                                                                                        <td style="text-align: left;"><b>Importe Total</b></td>
                                                                                                                                                                                                    
                                                                                                                                                                                                                             <td style="text-align: right;"><b>' .
                    number_format($totalDetalle, 2) .
                    '</b></td>
                                                                                                                                                                                                                    </tr>';
            }
            ?>

        </table>
        <br><br>

        <br>


        <table>

            <table style="width:68%;">
                <tr>
                    <td class="blue justify">
                        <label for=""><b>Comentario:</b> {{ $comment ?? '' }}</label>
                    </td>
                </tr>
            </table>
            
            
            <br>
            <tr></tr>
            <tr>
                <td class="w10 blue left">
                    <label for="total"><b>SON:<?php
                    $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
                    $totalEnPalabras = $formatter->format(floor($totalDetalle)); // Redondeamos hacia abajo para quitar la parte decimal
                    if ($totalDetalle != floor($totalDetalle)) {
                        $parteDecimal = round(($totalDetalle - floor($totalDetalle)) * 100); // Convertimos la parte decimal a centavos
                        echo ucfirst($totalEnPalabras) . " CON $parteDecimal/100 SOLES";
                    } else {
                        echo ucfirst($totalEnPalabras) . ' CON 00/100 SOLES';
                    }
                    ?></b></label>
                </td>
            </tr>
            <tr>
                <td class="w10 left">
                    Representación impresa de la Factura Electrónica, consulte
                    <br>
                    en<a href="https://facturae-garzasoft.com" style="text-decoration: none; color: inherit;"
                        target="_blank">
                        https://facturae-garzasoft.com
                    </a>
                    <br> <br><br>
                    <b>CUENTA CORRIENTE OPERACIONES LOGISTICAS HERNANDEZ S.A.C</b>
                </td>

            </tr>
        </table>

        <br>

        <table style="width:100%; border-collapse: collapse;">
            <tr>

                <td style="vertical-align: top;">
                    <table class="border" style="width:80%;">
                        <tr>
                            <th class="border">BANCO</th>
                            <th class="border">CUENTA</th>
                            <th class="border">CCI</th>
                        </tr>
                        <tr>
                            <td class="border">BCP</td>
                            <td class="border">305-4587365-0-26</td>
                            <td class="border">002305458736502616</td>
                        </tr>
                        <tr>
                            <th class="border">BANCO</th>
                            <th class="border">CUENTA DETRACCION</th>
                            <th class="border">CCI</th>
                        </tr>
                        <tr>
                            <td class="border">BN</td>
                            <td class="border">00250034385</td>
                            <td class="border"></td>
                        </tr>
                    </table>
                </td>

                <td style="vertical-align: top; padding-right: 10px;">
                    {{ getArchivosDocument($idMovimiento,'nota') }}
                </td>
            </tr>
        </table>






    </div>




    {{-- <img class="footerImage" src="{{ asset('storage/img/curvBotton.png') }}" alt="degraded"> --}}
</body>

</html>
