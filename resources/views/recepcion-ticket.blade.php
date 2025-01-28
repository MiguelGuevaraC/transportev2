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

<head>
    <title>TICKET RECEPCIÓN</title>
    <style>
        html,
        body {
            margin: 0 3px;
            padding: 0 3px;
            height: 99%;
            width: 97%;
            font-family: 'Roboto', sans-serif;
        }





        h1 {
            text-align: center;
            color: #333;

            text-transform: uppercase;
            font-size: 10px;
        }

        hr {
            height: 0.4px;
            /* Cambia el grosor */
            background-color: black;
            /* Color del <hr>, opcional */
            border: none;
            /* Elimina el borde predeterminado */
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
            /* font-weight: bold; */
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
            margin-top: 5px;
            text-align: center;
        }


        .totales {
            font-size: 8px;
            margin-top: 1px;
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


        tr {
            padding: 8px;
            margin: 8px;
        }

        th,
        td {
            text-align: center;
            padding: 6px;
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

        .label-bold {
            font-weight: bold;
        }

        .info-text {
            font-size: 12px;
            /* Tamaño de fuente */
            color: #000;

            /* Para ajustar bien los bordes */
        }

        <style>body {
            font-family: Arial, sans-serif;
        }





        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .slogan {
            font-size: 8px;
            margin: 0;
        }

        hr {
            border: none;
            border-top: 1px solid black;
            margin: 1px 0;
        }

        .label {
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            margin-bottom: 5px;
        }

        .info {
            font-size: 10px;
            font-weight: normal;
        }

        .highlight {
            font-size: 10px;
            font-weight: bold;
            border-bottom: 1px solid black;
        }

        .info-section,
        .recipient-section,
        .guide-section {
            margin-bottom: 10px;

        }
    </style>

    </style>
</head>


<body class="ticket">

    <table style="width: 100%; border-collapse: collapse; text-align:left;">
        <tr>
            <td style="width: 20%; vertical-align: bottom; text-align:left;">
                <div>
                    <img width="53px" height="40px" class="logoImage" src="{{ asset('storage/img/logoR.jpeg') }}"
                        alt="logoTransporte">
                </div> 
            </td> 
            <td style="width: 80%; vertical-align: bottom; padding: 5px; text-align: left">


                <p style="font-size:12px">OPERACIONES LOGISTICAS HERNANDEZ S.A.C.</p>
            </td>
        </tr>
    </table>

    <hr>
    <table style="width: 100%; border-collapse: collapse; text-align: center;">
        <tr>
            <td style="width: 50%; letter-spacing: 1px; font-size: 17px; padding: 3px; font-weight: bold; text-align: left;">
                ORIGEN:
            </td>
            <td style="width: 50%; letter-spacing: 1px; font-size: 17px; padding: 3px; font-weight: bold; text-align: left; border-left: 2px solid black;">
                DESTINO:
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                <span class="info" style="letter-spacing: 2px; font-size: 15px; font-weight: bold;">
                    {{ $recepcion['origin']['name'] ?? '-' }}
                </span>
            </td> 
            <td style="width: 50%; border-left: 2px solid black;">
                <span class="info" style="letter-spacing: 2px; font-size: 15px; font-weight: bold;">
                    {{ $recepcion['destination']['name'] ?? '-' }}
                </span>
            </td>
        </tr>
    </table>
    


    <hr>
    <p style="font-size: 17px; font-weight: bold;text-align:left">DESTINATARIO:</p>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="font-size: 10px; font-weight: bold;text-align:left; text-transform: uppercase;">
                NOMBRE:
            </td>
            <td style="font-size: 11px; text-align:justify; text-transform: uppercase;">
                {{ namePerson($recepcion['recipient']) }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px;  text-align: left; font-weight: bold;text-transform: uppercase;">
                {{ $recepcion['recipient']['typeofDocument'] }}:
            </td>

            <td style="font-size: 12px; text-align:left">
                {{ $recepcion['recipient']['documentNumber'] }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px; font-weight: bold; text-align: left; text-transform: uppercase;">
                CELULAR:
            </td>

            <td style="font-size: 11px; text-align:left">
                {{ $recepcion['recipient']['telephone'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 10px; font-weight: bold; text-align: left; text-transform: uppercase;">
                ENTREGA A DOMICILIO:
            </td>
            <td style="font-size: 11px;  text-align: justify; text-transform: uppercase;">
                {{ $recepcion['typeDelivery'] === 'Domicilio' ? $recepcion['address'] ?? '-' : 'OFICINA' }}
            </td>

        </tr>
        </tr>
        <tr>
            <td style="font-size: 13px; font-weight: bold; text-align: left; text-transform: uppercase;">
                N° DE GUIA:
            </td>
            <td style="letter-spacing: 1px;font-size: 14px; font-weight: bold;text-align:center">
                {{ $recepcion['firstCarrierGuide']['numero'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="font-size: 13px; font-weight: bold; text-align: left; text-transform: uppercase;">
                Nº BULTOS:
            </td>
            <td style="font-size:16px; font-weight: bold; text-align: center;">
                <div
                    style="display: inline-block; padding: 2px; border: 2px solid #000; border-radius: 10px; width: 100px; min-width: 100px; text-align: center;">
                    {{ $recepcion['bultosTicket'] }}
                </div>
            </td>
        </tr>


    </table>



</body>







</html>
