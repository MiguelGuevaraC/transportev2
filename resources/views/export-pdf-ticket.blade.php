<!DOCTYPE html>
<html>


<head>
    <title>TICKET DE PAGO</title>
    <style>
        body, html {
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
            <img width="100px" height="100px" class="logoImage" src="{{ asset('storage/img/logoTicket.jpeg') }}" alt="logoTransporte">
        </div> 

        <h1 style="font-size:13px">OPERACIONES LOGISTICAS HERNANDEZ S.A.C</h1>


        <section class="detalleCaja">
            <label for="RUC"><b>RUC:<span id="RUC">20605597484</span></b> </label>
            <label for="direccion"><span style="font-size:7px" id="direccion">MZA. 38 LOTE. 4A INT. 302 P.J. CHOSICA
                    DEL NORTE LAMBAYEQUE CHICLAYO
                    LA VICTORIA</span></label>
            <label for="tipoElectronica"><b><span style="font-size:10px"
                        id="nombretipoElectronica">{{ $tipoElectronica }}</span></b></label>
            <label for="numeroVenta"><b><span style="font-size:9px"
                        id="numeroVenta">{{ $numeroVenta }}</span></b></label>
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
                    <td style="text-align: left;">
                        <label for="ruc_dni"><b>DNI/RUC:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <label for="">{{ $ruc_dni }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">
                        <label for="cliente"><b>NOMBRE:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <label for="">{{ $cliente }}</label>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">
                        <label for="direccion"><b>DIRECCIÓN:</b></label>
                    </td>
                    <td style="text-align: right;">
                        <label for="">{{ $direccion }}</label>
                    </td>
                </tr>


            </table>
        </section>

        <section class="totales">
            <table>
                <tr>
                    <td style="text-align: left; word-wrap: break-word; white-space: normal;">
                        <label for="direccion"><b>FORMA PAGO:</b></label>
                    </td>
                    <td style="text-align: right; word-wrap: break-word; white-space: normal;">
                        <label for="">{{ $typePayment }}</label>
                    </td>
                </tr>
        
                @php
                    $i=1;
                @endphp
                {{-- Si la forma de pago es Créditos, mostrar tabla de cuentas --}}
                @if($typePayment == 'Créditos')
                    <tr>
                        <td colspan="2">
                            <table border="1" style="width: 100%; margin-top: 10px;">
                                <thead>
                                    <tr>
                                        <th>CUOTA</th>
                                        <th>FECHA V.</th>
                                        <th>MONTO NETO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cuentas as $cuenta)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $cuenta->date }}</td>
                                        <td>
                                            @php
                                                // Verifica que el porcentaje no sea null y sea mayor a 0
                                                $descuento = (!is_null($porcentaje) && $porcentaje > 0) 
                                                            ? round(($totalPagado * $porcentaje) / 100) 
                                                            : 0;
                                                $montoFinal = $cuenta->total - $descuento;
                                            @endphp
                                            {{ number_format($montoFinal, 2) }}
                                        </td>
                                        
                                    </tr>
                                @endforeach
                                
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @endif
            </table>
        </section>
        
<br>


        <section class="detalles">
            <table class="table">
                <thead>
                    <tr>
                        <th><b>Descripción</b></th>
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
                                                                        <td style="text-align: left;"><b>Total</b></td>
                                                    
                                                                             <td style="text-align: right;"><b>' .
                        number_format($totalDetalle, 2) .
                        '</b></td>
                                                                    </tr>';
                }
                ?>

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


                <br>
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







        </section>
        <br>
        <?php
        if ($linkRevisarFact) {
            echo '<section class="detalleCaja">
                                                                                    <label for="gracias" style="font-size:12px">¡ Gracias por su compra !</label>
                                                                                    <br>
                                                                                    <label for=""><b>Representación impresa del Comprobante Electrónico, consulta en <a style="text-decoration: none;color:black" href="http://facturae-garzasoft.com" target="_blank">http://facturae-garzasoft.com</a> </b></label>
                                                                                  </section>';
        }
        ?>


        <!-- <div class="footer">
            Copyright© 2024. Garzasoft. Todos los derechos reservados
        </div> -->


    </div>
</body>


</html>
