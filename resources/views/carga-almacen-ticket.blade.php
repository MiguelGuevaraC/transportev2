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

<?php 

?>
<body>
    <div class="ticket">
        <div style="text-align: center;margin:auto">
            <img width="180px" height="auto" class="logoImage" src="https://develop.garzasoft.com/transportedev/public/storage/app/public/img/logoTransportes.jpeg" alt="logoTransporte">

            <h1 style="font-size:9px">OPERACIONES LOGISTICAS HERNANDEZ S.A.C</h1>
            <label for="RUC"><b>RUC:<span id="RUC">20605597484</span></b> </label>
        </div>

        <label for="direccion"><span style="font-size:7px" id="direccion">MZA. 38 LOTE. 4A INT. 302 P.J. CHOSICA
                DEL NORTE LAMBAYEQUE CHICLAYO
                LA VICTORIA</span></label>


        <hr>

        <section class="ticket" style="font-size: 8px; text-align: center;">
            <b><span id="nombretipoElectronica">DOCUMENTO DE ALMACÉN</span></b>
            <br>
            <b><span id="numeroVenta" style="font-size: 9px;">{{ $doc_carga->code_doc }}</span></b>
        
            <hr style="border: 0.5px dashed #000;">
        
            <table style="width: 100%; font-size: 8px;">

                <tr>
                    <td style="text-align: left;"><b>Fecha:</b></td>
                    <td style="text-align: right;">{{ $doc_carga->movement_date ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;"><b>Cliente:</b></td>
                    <td style="text-align: right;">{{ namePerson($doc_carga->person ?? null) ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;"><b>RUC/DNI:</b></td>
                    <td style="text-align: right;">{{ $doc_carga?->person?->documentNumber ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;"><b>Sucursal:</b></td>
                    <td style="text-align: right;">{{ $doc_carga->branchOffice->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;"><b>Tipo Movimiento:</b></td>
                    <td style="text-align: right;">{{ $doc_carga->movement_type ?? '-' }}</td>
                </tr>
            </table>
        
            <hr style="border: 0.5px dashed #000;">
        
            <b>DETALLE DEL MOVIMIENTO</b>
        
            <table style="width: 100%; font-size: 8px; border-collapse: collapse; margin-top: 5px;">
                <tr>
                    <th style="text-align: left;">Producto</th>
                    <th style="text-align: center;">Cantidad</th>
                    <th style="text-align: center;">Peso</th>
                </tr>
                <tr>
                    <td style="text-align: left;">{{ $doc_carga->product->description ?? '-' }}</td>
                    <td style="text-align: center;">{{ $doc_carga->quantity ?? '-' }}</td>
                    <td style="text-align: center;">{{ $doc_carga->weight ?? '-' }} kg</td>
                </tr>
            </table>
        
            <hr style="border: 0.5px dashed #000;">
        
            <table style="width: 100%; font-size: 8px;">
                <tr>
                    <td style="text-align: left;"><b>Lote:</b></td>
                    <td style="text-align: right;">{{ $doc_carga->lote_doc ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;"><b>Fecha Venc.:</b></td>
                    <td style="text-align: right;">{{ $doc_carga->date_expiration ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align: left;"><b>N° Anexo:</b></td>
                    <td style="text-align: right;">{{ $doc_carga->num_anexo ?? '-' }}</td>
                </tr>
            </table>
        
            <hr style="border: 0.5px dashed #000;">
        
            <b>OBSERVACIONES:</b>
            <p style="text-align: left; font-size: 8px; word-wrap: break-word;">
                {{ $doc_carga->comment ?? 'Sin comentarios.' }}
            </p>
        
            <hr style="border: 0.5px dashed #000;">
        
            <p style="font-size: 8px; text-align: left;"><b>Firma de conformidad:</b></p>
            <br><br><br>
            <hr style="border: 1px solid #000; width: 60%;">
            <p style="font-size: 8px; text-align: center;">Nombre y firma</p>
        
   
        </section>
        
   

    </div>
</body>


</html>
