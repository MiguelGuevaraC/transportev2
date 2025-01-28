<?php
namespace App\Console\Commands;

use App\Models\Bitacora;
use App\Models\CarrierGuide;
use App\Models\CreditNote;
use App\Models\Moviment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeclararGuiasAutomatico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:declararguias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Declaración de guias del día, a las 23:30';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fechaActual = Carbon::now()->setTimezone('America/Lima');
        // Log::info("Probar fecha de CRON ENVIO AUTOMATICO DE GUIAS $fechaActual");
        // PARA GUIAS
        if ($fechaActual->hour == 23 && $fechaActual->minute == 45) {

            Log::info('El comando declarar Guia Automatico se ejecutó en la hora: ' . $fechaActual->format('H:i'));

            $fecha = Carbon::now()->toDateString();

            // Obtener todas las guías que cumplen con la fecha y el estado "Pendiente"
            $carriers = CarrierGuide::whereDate('transferStartDate', $fecha)
                ->where('status_facturado', 'Pendiente')
                ->where('type', '!=', 'Manual')
                ->get();

            //  Definir el nombre de la función para la solicitud
            $funcion = "enviarGuiaRemision";

            // Verificar si hay guías que cumplan con los criterios
            if ($carriers->isEmpty()) {
                Log::error("NO SE ENCONTRARON GUIAS PENDIENTES PARA ENVIAR");
            }
            Log::error("INICIO ENVIO MASIVO $fecha: DE GUIAS DEL DÍA");
            $contador = 0;
            // Procesar cada guía encontrada
            foreach ($carriers as $carrier) {
                $idventa = $carrier->id;
                $contador++;
                // Construir la URL con los parámetros
                $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
                $params = [
                    'funcion'    => $funcion,
                    'idventa'    => $idventa,
                    'empresa_id' => 1,
                ];
                $url .= '?' . http_build_query($params);

                // Inicializar cURL
                $ch = curl_init();

                // Configurar opciones cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Ejecutar la solicitud y obtener la respuesta
                $response = curl_exec($ch);

                // Verificar si ocurrió algún error
                if (curl_errno($ch)) {
                    $error = curl_error($ch);
                    // Registrar el error en el log
                    Log::error("Error en cURL al enviar VENTA. ID venta: $idventa, $funcion Error: $error");
                } else {
                    // Registrar la respuesta en el log
                    Log::info("Respuesta recibida de VENTA para ID venta: $idventa, $funcion Respuesta: $response");
                }

                // Cerrar cURL
                curl_close($ch);

                // Actualizar el estado de la guía a "Enviado"
                $carrier->status_facturado = 'Enviado';
                $carrier->save();

                // Log del cierre de la solicitud para cada guía
                Log::info("Solicitud de GUIA finalizada para ID venta: $idventa. $funcion");
                $carrier = CarrierGuide::with('tract', 'platform', 'motive', 'origin', 'destination', 'sender', 'recipient', 'branchOffice',
                    'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
                    'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
                )->find($carrier->id);
                Bitacora::create([
                    'user_id'     => null,             // ID del usuario que realiza la acción
                    'record_id'   => $carrier->id,     // El ID del usuario afectado
                    'action'      => 'CRON',           // Acción realizada
                    'table_name'  => 'carrier_guides', // Tabla afectada
                    'data'        => json_encode($carrier),
                    'description' => 'Declaración Automatica 23:30 pm', // Descripción de la acción
                    'ip_address'  => null,                               // Dirección IP del usuario
                    'user_agent'  => null,                               // Información sobre el navegador/dispositivo
                ]);
            }
            Log::error("FINALIZADO ENVIO MASIVO $fecha, GUIAS ENVIADAS: $contador");

        }
        // PARA VENTAS
        if ($fechaActual->hour == 23 && $fechaActual->minute == 50) {

            Log::info('El comando declarar VENTAS Automatico se ejecutó en la hora: ' . $fechaActual->format('H:i'));
            $fecha = Carbon::now()->toDateString();
            $fechaHoy = Carbon::now()->toDateString(); // Fecha actual
            $fechaAyer = Carbon::now()->subDays(1)->toDateString(); // Fecha de un día atrás
            

            // Obtener todas las guías que cumplen con la fecha y el estado "Pendiente"
            $ventas = Moviment::where(function ($query) use ($fechaHoy, $fechaAyer) {
                $query->whereDate('paymentDate', $fechaHoy)
                    ->orWhereDate('paymentDate', $fechaAyer);
            })
                ->where('status_facturado', 'Pendiente')
                ->where('movType', 'Venta')
                ->where('sequentialNumber', 'NOT LIKE', 'T%')
                ->get();

            Log::error("INICIO ENVIO MASIVO $fecha: DE VENTAS DEL DÍA");
            $contador = 0;
            // Procesar cada guía encontrada
            foreach ($ventas as $venta) {
                $numero = $venta->sequentialNumber;
                if ($numero[0] === 'B') {
                    $funcion = "enviarBoleta";
                } elseif ($numero[0] === 'F') {
                    $funcion = "enviarFactura";
                } else {
                    Log::error("NO ES BOLETA NI FACTURA: " . $venta);
                }

                $idventa = $venta->id;
                $contador++;
                // Construir la URL con los parámetros
                $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
                $params = [
                    'funcion'    => $funcion,
                    'idventa'    => $idventa,
                    'empresa_id' => 1,
                ];
                $url .= '?' . http_build_query($params);

                // Inicializar cURL
                $ch = curl_init();

                // Configurar opciones cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Ejecutar la solicitud y obtener la respuesta
                $response = curl_exec($ch);

                // Verificar si ocurrió algún error
                if (curl_errno($ch)) {
                    $error = curl_error($ch);
                    // Registrar el error en el log
                    Log::error("Error en cURL al enviar VENTA. ID venta: $idventa,$funcion Error: $error");
                    // echo 'Error en cURL: ' . $error;
                } else {
                    // Registrar la respuesta en el log
                    Log::error("Respuesta recibida de VENTA para ID venta: $idventa,$funcion Respuesta: $response");
                    // Mostrar la respuesta
                    // echo 'Respuesta: ' . $response;
                }

                // Cerrar cURL
                curl_close($ch);

                // Actualizar el estado de la guía a "Enviado"
                $venta->status_facturado = 'Enviado';
                $venta->save();

                // Log del cierre de la solicitud para cada guía
                Log::info("Solicitud de VENTA finalizada para ID venta: $idventa. $funcion");

                Bitacora::create([
                    'user_id'     => null,        // ID del usuario que realiza la acción
                    'record_id'   => $venta->id,  // El ID del usuario afectado
                    'action'      => 'CRON',      // Acción realizada
                    'table_name'  => 'moviments', // Tabla afectada
                    'data'        => json_encode($venta),
                    'description' => 'Declaración Automatica Ventas 23:48 pm', // Descripción de la acción
                    'ip_address'  => null,                                      // Dirección IP del usuario
                    'user_agent'  => null,                                      // Información sobre el navegador/dispositivo
                ]);
            }
            Log::error("FINALIZADO ENVIO MASIVO VENTAS $fecha, VENTAS ENVIADAS: $contador");

        }
