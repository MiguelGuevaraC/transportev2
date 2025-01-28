<!DOCTYPE html>
<html>


<head>
    <title>TICKET DE CAJA</title>
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
    </style>
</head>


<body>
    <div class="ticket">
        <div style="text-align: center;margin:auto">
            <img width="100px" height="100px" class="logoImage" src="{{ asset('storage/img/logoTicket.jpeg') }}"
                alt="logoTransporte">
        </div>

        <h1 style="font-size:13px">OPERACIONES LOGISTICAS HERNANDEZ S.A.C</h1>


        <section class="detalleCaja">
            <label for="RUC"><b>RUC:<span id="RUC">20605597484</span></b> </label>
            <label for="direccion"><span style="font-size:7px" id="direccion">MZA. 38 LOTE. 4A INT. 302 P.J. CHOSICA
                    DEL NORTE LAMBAYEQUE CHICLAYO
                    LA VICTORIA</span></label><br>
            <label for="fecha">
                Fecha Impresión: <span id="fecha"><?php echo date('Y-m-d H:i:s'); ?></span>
            </label>
            <label for="tipoElectronica"><b><span style="font-size:10px"
                        id="nombretipoElectronica">{{ $tipoElectronica }}</span></b></label>
                        
            <label for="numeroVenta"><b><span style="font-size:9px"
                        id="numeroVenta">{{ $numeroCaja }}</span></b></label>
        </section>


        <hr>


        <section class="totales">
            <table>
                <tr>
                    <td style="text-align: left;">
                        <label for="fechaemision"><b>FECHA:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <label for="">{{ $fechaemision }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 10px;">
                        <label for="ruc_dni"><b>ORIGEN:</b></label>
                    </td>
                    <td style="text-align: right;">
                        {{-- <label for="">{{ $ruc_dni }}</label> --}}
                        <label for="">Viaje: {{ $programacion->numero ?? '-' }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 10px;">
                        <label for="cliente"><b>CHOFER:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <label for="">{{ $conductor ?? '-' }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 10px;">
                        <label for="ruc_dni"><b>DESTINO:</b></label>
                    </td>
                    <td style="text-align: right;">
                        {{-- <label for="">{{ $ruc_dni }}</label> --}}
                        <label for="">{{ $caja }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left; padding-left: 10px;">
                        <label for="cliente"><b>SUCURSAL:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <label for="">{{ $sucursal }}</label>
                    </td>
                </tr>
            </table>
        </section>

        <section class="totales">
            <table>

                <tr>
                    <td style="text-align: left; word-wrap: break-word; white-space: normal;">
                        <label for="direccion"><b>DESCRIPCIÓN:</b></label>
                    </td>
                    <td style="text-align: right; word-wrap: break-word; white-space: normal;">
                        <label for="">{{ $comentario }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left; word-wrap: break-word; white-space: normal;">
                        <label for="direccion"><b>USUARIO:</b></label>
                    </td>
                    <td style="text-align: right; word-wrap: break-word; white-space: normal;">
                        <label for="">{{ $usuario }}</label>
                    </td>
                </tr>

            </table>
        </section>

        <br>


        <section class="detalles">
            <table class="table">
                <thead>
                    <tr>
                        <th><b>Concepto</b></th>
                        <th><b>Cant.</b></th>
                        <th><b>Unit.</b></th>
                        <th><b>Subt.</b></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalDetalle = 0;
            
                    foreach ($detalles as $detHab) :
                        $subtotal = $detHab['precioventaunitarioxitem'] * $detHab['cantidad'];
                        $totalDetalle += $subtotal;
                    ?>

                    <tr>
                        <td colspan="4" class="" style="text-align: justify">
                            <?php echo $detHab['descripcion']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="centroText"></td>
                        <td class="centroText"><?php echo $detHab['cantidad']; ?></td>
                        <td class="centroText"><?php echo number_format($detHab['precioventaunitarioxitem'], 2); ?></td>
                        <td class="total"><?php echo number_format($subtotal, 2); ?></td>
                    </tr>

                    <?php endforeach; ?>

                    <tr>
                        <td class="centroText"></td>
                        <td class="centroText"></td>
                        <td class="centroText"><strong>Total</strong></td>
                        <td class="total"><strong><?php echo number_format($totalDetalle, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>

        </section>

        <br>



        <section class="totales">
            <table class="totalesTb">




                ?>





                <?php if (false): ?>
                <tr>
                    <td style="text-align: left;">
                        <label for="total"><b>TOTAL APAGAR:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <?php echo number_format($totalDetalle, 2); ?></label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">
                        <label for="total"><b>EFECTIVO:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <?php echo number_format($totalPagado, 2); ?></label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">
                        <label for="total"><b>VUELTO:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <?php echo number_format($totalPagado - $totalDetalle, 2); ?></label>
                    </td>
                </tr>
                <?php endif; ?>


              
                <tr>
                    <td colspan="2" style="text-align: center;">
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

            </table>

<br><br><br><br>

<table>
                  
    <tr style=" width:100%">
        <td style="font-size: 6px; width:30px">
            <hr>
            FIRMA
        </td>
        <td  style="font-size: 6px; width:30px">
            <hr>
            DNI
        </td>
        <td style="font-size: 6px; width:40px">
            <hr>
            NOMBRE
        </td>
    </tr>
</table>



        </section>
        <br>



        <!-- <div class="footer">
            Copyright© 2024. Garzasoft. Todos los derechos reservados
        </div> -->


    </div>
</body>


</html>
