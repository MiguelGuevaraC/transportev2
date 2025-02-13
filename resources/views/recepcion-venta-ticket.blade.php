<!DOCTYPE html>
<html>
@php

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
        return $cadena;
    }
@endphp

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
                echo '<img width="70px" src="https://develop.garzasoft.com:81/transporteFacturadorZip/ficheros/' .
                    $pngFile .
                    '" alt="Imagen PNG">';
            } else {
                echo '';
            }
        } else {
            echo 'Error en la solicitud.';
        }
    }
@endphp
<head>
    <title>TICKET DE RECEPCIÓN</title>
    <style>
        body,
        html {
            font-family: Arial, sans-serif;
            margin: 0 10px;
            padding: 0 10px;
            height: 99%;
            width: 98%;
            justify-content: center;
            align-items: center;
        }


        .ticket {
            /* width: 40mm; */

            box-sizing: border-box;
        }


        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 10px;
        }


        .text-center {
            text-align: center;
            margin: auto;
        }



        .totalesTb {
            padding: 1px 1px;
            border-collapse: collapse;
        }


        p {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 8px;
            margin: 5px 0;
        }


        .footer {
            text-align: center;
            font-size: 6px;
            color: #888;
        }


        .detalleCaja,
        .detalles,
        .conceptos {
            font-size: 8px;
            margin-top: 2px;
            text-align: center;
        }


        .totales {
            font-size: 8px;

            text-align: center;
        }


        .totales table {
            width: 100%;
        }


        .detalles table {
            width: 100%;
            border-collapse: collapse;
        }


        .detalles table tr:last-child {
            border-top: 1px solid #000;
        }


        th,
        td {
            text-align: center;
            padding: 2px;
        }


        th {
            background-color: white;
            color: black;
            font-size: 8px;
            border-bottom: 1px solid #000;
            /* Agregamos borde superior a las celdas de encabezado */
        }


        td {
            font-size: 8px;
        }


        label {
            display: block;
            margin: 2px;
            padding: 0;
            font-size: 8px;
        }

        hr {
            margin: 0, 10px;
            padding: 0;
            border: none;
            border-top: 1px solid black;
        }
    </style>
</head>


<body>
    <div class="ticket">
        <div style="text-align: center;margin:auto">
            <img width="180px" height="auto" class="logoImage" src="{{ asset('storage/img/logoTransportes.jpeg') }}"
                alt="logoTransporte">
            <h1 style="font-size:9px">OPERACIONES LOGISTICAS HERNANDEZ S.A.C</h1>
            <label for="RUC"><b>RUC:<span id="RUC">20605597484</span></b> </label>
        </div>

        <label for="direccion"><span style="font-size:7px" id="direccion">MZA. 38 LOTE. 4A INT. 302 P.J. CHOSICA
                DEL NORTE LAMBAYEQUE CHICLAYO
                LA VICTORIA</span></label>


        <hr>

        <section class="totales" style="font-size: 8px;">
            <b><span id="nombretipoElectronica">RECEPCIÓN CARGA ELECTRÓNICA</span></b>
            <br>
            <b><span id="numeroVenta" style="font-size:9px">{{ $reception->codeReception }}</span></b>

            <table style="width: 100%;">
                @foreach ([
        'Atendido por' => namePerson($reception->seller->person ?? null) ?? '-',
        'Fecha' => $reception->receptionDate ?? '-',
        'Cliente' => namePerson($reception->payResponsible ?? null) ?? '-',
        'RUC/DNI' => $reception?->sender?->typeofDocument ?? '-',
    ] as $label => $value)
                    <tr>
                        <td style="text-align: left;"><b>{{ $label }}:</b></td>
                        <td style="text-align: right;">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </section>

        <section class="totales" style="font-size: 8px;">
            <b><span id="nombretipoElectronica">DATOS DE ENVÍO</span></b>
            <hr style="margin: 0; padding: 0; border: 0; border-top: 1px solid black;">
        
            <table style="width: 100%;">
                @foreach ([
                    'Remitente' => (namePerson($reception->sender ?? null) ?? '-') . ' ' . ($reception->sender->documentNumber ?? '-'),
                    'D. Partida' => ($reception->pointSender->name ?? '-') . ' ' . ($reception->origin->name ?? '-'),
                    'Destinatario' => (namePerson($reception->recipient ?? null) ?? '-') . ' ' . ($reception->recipient->documentNumber ?? '-'),
                    'D. Llegada' => ($reception->pointDestination->name ?? '-') . ' ' . ($reception->destination->name ?? '-')
                ] as $label => $value)
                    <tr>
                        <td style="text-align: left;"><b>{{ $label }}:</b></td>
                        <td style="text-align: right;">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </section>
        
        <section class="totales" style="font-size: 8px;">
            <hr style="margin: 0; padding: 0; border: 0; border-top: 1px solid black;">
        
            <table style="width: 100%;">
                @foreach ([
                    'T. Servicio' => $reception->typeService?? '',
                    'Origen' => $reception?->origin?->name ?? '',
                    'Destino' => $reception?->destination?->name ?? '',
                    'Cond. Pago' => $reception->conditionPay?? '',
                    'Srvicio' => $reception->typeDelivery?? '',
                   'D. Anexos' => $reception?->firstCarrierGuide?->document ? ($reception?->comment ?? '-') : '-',

                ] as $label => $value)
                    <tr>
                        <td style="text-align: left;"><b>{{ $label }}:</b></td>
                        <td style="text-align: right;">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </section>