// PARA NOTA CREDITO
        if ($fechaActual->hour == 23 && $fechaActual->minute == 53) { 

            Log::info('El comando declarar NOTA Automatico se ejecutó en la hora: ' . $fechaActual->format('H:i'));

            $fecha = Carbon::now()->toDateString();

            $notascredito = CreditNote::whereDate('created_at', $fecha)
            ->where('status_facturado', 'Pendiente')
            // ->where('reason', '!=', '13')
            ->get();

            Log::error("INICIO ENVIO MASIVO $fecha: DE NC DEL DÍA");
            $contador = 0;
            // Procesar cada guía encontrada
            foreach ($notascredito as $venta) {
                $numero  = $venta->sequentialNumber;
                $funcion = "enviarNotaCredito";

                $idventa = $venta->id;
                $contador++;
                // Construir la URL con los parámetros
                $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
                $params = [
                    'funcion'    => $funcion,
                    'idventa'    => $idventa,
                    'empresa_id' => 1,
                ];
                $url .= '?' . http_build_query($params);

                // Inicializar cURL
                $ch = curl_init();

                // Configurar opciones cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Ejecutar la solicitud y obtener la respuesta
                $response = curl_exec($ch);

                // Verificar si ocurrió algún error
                if (curl_errno($ch)) {
                    $error = curl_error($ch);
                    // Registrar el error en el log
                    Log::error("Error en cURL al enviar NC. ID nota: $idventa,$funcion Error: $error");
                    // echo 'Error en cURL: ' . $error;
                } else {
                    // Registrar la respuesta en el log
                    Log::error("Respuesta recibida de NC para ID nota: $idventa,$funcion Respuesta: $response");
                    // Mostrar la respuesta
                    // echo 'Respuesta: ' . $response;
                }

                // Cerrar cURL
                curl_close($ch);

                // Actualizar el estado de la guía a "Enviado"
                $venta->status_facturado = 'Enviado';
                $venta->save();

                // Log del cierre de la solicitud para cada guía
                Log::info("Solicitud de NC finalizada para ID NC: $idventa. $funcion");

                Bitacora::create([
                    'user_id'     => null,           // ID del usuario que realiza la acción
                    'record_id'   => $venta->id,     // El ID del usuario afectado
                    'action'      => 'CRON',         // Acción realizada
                    'table_name'  => 'credit_notes', // Tabla afectada
                    'data'        => json_encode($venta),
                    'description' => 'Declaración Automatica NC 23:45 pm', // Descripción de la acción
                    'ip_address'  => null,                                  // Dirección IP del usuario
                    'user_agent'  => null,                                  // Información sobre el navegador/dispositivo
                ]);
            }
            Log::error("FINALIZADO ENVIO NOTA MASIVO $fecha, NOTAS ENVIADAS: $contador");

        }

    }
}