<br>
        <section class="detalles">
            <table class="table">
                <thead>
                    <tr>
                        <th><b>Descripción</b></th>
                        <th><b>Peso.</b></th>
                        <th><b>Importe</b></th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td colspan="3" class="" style="text-align: justify">
                            <?php echo $reception->details->pluck('description')->implode(', '); ?>

                        </td>
                    </tr>
                    <tr>
                        <td class="centroText"><strong>Total</strong></td>
                        
                        <td class="centroText"><?php echo $reception->netWeight;?></td>
                        <td class="total"><?php echo number_format($reception->paymentAmount, 2); ?></td>
                    </tr>

              
                </tbody>
            </table>

        </section>

        <section class="totales" style="text-align: center; margin: auto;">
            <table class="totalesTb">
                <table style="width: 100%;">
                    <tr>
                        <!-- QR en la primera columna (Solo se muestra si hay movimiento) -->
                        <td style="width: 50%; text-align: left;">
                            @if ($reception->moviment)
                                {{ getArchivosDocument($reception->moviment->id, 'venta') }}
                            @endif
                        </td>
        
                        <!-- Factura y Guía en la segunda columna -->
                        <td style="width: 50%; text-align: left; vertical-align: top;">
                            <table style="font-size: 7px; width: 100%;">
                                @foreach ([
                                  'Documento Venta' => $reception?->moviment?->sequentialNumber ?? ($reception?->nro_sale ?? '-'),
                                    'Guía Transporte' => $reception?->firstCarrierGuide?->numero ?? '-',
                                ] as $label => $value)
                                    <tr>
                                        <td colspan="2" style="text-align: left;"><b>{{ $label }}</b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align: left;">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                </table>
        
                <!-- Total en palabras -->
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <label for="total"><b>SON: 
                            <?php
                            $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
                            $totalEnPalabras = strtoupper($formatter->format(floor($totalDetalle))); // Convertimos a mayúsculas
                            if ($totalDetalle != floor($totalDetalle)) {
                                $parteDecimal = round(($totalDetalle - floor($totalDetalle)) * 100); // Convertimos la parte decimal a centavos
                                echo ucfirst($totalEnPalabras) . " CON $parteDecimal/100 SOLES";
                            } else {
                                echo ucfirst($totalEnPalabras) . ' CON 00/100 SOLES';
                            }
                            ?>
                        </b></label>
                    </td>
                </tr>
            </table>
        </section>
        
        
        <br>

    </div>
</body>


</html>
