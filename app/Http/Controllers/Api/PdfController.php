<?php
namespace App\Http\Controllers\Api;

use App\Exports\CajaExport;
use App\Exports\CuentasPorCobrarExport;
use App\Exports\ReceptionsExport;
use App\Exports\SalesExport;
use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\CarrierGuide;
use App\Models\CreditNote;
use App\Models\DriverExpense;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\Person;
use App\Models\Programming;
use App\Models\Reception;
use App\Models\Worker;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class PdfController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/saveguia/{id}",
     *     summary="Exportar Guía Transportista",
     *     tags={"CarrierGuide"},
     *     description="Genera y descarga una Guía Transportista en formato PDF",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la Guía Transportista",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guía Transportista en formato PDF",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en los datos")
     *         )
     *     )
     * )
     */
    public function guia(Request $request, $id)
    {
        $object = CarrierGuide::find($id);

        if (! $object) {
            abort(404);
        }
        $this->generarQrGuia();
        $object = CarrierGuide::with('tract', 'platform',
            'districtEnd.province.department', 'districtStart.province.department',
            'sender', 'recipient',
            'payResponsible',
            'driver',
            'driver.person',
            'copilot',
            'copilot.person',
            'subcontract',
            'driver.person',
            'reception'
        )->find($id);
        // $pdf = Pdf::loadView('guia', [
        //     'object' => $object,
        // ]);
        // dd($object);
        // return $pdf->stream('guia.pdf');
        // return $pdf->stream('guia.pdf');
        // return view('guia')->render();

        Bitacora::create([
            'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
            'record_id'   => $object->id,      // El ID del usuario afectado
            'action'      => 'GET',            // Acción realizada
            'table_name'  => 'carrier_guides', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'DESCARGÓ PDF DE GUIA', // Descripción de la acción
            'ip_address'  => $request->ip(),          // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),   // Información sobre el navegador/dispositivo
        ]);

        $html = view('guia', compact('object'))->render();

        // Crear una nueva instancia de Dompdf
        // $html = View::make('guia', $object)->render();

        // Configurar DomPDF
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Descargar el PDF con un nombre de archivo dinámico basado en el ID
        return $dompdf->stream('Guia_' . now() . '.pdf');
    }

    public function guiaPDF($id)
    {
        $object = CarrierGuide::with('tract', 'platform',
            'origin', 'destination',
            'sender', 'recipient', 'motive',
            'payResponsible', 'driver', 'copilot', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        )->find($id);

        if (! $object) {
            abort(404);
        }
        $this->generarQrGuia();
        $object = CarrierGuide::with('tract', 'platform',
            'origin', 'destination',
            'sender', 'recipient',
            'payResponsible',
            'driver',
            'driver.person',
            'copilot',
            'copilot.person',
            'subcontract',
            'driver.person',
            'reception'
        )->find($id);
        $pdf = Pdf::loadView('guia', compact('object'));
        return $pdf->stream('guiaPDF.pdf');

    }

    public function creditNote($id)
    {
        $object = CreditNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])->find($id);

        $Movimiento = Moviment::with(['reception',
            'reception.firstCarrierGuide.origin', 'reception.firstCarrierGuide.destination'])->find($object->moviment_id);
        $linkRevisarFact   = false;
        $pointSend         = strtoupper($Movimiento->reception?->pointSender?->name) ?? '';
        $pointDestination  = strtoupper($Movimiento->reception?->pointDestination?->name ?? '');
        $ruta              = $pointSend . ' - ' . $pointDestination;
        $receptionDetails  = $Movimiento?->reception?->details() ?? [];
        $descriptionString = '';
        if ($receptionDetails != []) {
            $descriptions      = $receptionDetails?->pluck('description')?->toArray() ?? []; // Obtiene todas las descripciones
            $descriptionString = implode(', ', $descriptions);                               // Une las descripciones con comas
        }
        if ($Movimiento) {
            $productList = $Movimiento->detalles;
        }
        // Inicializar el array de detalles
        $detalles = [];

        $motiveMap = [
            1  => 'Anulación de la Operación',
            2  => 'Anulación por error en el RUC',
            3  => 'Corrección por error en la descripción',
            4  => 'Descuento global',
            5  => 'Descuento por ítem',
            6  => 'Devolución total',
            7  => 'Devolución por ítem',
            8  => 'Bonificación',
            9  => 'Disminución en el valor',
            10 => 'Otros conceptos',
            13 => 'Ajuste Monto y Fecha',
        ];

        // Obtener el nombre del motivo o un valor predeterminado si no se encuentra
        $motiveName = $motiveMap[$object->reason] ?? 'Motivo no encontrado';

        if ($object->total < $object->totalReferido) {

            $detalles[] = [
                "descripcion"              => $motiveName ?? '-',
                "os"                       => '-',
                "guia"                     => '-',
                "placaVehiculo"            => '-',
                "cantidad"                 => 1, // Cantidad fija (es un servicio)
                "precioventaunitarioxitem" => $object->total ?? 0,
            ];

        } else {
            if (($productList) != []) {
                foreach ($productList as $producto) {
                    // Buscar la recepción si existe 'reception_id', si no asigna null
                    // $reception = Reception::find($producto->reception_id ?? null);

                    // Usar operadores de navegación segura y coalescencia para evitar errores y asignar valores por defecto
                    $detalles[] = [
                        "descripcion"              => $producto->description ?? '-',
                        "os"                       => $producto->os ?? '-',
                        "guia"                     => $producto->guia ?? '-',
                        "placaVehiculo"            => $producto->placa ?? '-',
                        "cantidad"                 => $producto->cantidad ?? 1, // Cantidad fija (es un servicio)
                        "precioventaunitarioxitem" => $producto->precioVenta ?? 0,
                    ];
                }
            }
        }
        $tipoDocumento = '';
        $num           = $Movimiento->sequentialNumber;
        if (strpos($num, 'B') === 0) {
            $tipoDocumento   = 'BOLETA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'F') === 0) {
            $tipoDocumento   = 'FACTURA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'T') === 0) {
            $tipoDocumento   = 'TICKET ELECTRÓNICO';
            $linkRevisarFact = false;
        } else {
            abort(404);
        }
        $dateTime = Carbon::now()->format('Y-m-d H:i:s');
        // $personaCliente = Person::find($Movimiento->person_id);
        $personaCliente = Person::withTrashed()->find($Movimiento->person_id);
        $fechaInicio    = $Movimiento->created_at;
        $rucOdni        = $personaCliente->documentNumber;
        $direccion      = "";
        if (strtoupper($personaCliente->typeofDocument) != 'DNI') {
            $nombreCliente = $personaCliente->businessName;
            $direccion     = $personaCliente->fiscalAddress ?? '-';
        } else {
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $direccion     = $personaCliente->address ?? '-';
        }

        if ($personaCliente->names == 'VARIOS') {
            $nombreCliente = "VARIOS";
            if (strpos($num, 'B') === 0) {
                $rucOdni = '11111111';
            } elseif (strpos($num, 'F') === 0) {
                $rucOdni = '11111111111';
            }
        }
        $direccion = $personaCliente->address ?? '-';
        // Generar el código QR
        // $qrCode = new QrCode($num);
        // $writer = new PngWriter();
        // $qrImage = $writer->write($qrCode);
        // // Generar un nombre único para el archivo
        // $fileName = $num . '.png';
        // $filePath = 'qr_images/' . $fileName;
        // if (Storage::disk('public')->exists($filePath)) {
        //     $fileName = $num . '.png';
        //     $filePath = 'qr_images/' . $fileName;
        // }
        // Storage::disk('public')->put($filePath, $qrImage->getString());
        $dataE = [
            'object'            => $object,
            'title'             => 'NOTA DE CREDITO',
            'linkRevisarFact'   => $linkRevisarFact,
            'ruc_dni'           => $rucOdni,
            'direccion'         => $direccion,
            'tipoElectronica'   => 'NOTA DE CREDITO ELECTRÓNICA',
            'typePayment'       => $Movimiento->typePayment ?? '-',
            'nroReferencia'     => $Movimiento->sequentialNumber ?? '-',
            'numeroNotaCredito' => $object->number ?? '',
            'comment'           => $object->comment ?? '',
            'numeroVenta'       => $num,
            'fechaemision'      => $object->created_at->format('Y-m-d'),
            'cliente'           => $nombreCliente,
            'detalles'          => $detalles,
            'vuelto'            => '0.00',
            'totalPagado'       => $Movimiento->total,
            'totalNota'         => $object->total,
            'idMovimiento'      => $object->id,

            'motive'            => $object?->reason ?? '',
            'formaPago'         => $Movimiento->formaPago ?? '-',
            'fechaInicio'       => $fechaInicio,
            'guia'              => $Movimiento->reception?->firstCarrierGuide?->numero ?? '-',
            'placa'             => $Movimiento->reception?->firstCarrierGuide?->tract?->currentPlate ?? '-',
        ];

        $pdf    = PDF::loadView('creditNote', $dataE);
        $canvas = $pdf->getDomPDF()->get_canvas();
        // $contenidoAncho = $canvas->get_width();
        $contenidoAlto = $canvas->get_height();

        $tipoDocumento = "07";
        $fileName      = '20605597484-' . $tipoDocumento . '-' . $object->number . '.pdf'; // Formato del nombre
        $fileName      = str_replace(' ', '_', $fileName);                                 // Reemplazar espacios con guiones bajos
        return $pdf->stream($fileName);

    }
    public function guiamanual()
    {

        $pdf = Pdf::loadView('guiamanual');
        return $pdf->stream('guia.pdf');
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/manifiesto/{id}",
     *     summary="Exportar Manifiesto",
     *     tags={"Report"},
     *     description="Genera y descarga una Manifiesto en formato PDF",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la Programación",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Manifiesto en formato PDF",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en los datos")
     *         )
     *     )
     * )
     */

    public function manifiesto(Request $request, $id)
    {

        $object = Programming::find($id);

        if (! $object) {
            abort(404);
        }

        $branch_office_id = auth()->user()->worker->branchOffice_id ?? 1;

        $object = Programming::with('tract.photos', 'platform.photos', 'tract',
            'origin', 'destination', 'detailsWorkers', 'detailsWorkers.worker',
            'detailReceptions', 'carrierGuides.reception.details'
        )->find($id);
        $tract = $object->tract;
                                                                          // Obtén los IDs de las guías desde la relación
        $relationGuides = $object->carrierGuides->pluck('id')->toArray(); // IDs de las guías desde la relación

// Obtén las guías directamente desde la tabla, excluyendo los eliminados
        $dbGuides = DB::table('carrier_by_programmings')
            ->where('programming_id', $id)
            ->whereNull('deleted_at')   // Excluir registros eliminados
            ->pluck('carrier_guide_id') // Obtén solo los IDs de las guías
            ->toArray();

// Combina ambas fuentes y elimina duplicados
        $uniqueGuidesIds = collect($relationGuides)
            ->merge($dbGuides)
            ->unique()   // Elimina duplicados
            ->values()   // Re-indexa los valores
            ->toArray(); // Convierte a un array

        // Obtén los detalles completos de las guías únicas
        $uniqueGuides = CarrierGuide::whereIn('id', $uniqueGuidesIds)->get();

        // Reemplaza en `$object`
        $object['carrierGuides'] = $uniqueGuides;
        // return view('manifiesto', compact('object'))->render();

        $titulo = 'MANIFIESTO DE CARGA';

        // return view('manifiesto', compact('object'))->render();

        Bitacora::create([
            'user_id'     => Auth::id(),     // ID del usuario que realiza la acción
            'record_id'   => $object->id,    // El ID del usuario afectado
            'action'      => 'GET',          // Acción realizada
            'table_name'  => 'programmings', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Descargó pdf de Manifiesto', // Descripción de la acción
            'ip_address'  => $request->ip(),                // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),         // Información sobre el navegador/dispositivo
        ]);

        $html = view('manifiesto', compact('object', 'titulo', 'tract'))->render();

        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        return $dompdf->stream('Manfiesto_' . now() . '.pdf');
    }

    public function manifiestoConductor(Request $request, $id)
    {

        $object = Programming::find($id);

        if (! $object) {
            abort(404);
        }

        $branch_office_id = auth()->user()->worker->branchOffice_id ?? 1;

        // $object = Programming::with('tract.photos', 'platform.photos',
        //     'origin', 'destination', 'detailsWorkers', 'detailsWorkers.worker',
        //     'detailReceptions', 'carrierGuides.reception.details'
        // )->find($id);
        $object = Programming::with([
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailsWorkers.worker',
            'detailReceptions',
            'carrierGuides.reception', // Carga la relación de recepciones
        ])->find($id);

        $relationGuides = $object->carrierGuides->pluck('id')->toArray(); // IDs de las guías desde la relación

        // Obtén las guías directamente desde la tabla, excluyendo los eliminados
        $dbGuides = DB::table('carrier_by_programmings')
            ->where('programming_id', $id)
            ->whereNull('deleted_at')   // Excluir registros eliminados
            ->pluck('carrier_guide_id') // Obtén solo los IDs de las guías
            ->toArray();

        // Combina ambas fuentes y elimina duplicados
        $uniqueGuidesIds = collect($relationGuides)
            ->merge($dbGuides)
            ->unique()   // Elimina duplicados
            ->values()   // Re-indexa los valores
            ->toArray(); // Convierte a un array

        // Obtén los detalles completos de las guías únicas
        $uniqueGuides = CarrierGuide::whereIn('id', $uniqueGuidesIds)->get();

        // Reemplaza en `$object`
        $object['carrierGuides'] = $uniqueGuides;

        // Iterar sobre las guías de carga y modificar los campos en las recepciones
        foreach ($object->carrierGuides as $carrierGuide) {
            if ($carrierGuide->reception && $carrierGuide->reception->conditionPay === 'Créditos') {
                $carrierGuide->reception->paymentAmount = -1;
                $carrierGuide->reception->creditAmount  = -1;
                $carrierGuide->reception->debtAmount    = -1;
            }
        }

        $titulo = 'MANIFIESTO DE CARGA CONDUCTOR';

        // return view('manifiesto', compact('object'))->render();

        Bitacora::create([
            'user_id'     => Auth::id(),     // ID del usuario que realiza la acción
            'record_id'   => $object->id,    // El ID del usuario afectado
            'action'      => 'GET',          // Acción realizada
            'table_name'  => 'programmings', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Descargó pdf de Manifiesto Conductor', // Descripción de la acción
            'ip_address'  => $request->ip(),                          // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),                   // Información sobre el navegador/dispositivo
        ]);

        $html = view('manifiesto', compact('object', 'titulo'))->render();

        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        return $dompdf->stream('Manfiesto_' . now() . '.pdf');
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/reportCaja",
     *     summary="Exportar Reporte Caja",
     *     tags={"Report"},
     *     description="Genera y descarga una Reporte Caja Aperturada en formato PDF",
     *     security={{"bearerAuth":{}}},

     *     @OA\Response(
     *         response=200,
     *         description="Reporte Caja en formato PDF",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en los datos")
     *         )
     *     )
     * )
     */

    public function reportCaja(Request $request)
    {

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }

        $movCaja = Moviment::where('status', 'Activa')
            ->where('paymentConcept_id', 1)
            ->where('box_id', $box_id)
            ->first();

        if (! $box) {
            return response()->json([
                "message" => "Este usuario no tiene caja",
            ], 422);
        }

        $data = [];
        if ($movCaja) {

            $movCajaAperturada = Moviment::where('id', $movCaja->id)
                ->where('paymentConcept_id', 1)
                ->where('box_id', $box_id)
                ->first();

            if (! $movCajaAperturada) {
                return response()->json([
                    "message" => "Movimiento de Apertura no encontrado",
                ], 404);
            }

            $movCajaCierre = Moviment::where('id', '>', $movCajaAperturada->id)
                ->where('paymentConcept_id', 2)
                ->where('box_id', $box_id)
                ->orderBy('id', 'asc')->first();

            if ($movCajaCierre == null) {
                //CAJA ACTIVA
                $query = Moviment::select(['*', DB::raw('(SELECT obtenerFormaPagoPorCaja(moviments.id)) AS formaPago')])
                    ->where('id', '>=', $movCajaAperturada->id)
                    ->where('box_id', $box_id)
                    ->where('movType', 'Caja')
                    ->orderBy('id', 'desc')
                    ->with(['paymentConcept', 'person', 'user.worker.person']);

                // Ejecutar la consulta paginada
                $movimientosCaja = $query->paginate(15);

                $movimientosCaja = [
                    'current_page'   => $movimientosCaja->currentPage(),
                    'data'           => $movimientosCaja->items(), // Los datos paginados
                    'total'          => $movimientosCaja->total(), // El total de registros
                    'first_page_url' => $movimientosCaja->url(1),
                    'from'           => $movimientosCaja->firstItem(),
                    'next_page_url'  => $movimientosCaja->nextPageUrl(),
                    'path'           => $movimientosCaja->path(),
                    'per_page'       => $movimientosCaja->perPage(),
                    'prev_page_url'  => $movimientosCaja->previousPageUrl(),
                    'to'             => $movimientosCaja->lastItem(),
                ];

                $resumenCaja = Moviment::selectRaw('
              COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.total ELSE 0 END), 0.00) as total_ingresos,
              COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.total ELSE 0 END), 0.00) as total_egresos,
              COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_ingresos,
              COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_egresos,
              COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_ingresos,
              COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_egresos,
              COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_ingresos,
              COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_egresos,
              COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_ingresos,
              COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_egresos,
              COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_ingresos,
              COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_egresos')
                    ->leftJoin('payment_concepts as cp', 'moviments.paymentConcept_id', '=', 'cp.id')
                    ->where('moviments.id', '>=', $movCajaAperturada->id)
                    ->where('moviments.box_id', $box_id)
                    ->where('moviments.movType', 'Caja')
                    ->first();

                $movCajaCierreArray = null;
            } else {
                $movimientosCaja = Moviment::select(['*', DB::raw('(SELECT obtenerFormaPagoPorCaja(moviments.id)) AS formaPago')])
                    ->where('id', '>=', $movCajaAperturada->id)
                // ->where('branchOffice_id', $movCajaAperturada->branchOffice_id)
                    ->where('id', '<', $movCajaCierre->id)
                    ->where('movType', 'Caja')
                    ->orderBy('id', 'desc')
                    ->with(['paymentConcept', 'person', 'user.worker.person'])
                    ->simplePaginate();

                $resumenCaja = Moviment::selectRaw('
                  COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.total ELSE 0 END), 0.00) as total_ingresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.total ELSE 0 END), 0.00) as total_egresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_ingresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_egresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_ingresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_egresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_ingresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_egresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_ingresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_egresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_ingresos,
                  COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_egresos')
                    ->leftJoin('payment_concepts as cp', 'moviments.paymentConcept_id', '=', 'cp.id')
                    ->where('moviments.box_id', $box_id)
                    ->where('moviments.id', '>=', $movCajaAperturada->id)
                    ->where('moviments.id', '<', $movCajaCierre->id)
                    ->where('moviments.movType', 'Caja')
                    ->whereNull('moviments.deleted_at')

                    ->first();

            }

            $data = [

                'MovCajaApertura' => $movCajaAperturada,
                'MovCajaCierre'   => $movCajaCierre,
                'MovCajaInternos' => $movimientosCaja,

                "resumenCaja"     => $resumenCaja ?? null,
            ];
        } else {
            abort(404, 'MovCajaCierre not found');
        }

        $html = view('reportCaja', compact('data'))->render();

        // Crear una nueva instancia de Dompdf
        // $html = View::make('guia', $object)->render();

        // Configurar DomPDF
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Descargar el PDF con un nombre de archivo dinámico basado en el ID
        return $dompdf->stream('ReporteCaja_' . now() . '.pdf');
        // $pdf = PDF::loadView('reportCaja', compact('data'));
        // return $pdf->stream('guia.pdf');
    }
    // public function documento($id = 1)
    // {
    //     $pdf = Pdf::loadView('export-pdf-ticket');
    //     return $pdf->stream('documento.pdf');

    // }

    public function documento(Request $request, $idMov = 10)
    {
        $Movimiento = Moviment::with(['reception',
            'reception.firstCarrierGuide.origin', 'reception.firstCarrierGuide.destination'])->find($idMov);
        $linkRevisarFact = false;

        $pointSend        = strtoupper($Movimiento->reception?->pointSender?->name ?? '');
        $pointDestination = strtoupper($Movimiento->reception?->pointDestination?->name ?? '');

        $ruta              = $pointSend . ' - ' . $pointDestination;
        $receptionDetails  = $Movimiento?->reception?->details() ?? [];
        $descriptionString = '';
        if ($receptionDetails != []) {
            $descriptions      = $receptionDetails?->pluck('description')?->toArray() ?? []; // Obtiene todas las descripciones
            $descriptionString = implode(', ', $descriptions);                               // Une las descripciones con comas
        }

        // Inicializar el array de detalles
        $detalles = [];
        if ($Movimiento) {
            $productList = $Movimiento->detalles;
        }

        // Inicializar el array de detalles
        $detalles = [];

        if (($productList) != []) {
            foreach ($productList as $producto) {
                // Buscar la recepción si existe 'reception_id', si no asigna null
                // $reception = Reception::find($producto->reception_id ?? null);

                // Usar operadores de navegación segura y coalescencia para evitar errores y asignar valores por defecto
                $detalles[] = [
                    "descripcion"              => $producto->description ?? '-',
                    "os"                       => $producto->os ?? '-',
                    "guia"                     => $producto->guia ?? '-',
                    "placaVehiculo"            => $producto->placa ?? '-',
                    "cantidad"                 => $producto->cantidad ?? 1, // Cantidad fija (es un servicio)
                    "precioventaunitarioxitem" => $producto->precioVenta ?? 0,
                ];
            }
        }

        $tipoDocumento = '';
        $num           = $Movimiento->sequentialNumber;
        if (strpos($num, 'B') === 0) {
            $tipoDocumento   = 'BOLETA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'F') === 0) {
            $tipoDocumento   = 'FACTURA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'T') === 0) {
            $tipoDocumento   = 'TICKET ELECTRÓNICO';
            $linkRevisarFact = false;
        } else {
            abort(404);
        }

        $dateTime       = Carbon::now()->format('Y-m-d H:i:s');
        $personaCliente = Person::withTrashed()->find($Movimiento->person_id);
        $fechaInicio    = $Movimiento->created_at;
        $rucOdni        = $personaCliente->documentNumber;

        if (strtoupper($personaCliente->typeofDocument) != 'DNI') {
            $nombreCliente = $personaCliente->businessName;
            $direccion     = $personaCliente->fiscalAddress ?? '-';
        } else {
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $direccion     = $personaCliente->address ?? '-';
        }

        if ($personaCliente->names == 'VARIOS') {
            $nombreCliente = "VARIOS";
            if (strpos($num, 'B') === 0) {
                $rucOdni = '11111111';
            } elseif (strpos($num, 'F') === 0) {
                $rucOdni = '11111111111';
            }
        }

        $dataE = [
            'title'           => 'DOCUMENTO DE PAGO',
            'ruc_dni'         => $rucOdni,
            'direccion'       => $direccion,
            'idMovimiento'    => $Movimiento->id,
            'tipoElectronica' => $tipoDocumento,
            'typePayment'     => $Movimiento->typePayment ?? '-',
            'numeroVenta'     => $num,
            'porcentaje'      => $Movimiento->percentDetraction,

            'fechaemision'    => $Movimiento->created_at->format('Y-m-d'),
            'cliente'         => $nombreCliente,
            'detalles'        => $detalles,
            'cuentas'         => $Movimiento->installments,
            'vuelto'          => '0.00',
            'totalPagado'     => $Movimiento->total,
            'linkRevisarFact' => $linkRevisarFact,
            'formaPago'       => $Movimiento->formaPago ?? '-',
            'fechaInicio'     => $fechaInicio,
            'guia'            => $Movimiento->reception?->firstCarrierGuide?->numero ?? '-',
            'placa'           => $Movimiento->reception->id ?? '-',
            'typeSale'        => $Movimiento->typeSale ?? '-',
            'codeDetraction'  => $Movimiento->codeDetraction ?? '-',
        ];

        // Utiliza el método loadView() directamente en la fachada PDF
        $pdf           = PDF::loadView('export-pdf-ticket', $dataE);
        $canvas        = $pdf->getDomPDF()->get_canvas();
        $contenidoAlto = $canvas->get_height();
        $pdf->setPaper([15, 5, 172, 599], 'portrait');

        $fileName = 'DOCUMENTO DE PAGO- ' . $dateTime . '.pdf';
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->stream($fileName);
    }

    public function documentoA4(Request $request, $idMov = 0)
    {
        $Movimiento = Moviment::with(['reception',
            'reception.firstCarrierGuide.origin', 'reception.firstCarrierGuide.destination'])->find($idMov);
        $linkRevisarFact = false;

        $pointSend        = strtoupper($Movimiento->reception?->pointSender?->name ?? '');
        $pointDestination = strtoupper($Movimiento->reception?->pointDestination?->name ?? '');

        $ruta              = $pointSend . ' - ' . $pointDestination;
        $receptionDetails  = $Movimiento?->reception?->details() ?? [];
        $descriptionString = '';
        if ($receptionDetails != []) {
            $descriptions      = $receptionDetails?->pluck('description')?->toArray() ?? []; // Obtiene todas las descripciones
            $descriptionString = implode(', ', $descriptions);                               // Une las descripciones con comas
        }
        $productList = [];
        // if ($Movimiento) {
        //     $productList = json_decode($Movimiento->productList, true) ?? [];
        // }
        if ($Movimiento) {
            $productList = $Movimiento->detalles;
        }
        // Inicializar el array de detalles
        $detalles = [];

        if (($productList) != []) {
            foreach ($productList as $producto) {
                // Buscar la recepción si existe 'reception_id', si no asigna null
                // $reception = Reception::find($producto->reception_id ?? null);

                // Usar operadores de navegación segura y coalescencia para evitar errores y asignar valores por defecto
                $detalles[] = [
                    "descripcion"              => $producto->description ?? '-',
                    "os"                       => $producto->os ?? '-',
                    "guia"                     => $producto->guia ?? '-',
                    "placaVehiculo"            => ($producto->placa === '-')
                    ? CarrierGuide::where('numero', $producto->guia)->value('placa') ?? '-'
                    : $producto->placa,
                    "cantidad"                 => $producto->cantidad ?? 1, // Cantidad fija (es un servicio)
                    "precioventaunitarioxitem" => $producto->precioVenta ?? 0,
                ];
            }
        }

        $tipoDocumento = '';
        $num           = $Movimiento->sequentialNumber;
        if (strpos($num, 'B') === 0) {
            $tipoDocumento   = 'BOLETA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'F') === 0) {
            $tipoDocumento   = 'FACTURA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'T') === 0) {
            $tipoDocumento   = 'TICKET ELECTRÓNICO';
            $linkRevisarFact = false;
        } else {
            abort(404);
        }
        $dateTime       = Carbon::now()->format('Y-m-d H:i:s');
        $personaCliente = Person::withTrashed()->find($Movimiento->person_id);
        $fechaInicio    = $Movimiento->created_at;
        $rucOdni        = $personaCliente->documentNumber;
        $direccion      = "";
        if (strtoupper($personaCliente->typeofDocument) != 'DNI') {
            $nombreCliente = $personaCliente->businessName;

            $direccion = $personaCliente->fiscalAddress ?? '-';
        } else {
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $direccion     = $personaCliente->address ?? '-';
        }

        if ($personaCliente->names == 'VARIOS') {
            $nombreCliente = "VARIOS";
            if (strpos($num, 'B') === 0) {
                $rucOdni = '11111111';

            } elseif (strpos($num, 'F') === 0) {
                $rucOdni = '11111111111';

            }
        }

        // Generar el código QR
        // $qrCode = new QrCode($num);
        // $writer = new PngWriter();
        // $qrImage = $writer->write($qrCode);
        // // Generar un nombre único para el archivo
        // $fileName = $num . '.png';
        // $filePath = 'qr_images/' . $fileName;
        // if (Storage::disk('public')->exists($filePath)) {
        //     $fileName = $num . '.png';
        //     $filePath = 'qr_images/' . $fileName;
        // }
        // Storage::disk('public')->put($filePath, $qrImage->getString());
        $dataE = [
            'title'           => 'DOCUMENTO DE PAGO',
            'ruc_dni'         => $rucOdni,
            'direccion'       => $direccion,
            'idMovimiento'    => $Movimiento->id,
            'tipoElectronica' => $tipoDocumento,
            'typePayment'     => $Movimiento->typePayment ?? '-',
            'numeroVenta'     => $num,
            'porcentaje'      => $Movimiento->percentDetraction,
            'fechaemision'    => $Movimiento->created_at->format('Y-m-d'),
            'cliente'         => $nombreCliente,
            'detalles'        => $detalles,
            'cuentas'         => $Movimiento->installments,
            'montodetraccion' => $Movimiento->monto_detraction,
            'valueref'        => $Movimiento->value_ref,
            'isvalueref'      => $Movimiento->isValue_ref,
            'montoneto'       => $Movimiento->monto_neto,
            'vuelto'          => '0.00',
            'totalPagado'     => $Movimiento->total,
            'linkRevisarFact' => $linkRevisarFact,
            'formaPago'       => $Movimiento->formaPago ?? '-',
            'fechaInicio'     => $fechaInicio,
            'guia'            => $Movimiento->reception?->firstCarrierGuide?->numero ?? '-',
            'placa'           => $Movimiento->reception->id ?? '-',
            'typeSale'        => $Movimiento->typeSale ?? '-',
            'codeDetraction'  => $Movimiento->codeDetraction ?? '-',
            'observation'  => $Movimiento->observation ?? '',
        ];
        // Utiliza el método loadView() directamente en la fachada PDF
        $pdf    = PDF::loadView('documentoA4', $dataE);
        $canvas = $pdf->getDomPDF()->get_canvas();
        // $contenidoAncho = $canvas->get_width();

        $qrUrl = $this->getArchivosDocument($Movimiento->id, 'venta');

// Verificamos si la URL es válida (no es null o vacía)
        // if ($qrUrl) {
        //     // Si la URL está disponible, podemos agregar la imagen al PDF
        //     $canvas->image($qrUrl, 480, 730, 70, 80); // Ajusta las coordenadas y el tamaño según sea necesario
        // }

        $contenidoAlto = $canvas->get_height();
        if (strpos($num, 'B') === 0) {
            $tipoDocumento = '01'; // Boleta
        } elseif (strpos($num, 'F') === 0) {
            $tipoDocumento = '03'; // Factura
        } elseif (strpos($num, 'T') === 0) {
            $tipoDocumento = '00'; // Ticket
        }

        $fileName = '20605597484-' . $tipoDocumento . '-' . $num . '.pdf'; // Formato del nombre
        $fileName = str_replace(' ', '_', $fileName);                      // Reemplazar espacios con guiones bajos
        return $pdf->stream($fileName);
    }
    public function documentoA4F2(Request $request, $idMov = 0)
    {
        $Movimiento = Moviment::with(['reception',
            'reception.firstCarrierGuide.origin', 'reception.firstCarrierGuide.destination'])->find($idMov);
        $linkRevisarFact = false;

        $pointSend        = strtoupper($Movimiento->reception?->pointSender?->name ?? '');
        $pointDestination = strtoupper($Movimiento->reception?->pointDestination?->name ?? '');

        $ruta              = $pointSend . ' - ' . $pointDestination;
        $receptionDetails  = $Movimiento?->reception?->details() ?? [];
        $descriptionString = '';
        if ($receptionDetails != []) {
            $descriptions      = $receptionDetails?->pluck('description')?->toArray() ?? []; // Obtiene todas las descripciones
            $descriptionString = implode(', ', $descriptions);                               // Une las descripciones con comas
        }
        $productList = [];
        // if ($Movimiento) {
        //     $productList = json_decode($Movimiento->productList, true) ?? [];
        // }
        if ($Movimiento) {
            $productList = $Movimiento->detalles;
        }
        // Inicializar el array de detalles
        $detalles = [];

        if (($productList) != []) {
            foreach ($productList as $producto) {
                // Buscar la recepción si existe 'reception_id', si no asigna null
                // $reception = Reception::find($producto->reception_id ?? null);

                // Usar operadores de navegación segura y coalescencia para evitar errores y asignar valores por defecto
                $detalles[] = [
                    "descripcion"              => $producto->description ?? '-',
                    "os"                       => $producto->os ?? '-',
                    "guia"                     => $producto->guia ?? '-',
                    "placaVehiculo"            => ($producto->placa === '-')
                    ? CarrierGuide::where('numero', $producto->guia)->value('placa') ?? '-'
                    : $producto->placa,
                    "cantidad"                 => $producto->cantidad ?? 1, // Cantidad fija (es un servicio)
                    "precioventaunitarioxitem" => $producto->precioVenta ?? 0,
                ];
            }
        }

        $tipoDocumento = '';
        $num           = $Movimiento->sequentialNumber;
        if (strpos($num, 'B') === 0) {
            $tipoDocumento   = 'BOLETA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'F') === 0) {
            $tipoDocumento   = 'FACTURA ELECTRÓNICA';
            $linkRevisarFact = true;
        } elseif (strpos($num, 'T') === 0) {
            $tipoDocumento   = 'TICKET ELECTRÓNICO';
            $linkRevisarFact = false;
        } else {
            abort(404);
        }
        $dateTime       = Carbon::now()->format('Y-m-d H:i:s');
        $personaCliente = Person::withTrashed()->find($Movimiento->person_id);
        $fechaInicio    = $Movimiento->created_at;
        $rucOdni        = $personaCliente->documentNumber;
        $direccion      = "";
        if (strtoupper($personaCliente->typeofDocument) != 'DNI') {
            $nombreCliente = $personaCliente->businessName;

            $direccion = $personaCliente->fiscalAddress ?? '-';
        } else {
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $direccion     = $personaCliente->address ?? '-';
        }

        if ($personaCliente->names == 'VARIOS') {
            $nombreCliente = "VARIOS";
            if (strpos($num, 'B') === 0) {
                $rucOdni = '11111111';

            } elseif (strpos($num, 'F') === 0) {
                $rucOdni = '11111111111';

            }
        }

        // Generar el código QR
        // $qrCode = new QrCode($num);
        // $writer = new PngWriter();
        // $qrImage = $writer->write($qrCode);
        // // Generar un nombre único para el archivo
        // $fileName = $num . '.png';
        // $filePath = 'qr_images/' . $fileName;
        // if (Storage::disk('public')->exists($filePath)) {
        //     $fileName = $num . '.png';
        //     $filePath = 'qr_images/' . $fileName;
        // }
        // Storage::disk('public')->put($filePath, $qrImage->getString());
        $dataE = [
            'title'           => 'DOCUMENTO DE PAGO',
            'ruc_dni'         => $rucOdni,
            'direccion'       => $direccion,
            'idMovimiento'    => $Movimiento->id,
            'tipoElectronica' => $tipoDocumento,
            'typePayment'     => $Movimiento->typePayment ?? '-',
            'numeroVenta'     => $num,
            'porcentaje'      => $Movimiento->percentDetraction,
            'fechaemision'    => $Movimiento->created_at->format('Y-m-d'),
            'cliente'         => $nombreCliente,
            'detalles'        => $detalles,
            'cuentas'         => $Movimiento->installments,
            'montodetraccion' => $Movimiento->monto_detraction,
            'valueref'        => $Movimiento->value_ref,
            'isvalueref'      => $Movimiento->isValue_ref,
            'montoneto'       => $Movimiento->monto_neto,
            'vuelto'          => '0.00',
            'totalPagado'     => $Movimiento->total,
            'linkRevisarFact' => $linkRevisarFact,
            'formaPago'       => $Movimiento->formaPago ?? '-',
            'fechaInicio'     => $fechaInicio,
            'guia'            => $Movimiento->reception?->firstCarrierGuide?->numero ?? '-',
            'placa'           => $Movimiento->reception->id ?? '-',
            'typeSale'        => $Movimiento->typeSale ?? '-',
            'codeDetraction'  => $Movimiento->codeDetraction ?? '-',
        ];
        // Utiliza el método loadView() directamente en la fachada PDF
        $pdf    = PDF::loadView('documentoA4F2', $dataE);
        $canvas = $pdf->getDomPDF()->get_canvas();
        // $contenidoAncho = $canvas->get_width();

        $qrUrl = $this->getArchivosDocument($Movimiento->id, 'venta');

// Verificamos si la URL es válida (no es null o vacía)
        // if ($qrUrl) {
        //     // Si la URL está disponible, podemos agregar la imagen al PDF
        //     $canvas->image($qrUrl, 480, 730, 70, 80); // Ajusta las coordenadas y el tamaño según sea necesario
        // }

        $contenidoAlto = $canvas->get_height();
        if (strpos($num, 'B') === 0) {
            $tipoDocumento = '01'; // Boleta
        } elseif (strpos($num, 'F') === 0) {
            $tipoDocumento = '03'; // Factura
        } elseif (strpos($num, 'T') === 0) {
            $tipoDocumento = '00'; // Ticket
        }

        $fileName = '20605597484-' . $tipoDocumento . '-' . $num . '.pdf'; // Formato del nombre
        $fileName = str_replace(' ', '_', $fileName);                      // Reemplazar espacios con guiones bajos
        return $pdf->stream($fileName);
    }
    public function getArchivosDocument($idventa, $typeDocument)
    {
        $funcion = 'buscarNumeroSolicitud';
        $url     = 'https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=' . $funcion . '&typeDocument=' . $typeDocument;

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

                // Retornamos la URL de la imagen
                return 'https://develop.garzasoft.com:81/transporteFacturadorZip/ficheros/' . $pngFile;
            }
        }

        // Si no se encontró la imagen, devolvemos null
        return null;
    }

    public function reporteReception(Request $request, $idMov = 0)
    {
        $recepcion = Reception::with(['user', 'origin', 'office', 'destination',
            'recipient',
            'seller', 'firstCarrierGuide', 'details'])
            ->find($idMov);

        $dateTime = Carbon::now()->format('Y-m-d H:i:s');

        $dataE = [
            'title'     => 'TICKET RECEPCIÓN',
            'recepcion' => $recepcion,
        ];

        // Cargar la vista con los datos
        $pdf = PDF::loadView('recepcion-ticket', $dataE);

                                                            // Configurar el tamaño del papel a un cuadrado de 11 cm por 11 cm
        $pdf->setPaper([0, 0, 312.28, 312.28], 'portrait'); // 11 cm por cada lado

        $fileName = 'RECPECIÓN_' . $dateTime . '.pdf';
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->stream($fileName);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/reportCajaExcel",
     *     summary="Exportar Reporte Caja a Excel",
     *     tags={"Report"},
     *     description="Genera y descarga un Reporte de Caja Aperturada en formato EXCEL",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reporte de Caja en formato EXCEL",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en los datos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Movimiento de Caja no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Movimiento de Caja no encontrado")
     *         )
     *     )
     * )
     */
    public function reporteIngresosExcel(Request $request)
    {

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }
        $moviment_id = $request->input('moviment_id');

        $query = Moviment::where('paymentConcept_id', 1)
            ->where('box_id', $box_id);

        // Solo agregar condición de 'Activa' si no existe moviment_id
        if (empty($moviment_id)) {
            $query->where('status', 'Activa');

        } else {
            $query->where('id', $moviment_id);
        }

        // Ejecutar la consulta y obtener el primer resultado
        $movCaja = $query->first();

        $data = [];
        if ($movCaja) {

            $movCajaAperturada = Moviment::where('id', $movCaja->id)->where('paymentConcept_id', 1)
                ->first();

            if (! $movCajaAperturada) {
                return response()->json([
                    "message" => "Movimiento de Apertura no encontrado",
                ], 404);
            }

            $movCajaCierre = Moviment::where('id', '>', $movCajaAperturada->id)
                ->where('paymentConcept_id', 2)
                ->where('box_id', $box_id)
                ->where('movType', 'Caja')
                ->orderBy('id', 'asc')->first();

            if ($movCajaCierre == null) {
                // CAJA ACTIVA
                $query = Moviment::select(['*', DB::raw('(SELECT obtenerFormaPagoPorCaja(moviments.id)) AS formaPago')])
                    ->where('id', '>=', $movCajaAperturada->id)
                    ->where('box_id', $box_id)
                    ->where('movType', 'Caja')
                    ->orderBy('id', 'desc')
                    ->with(['paymentConcept', 'person', 'user.worker.person']);

                // Ejecutar la consulta paginada
                $movimientosCaja = $query->paginate(150);

                $movimientosCaja = [
                    'current_page'   => $movimientosCaja->currentPage(),
                    'data'           => $movimientosCaja->items(), // Los datos paginados
                    'total'          => $movimientosCaja->total(), // El total de registros
                    'first_page_url' => $movimientosCaja->url(1),
                    'from'           => $movimientosCaja->firstItem(),
                    'next_page_url'  => $movimientosCaja->nextPageUrl(),
                    'path'           => $movimientosCaja->path(),
                    'per_page'       => $movimientosCaja->perPage(),
                    'prev_page_url'  => $movimientosCaja->previousPageUrl(),
                    'to'             => $movimientosCaja->lastItem(),
                ];

            } else {
                $movimientosCaja = Moviment::select(['*', DB::raw('(SELECT obtenerFormaPagoPorCaja(moviments.id)) AS formaPago')])
                    ->where('id', '>=', $movCajaAperturada->id)
                // ->where('branchOffice_id', $movCajaAperturada->branchOffice_id)
                    ->where('box_id', $box_id)
                    ->where('movType', 'Caja')
                    ->where('id', '<=', $movCajaCierre->id)
                    ->orderBy('id', 'desc')
                    ->with(['paymentConcept', 'person', 'user.worker.person'])
                    ->paginate(150);

                $movimientosCaja = [
                    'current_page'   => $movimientosCaja->currentPage(),
                    'data'           => $movimientosCaja->items(), // Los datos paginados
                    'total'          => $movimientosCaja->total(), // El total de registros
                    'first_page_url' => $movimientosCaja->url(1),
                    'from'           => $movimientosCaja->firstItem(),
                    'next_page_url'  => $movimientosCaja->nextPageUrl(),
                    'path'           => $movimientosCaja->path(),
                    'per_page'       => $movimientosCaja->perPage(),
                    'prev_page_url'  => $movimientosCaja->previousPageUrl(),
                    'to'             => $movimientosCaja->lastItem(),
                ];
            }

            $data = [
                'MovCajaInternos' => $movimientosCaja,
            ];
        } else {
            abort(404, 'MovCajaCierre not found');
        }

        // Preparar datos para exportación
        $exportData = [];

        foreach ($data['MovCajaInternos']['data'] as $moviment) {

            $nombresPersona = '';
            if (strtoupper($moviment->person->typeofDocument) != 'RUC') {
                $nombresPersona = $moviment->person->names . ' ' . $moviment->person->fatherSurname;
            } else {
                $nombresPersona = $moviment->person->businessName ?? '';
            }
            $nombresPersona = $moviment->person->documentNumber . ' | ' . $nombresPersona;

            $factor       = $moviment->typeDocument == 'Ingreso' ? 1 : -1;
            $exportData[] = [
                'Date'    => (string) ($moviment->created_at->format('Y-m-d H:i:s')),
                'Numero'  => (string) ($moviment->sequentialNumber ?? ''),
                'Tipo'    => (string) ($moviment->typeDocument ?? '-'),
                'Concept' => (string) ($moviment->paymentConcept->name ?? ''),
                'Person'  => (string) ($nombresPersona ?? ''),
                'Guias'   => (string) implode(',', $moviment?->movVenta?->detalles()->pluck('guia')->toArray() ?? []),

                'Cash'    => (string) (($moviment->cash * $factor) ?? '0'),
                'Card'    => (string) (($moviment->card * $factor) ?? '0'),
                'Deposit' => (string) (($moviment->deposit * $factor) ?? '0'),
                'Yape'    => (string) (($moviment->yape * $factor) ?? '0'),
                'Plin'    => (string) (($moviment->plin * $factor) ?? '0'),
                'Total'   => (string) (($moviment->total * $factor) ?? '0'),
                'Comment' => (string) ($moviment->comment ?? ''),

            ];

        }

        return Excel::download(new CajaExport($exportData), 'MovimientosCaja.xlsx');
    }

    public function reporteCuentasPorCobrarExcel(Request $request)
    {
        $status           = $request->input('status') ?? '';
        $personId         = $request->input('person_id') ?? '';
        $start            = $request->input('start') ?? '';            // Fecha de inicio de Installment
        $end              = $request->input('end') ?? '';              // Fecha de fin de Installment
        $sequentialNumber = $request->input('sequentialNumber') ?? ''; // Este campo está dentro de Moviment

        $branchOffice_id = $request->input('branchOffice_id') ?? '';
        if ($branchOffice_id && is_numeric($branchOffice_id)) {
            $branchOffice = BranchOffice::find($branchOffice_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }

        $box_id = $request->input('box_id') ?? '';
        if ($box_id != '') {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }

        // Actualizar estado de cuotas vencidas
        Installment::where('status', 'Pendiente')
            ->where('date', '<', now())        // Si la fecha actual es mayor que la fecha de vencimiento
            ->update(['status' => 'Vencido']); // Actualiza el estado a "Vencido"

        // Iniciar la consulta base
        $query = Installment::with(['moviment', 'moviment.person', 'payInstallments', 'payInstallments.bank']);

        if (! empty($status) && $status != '""') {
            $query->where('status', $status);
        }

        if (! empty($personId)) {
            $query->whereHas('moviment', function ($q) use ($personId) {
                $q->where('person_id', $personId);
            });
        }

        if (! empty($branchOffice_id)) {
            $query->whereHas('moviment', function ($q) use ($branchOffice_id) {
                $q->where('branchOffice_id', $branchOffice_id);
            });
        }

        if (! empty($box_id)) {
            $query->whereHas('moviment', function ($q) use ($box_id) {
                $q->where('box_id', $box_id);
            });
        }

        if (! empty($sequentialNumber)) {
            $query->whereHas('moviment', function ($q) use ($sequentialNumber) {
                $q->where('sequentialNumber', 'like', '%' . $sequentialNumber . '%');
            });
        }
        if (! empty($start)) {
            // $query->where('date', '>=', $start);
             $query->whereHas('moviment', function ($q) use ($start) {
                $q->where('paymentDate', '>=',$start);
            });
        }
        if (! empty($end)) {
            // $query->where('date', '<=', $end);
              $query->whereHas('moviment', function ($q) use ($end) {
                $q->where('paymentDate','<=', $end);
            });
        }
        // Calcular la suma total de totalDebt
        $totalDebtSum = $query->sum('totalDebt');

        // Obtener los registros filtrados con paginación
        $data = $query->orderBy('id', 'desc')->take(500)->get();

        // Inicializar las variables de totales
        $total           = 0;
        $totalDebt       = 0;
        $totalPagado     = 0;
        $totalSaldo      = 0;
        $totalDetraccion = 0;
        $totalSaldoNeto  = 0;

        $exportData = [];
        $i          = 1;
        foreach ($data as $item) {
            // Verificar si la cuota (installment) existe
            $installment = Installment::find($item->id);
            if (! $installment) {
                continue; // Si no existe la cuota, pasar al siguiente item
            }

            // Validar si existe la relación `moviment`
            $moviment = $installment->moviment ?? null;

            // Determinar el tipo de documento basado en el número secuencial
            $tipoDoc = '';
            if ($moviment && isset($moviment->sequentialNumber)) {
                if (str_starts_with($moviment->sequentialNumber, 'F')) {
                    $tipoDoc = 'Factura Venta';
                } elseif (str_starts_with($moviment->sequentialNumber, 'B')) {
                    $tipoDoc = 'Boleta Venta';
                }
            }

            // Validar el cliente y construir el nombre
            $personaCliente = $moviment->person ?? null;
            $nombreCliente  = '-';
            if ($personaCliente) {
                $nombreCliente = strtoupper($personaCliente->typeofDocument) != 'DNI'
                ? ($personaCliente->businessName ?? '-')
                : trim(($personaCliente->names ?? '') . ' ' . ($personaCliente->fatherSurname ?? '') . ' ' . ($personaCliente->motherSurname ?? ''));
            }

            // Calcular la detracción
            $totalDetalle     = $item->total ?? 0;
            $totalDetalleDebt = $item->totalDebt ?? 0;
            $montoDetraccion  = 0;

            if ($moviment && isset($moviment->codeDetraction)) {
                if ($moviment->codeDetraction == '027') {
                    $montoDetraccion = round($totalDetalle * 0.04); // Redondeo al entero más cercano
                } elseif ($moviment->codeDetraction == '021') {
                    $montoDetraccion = round($totalDetalle * 0.10); // Redondeo al entero más cercano
                }
            }

            // Calcular el saldo neto (total - detracción)
            $saldoNeto = $totalDetalle - $montoDetraccion;

            // Calcular montos pagados y saldos
            $pagado = $totalDetalle - ($item->totalDebt ?? 0);
            $saldo  = $item->totalDebt ?? 0;

            // Acumular los totales
            $total += $totalDetalle;
            $totalDebt += $totalDetalleDebt;
            $totalPagado += $pagado;
            $totalSaldo += $saldo;
            $totalDetraccion += $montoDetraccion;
            $totalSaldoNeto += $saldoNeto;
            $razon = '';
            if ($moviment->person && $moviment->person->typeofDocument != "DNI") {
                $razon = $moviment->person->businessName ?? '';
            } elseif ($moviment->person) {
                $razon =
                    ($moviment->person->names ?? '') . ' ' .
                    ($moviment->person->fatherSurname ?? '') . ' ' .
                    ($moviment->person->motherSurname ?? '');
            }



            // Construir el array de exportación con validaciones
            $exportData[] = [
                'Item'                 => $i++,
                'RUC / DNI'            => $moviment->person->documentNumber ?? '',
                'Razón Social'         => $razon,

                'Fecha de Emision'     => isset($moviment->paymentDate) ? (string) Carbon::parse($moviment->paymentDate)->format('Y-m-d') : '',
                'Fecha de Vencimiento' => $item->date ?? '',
               'Dias Retraso'         => isset($item->date)
        ? Carbon::createFromFormat('Y-m-d', Carbon::now()->format('Y-m-d'))
            ->diffInDays(Carbon::createFromFormat('Y-m-d', Carbon::parse($item->date)->format('Y-m-d')), false) . ' dias'
        : '',

                'Documento'            => $moviment->sequentialNumber ?? '',
                'Total'                => (string) $totalDetalle,     // Convertir a cadena
                'Total Deuda'          => (string) $totalDetalleDebt, // Convertir a cadena
                'Detraccion'           => (string) $montoDetraccion,  // Convertir a cadena
                'Saldo Neto'           => (string) $saldoNeto,        // Convertir a cadena
                'Obs'                  => $item->status ?? 'Desconocido',
                'Pagos' => $installment->resumenPagos(),


            ];
        }

        $exportData[] = [
            'Item'                 => '',
            'RUC / DNI'            => '',
            'Razón Social'         => '',

            'Fecha de Emision'     => '',
            'Fecha de Vencimiento' => '',
            'Dias Retraso' => '',
            'Documento'            => 'TOTAL',
            'Total'                => (string) $total,           // Convertir a cadena
            'Total Deuda'          => (string) $totalDebt,       // Convertir a cadena
            'Detraccion'           => (string) $totalDetraccion, // Convertir a cadena
            'Saldo Neto'           => (string) $totalSaldoNeto,  // Convertir a cadena
            'Obs'                  => '',
            'Pagos'                  => '',
        ];
        // Agregar una fila adicional para los totales

        return Excel::download(new CuentasPorCobrarExport($exportData, $start, $end), 'CuentasPorCobrar_' . $start . '_al_' . $end . '.xlsx');
    }



    public function reporteVentasExcel(Request $request)
    {
        // Variables de entrada
        $branch_office_id = $request->input('branch_office_id');
        $typeDocument     = $request->input('typeDocument') ?? '';
        $status           = $request->input('status') ?? '';
        $personId         = $request->input('person_id') ?? '';
        $start            = $request->input('start') ?? '';            // Fecha de inicio
        $end              = $request->input('end') ?? '';              // Fecha de fin
        $sequentialNumber = $request->input('sequentialNumber') ?? ''; // Número secuencial (opcional)

        // Buscar sucursal
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice     = BranchOffice::find($branch_office_id);
        }

        // Buscar caja
        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }

        // Obtener datos que NO tienen nota de crédito
        $ventasSinNotaCredito = Moviment::where('branchOffice_id', $branch_office_id)
            ->doesntHave('creditNote') // Filtra las que no tienen nota de crédito
            ->when($typeDocument != '', function ($query) use ($typeDocument) {
                return $query->where('typeDocument', $typeDocument);
            })
            ->when($box_id != '', function ($query) use ($box_id) {
                return $query->where('box_id', $box_id);
            })
            ->when($personId != '', function ($query) use ($personId) {
                return $query->where('person_id', $personId);
            })
            ->when($status != '', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($start != '', function ($query) use ($start) {
                return $query->where('paymentDate', '>=', $start);
            })
            ->when($end != '', function ($query) use ($end) {
                return $query->where('paymentDate', '<=', $end);
            })
            ->when($sequentialNumber != '', function ($query) use ($sequentialNumber) {
                return $query->where('sequentialNumber', 'LIKE', "%$sequentialNumber%");
            })
            ->where('movType', 'Venta')
            ->with([
                'receptions', 'branchOffice', 'paymentConcept', 'box', 'detailsMoviment',
                'reception.details', 'person', 'user.worker.person', 'installments', 'installments.payInstallments',
            ])
            ->orderBy('id', 'desc')
            ->take(400)->get();

        // Obtener datos que SÍ tienen nota de crédito
        $ventasConNotaCredito = Moviment::where('branchOffice_id', $branch_office_id)
            ->has('creditNote') // Filtra las que tienen nota de crédito
            ->when($typeDocument != '', function ($query) use ($typeDocument) {
                return $query->where('typeDocument', $typeDocument);
            })
            ->when($box_id != '', function ($query) use ($box_id) {
                return $query->where('box_id', $box_id);
            })
            ->when($personId != '', function ($query) use ($personId) {
                return $query->where('person_id', $personId);
            })
            ->when($status != '', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($start != '', function ($query) use ($start) {
                return $query->where('paymentDate', '>=', $start);
            })
            ->when($end != '', function ($query) use ($end) {
                return $query->where('paymentDate', '<=', $end);
            })
            ->when($sequentialNumber != '', function ($query) use ($sequentialNumber) {
                return $query->where('sequentialNumber', 'LIKE', "%$sequentialNumber%");
            })
            ->where('movType', 'Venta')
            ->with([
                'receptions', 'branchOffice', 'paymentConcept', 'box', 'detailsMoviment',
                'reception.details', 'person', 'user.worker.person', 'installments', 'installments.payInstallments',
            ])
            ->orderBy('id', 'desc')
            ->take(400)->get();

        // Procesar los datos para la exportación
        $totalAfecto     = 0;
        $totalInafecto   = 0;
        $totalIgv        = 0;
        $totalDetalle    = 0;
        $totalDetraccion = 0;
        $totalSaldoNeto  = 0;

        $exportDataSinNotaCredito = [];
        foreach ($ventasSinNotaCredito as $moviment) {

            $personaCliente = $moviment->person ?? null;
            $nombreCliente  = '-';
            if ($personaCliente) {
                $nombreCliente = strtoupper($personaCliente->typeofDocument) != 'DNI'
                ? ($personaCliente->businessName ?? '-')
                : trim(($personaCliente->names ?? '') . ' ' . ($personaCliente->fatherSurname ?? '') . ' ' . ($personaCliente->motherSurname ?? ''));
            }

            // Cálculos
            $totalDetalle = $moviment->total ?? 0;
            $afecto       = $totalDetalle ? $totalDetalle / 1.18 : 0;
            $igv          = $afecto * 0.18;
            $total        = $afecto + $igv;

            $montoDetraccion = 0;
            if ($moviment && isset($moviment->codeDetraction)) {
                if ($moviment->codeDetraction == '027') {
                    $montoDetraccion = round($totalDetalle * 0.04);
                } elseif ($moviment->codeDetraction == '021') {
                    $montoDetraccion = round($totalDetalle * 0.10);
                }
            }

            $saldoNeto = $totalDetalle - $montoDetraccion;

            // Sumar totales
            $totalAfecto += $afecto;
            $totalInafecto += 0; // Asumiendo que no hay inafecto en este contexto
            $totalIgv += $igv;
            $totalDetraccion += $montoDetraccion;
            $totalSaldoNeto += $saldoNeto;

            $exportDataSinNotaCredito[] = [

                'FECHA DE EMISION' => $moviment->paymentDate,
                'NUMERO'           => $moviment->sequentialNumber,
                'DNI/RUC'          => $personaCliente->documentNumber ?? '',
                'RAZON SOCIAL'     => $nombreCliente,
                'AFECTO S/'        => (string) number_format($afecto, 2, '.', ''),
                'INAFECTO S/'      => (string) number_format(0, 2, '.', ''),
                'IGV S/'           => (string) number_format($igv, 2, '.', ''),
                'TOTAL S/'         => (string) number_format($total, 2, '.', ''),

                'DETRACCION'       => (string) number_format($montoDetraccion, 2, '.', ''),
                'SALDO NETO'       => (string) number_format($saldoNeto, 2, '.', ''),
                'ESTADO'           => $moviment->status,
                'USUARIO'          => $moviment->user->username,
            ];
        }

        // Agregar fila de totales para ventas sin nota de crédito
        $exportDataSinNotaCredito[] = [

            'FECHA DE EMISION' => '',
            'NUMERO'           => '',
            'DNI/RUC'          => '',
            'RAZON SOCIAL'     => 'TOTALES',
            'AFECTO S/'        => (string) number_format($totalAfecto, 2, '.', ''),
            'INAFECTO S/'      => (string) number_format($totalInafecto, 2, '.', ''),
            'IGV S/'           => (string) number_format($totalIgv, 2, '.', ''),
            'TOTAL S/'         => (string) number_format($totalDetalle, 2, '.', ''),

            'DETRACCION'       => (string) number_format($totalDetraccion, 2, '.', ''),
            'SALDO NETO'       => (string) number_format($totalSaldoNeto, 2, '.', ''),
            'ESTADO'           => '',
            'USUARIO'          => '',
        ];

        // Procesar los datos de ventas con nota de crédito
        $totalAfectoCredito     = 0;
        $totalInafectoCredito   = 0;
        $totalIgvCredito        = 0;
        $totalDetalleCredito    = 0;
        $totalDetraccionCredito = 0;
        $totalSaldoNetoCredito  = 0;

        $exportDataConNotaCredito = [];
        foreach ($ventasConNotaCredito as $moviment) {
            $personaCliente = $moviment->person ?? null;
            $nombreCliente  = '-';
            if ($personaCliente) {
                $nombreCliente = strtoupper($personaCliente->typeofDocument) != 'DNI'
                ? ($personaCliente->businessName ?? '-')
                : trim(($personaCliente->names ?? '') . ' ' . ($personaCliente->fatherSurname ?? '') . ' ' . ($personaCliente->motherSurname ?? ''));
            }

            // Cálculos
            $totalDetalleCredito = $moviment->total ?? 0;
            $afectoCredito       = $totalDetalleCredito ? $totalDetalleCredito / 1.18 : 0;
            $igvCredito          = $afectoCredito * 0.18;
            $totalCredito        = $afectoCredito + $igvCredito;

            $montoDetraccionCredito = 0;
            if ($moviment && isset($moviment->codeDetraction)) {
                if ($moviment->codeDetraction == '027') {
                    $montoDetraccionCredito = round($totalDetalleCredito * 0.04);
                } elseif ($moviment->codeDetraction == '021') {
                    $montoDetraccionCredito = round($totalDetalleCredito * 0.10);
                }
            }

            $saldoNetoCredito = $totalDetalleCredito - $montoDetraccionCredito;

            // Sumar totales
            $totalAfectoCredito += $afectoCredito;
            $totalInafectoCredito += 0; // Asumiendo que no hay inafecto en este contexto
            $totalIgvCredito += $igvCredito;
            $totalDetraccionCredito += $montoDetraccionCredito;
            $totalSaldoNetoCredito += $saldoNetoCredito;

            $razones = [
                1  => 'Anulacion de la Operación',
                2  => 'Anulacion por error en el RUC',
                3  => 'Correción por error en la descripción',
                4  => 'Descuento global',
                5  => 'Descuento por item',
                6  => 'Devolución total',
                7  => 'Devolución por ítem',
                8  => 'Bonificación',
                9  => 'Disminución en el valor',
                10 => 'Otros conceptos',
            ];

            // Obtener el ID de la razón de la nota de crédito
            $razonId = $moviment->creditNote->reason ?? null;

            // Asignar la razón utilizando el ID, o una cadena vacía si no existe
            $razonName = $razonId !== null && isset($razones[$razonId]) ? $razones[$razonId] : '';

            $exportDataConNotaCredito[] = [
                'FECHA DE EMISION NOTA CREDITO' => isset($moviment->creditNote->created_at) ? Carbon::parse($moviment->creditNote->created_at)->format('Y-m-d H:i:s') : '', // Agregar la fecha de emisión de la nota de crédito
                'NUMERO NOTA CREDITO'           => $moviment->creditNote->number ?? '',                                                                                     // Agregar el número de la nota de crédito
                'RAZON'                         => $razonName ?? '',                                                                                                        // Agregar la razón de la nota de crédito
                'FECHA DE EMISION VENTA'        => $moviment->paymentDate,
                'NUMERO VENTA'                  => $moviment->sequentialNumber,
                'DNI/RUC'                       => $personaCliente->documentNumber ?? '',
                'RAZON SOCIAL'                  => $nombreCliente,
                'AFECTO S/'                     => (string) number_format($afectoCredito, 2, '.', ''),
                'INAFECTO S/'                   => (string) number_format(0, 2, '.', ''),
                'IGV S/'                        => (string) number_format($igvCredito, 2, '.', ''),
                'TOTAL S/'                      => (string) number_format($totalCredito, 2, '.', ''),

                'DETRACCION'                    => (string) number_format($montoDetraccionCredito, 2, '.', ''),
                'SALDO NETO'                    => (string) number_format($saldoNetoCredito, 2, '.', ''),
                'ESTADO'                        => $moviment->status,
                'USUARIO'                       => $moviment->user->username,
            ];
        }

        // Agregar fila de totales para ventas con nota de crédito
        $exportDataConNotaCredito[] = [
            'FECHA DE EMISION NOTA CREDITO' => '', // Agregar la fecha de emisión de la nota de crédito
            'NUMERO NOTA CREDITO'           => '', // Agregar el número de la nota de crédito
            'RAZON'                         => '', // Agregar la razón de la nota de crédito
            'FECHA DE EMISION VENTA'        => '',
            'NUMERO VENTA'                  => '',
            'DNI/RUC'                       => '',
            'RAZON SOCIAL'                  => 'TOTALES',
            'AFECTO S/'                     => (string) number_format($totalAfectoCredito, 2, '.', ''),
            'INAFECTO S/'                   => (string) number_format($totalInafectoCredito, 2, '.', ''),
            'IGV S/'                        => (string) number_format($totalIgvCredito, 2, '.', ''),
            'TOTAL S/'                      => (string) number_format($totalDetalleCredito, 2, '.', ''),

            'DETRACCION'                    => (string) number_format($totalDetraccionCredito, 2, '.', ''),
            'SALDO NETO'                    => (string) number_format($totalSaldoNetoCredito, 2, '.', ''),
            'ESTADO'                        => '',
            'USUARIO'                       => '',
        ];
// dd('xd');
        // Devolvemos los datos en un archivo Excel
        return Excel::download(new SalesExport($exportDataSinNotaCredito,
            $exportDataConNotaCredito, $start, $end), 'reporte_ventas.xlsx');
    }

    public function pruebaFacturador()
    {
        // Aquí puedes pasar datos a la vista si lo necesitas, pero por ahora solo devuelve la vista.
        return view('pruebaFacturador');
    }

    public function namePerson($person)
    {
        if ($person == null) {
            return '-'; // Si $person es nulo, retornamos un valor predeterminado
        }

        $typeD  = $person->typeofDocument ?? 'dni';
        $cadena = '';

        if (strtolower($typeD) === 'ruc') {
            $cadena = $person->businessName;
        } else {
            $cadena = $person->names . ' ' . $person->fatherSurname . ' ' . $person->motherSurname;
        }

        // return $cadena . ' ' . ($person->documentNumber == null ? '?' : $typeD . ' ' . $person->documentNumber);

        return $cadena;
    }
    public function generarQrGuia()
    {

        $funcion   = "actualizarEstadoServidor2";
        $fechaHoy  = Carbon::now()->format('Y-m-d');
        $fechaAyer = Carbon::now()->subDay()->format('Y-m-d');
        // Construir la URL con los parámetros
        $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion'    => $funcion,
            'fecini'     => $fechaAyer,
            'fecfin'     => $fechaHoy,
            // 'estado' => 1,
            'id_empresa' => 437,
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

        } else {

        }

        // Cerrar cURL
        curl_close($ch);

    }
    public function ticketbox(Request $request, $idMov = 10)
    {
        $Movimiento = Moviment::with(['reception', 'paymentConcept'])->find($idMov);

        // Inicializar el array de detalles
        $detalles = [];

        $detalles[] = [
            "descripcion"              => $Movimiento?->paymentConcept?->name ?? '-',
            "cantidad"                 => 1, // Cantidad fija (es un servicio)
            "precioventaunitarioxitem" => $Movimiento->total ?? 0,
        ];

        $tipoDocumento = '';
        $num           = $Movimiento->sequentialNumber;

        $dateTime       = Carbon::now()->format('Y-m-d H:i:s');
        $personaCliente = Person::withTrashed()->find($Movimiento->person_id);
        $fechaInicio    = $Movimiento->created_at;
        $rucOdni        = $personaCliente->documentNumber;

        if (strtoupper($personaCliente->typeofDocument) != 'DNI') {
            $nombreCliente = $personaCliente->businessName;
            $direccion     = $personaCliente->fiscalAddress ?? '-';
        } else {
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $direccion     = $personaCliente->address ?? '-';
        }

        $formapago   = DB::select('SELECT obtenerFormaPagoPorCaja(:id) AS forma_pago', ['id' => $Movimiento->id]);
        $typePayment = $formapago[0]->forma_pago ?? '-';

        $dataE = [
            'title'           => 'DOCUMENTO DE PAGO123',
            'ruc_dni'         => $rucOdni,
            'direccion'       => $direccion,
            'idMovimiento'    => $Movimiento->id,
            'tipoElectronica' => $tipoDocumento,
            'typePayment'     => $typePayment,
            'numeroVenta'     => $num,
            'numeroCaja'      => $Movimiento->correlative,
            'porcentaje'      => $Movimiento->percentDetraction,
            'mov_id'          => $Movimiento->mov_id ?? null,
            'fechaemision'    => $Movimiento->created_at->format('Y-m-d'),
            'cliente'         => $nombreCliente,
            'detalles'        => $detalles,
            'cuentas'         => $Movimiento->installments,
            'vuelto'          => '0.00',
            'totalPagado'     => $Movimiento->total,
            'linkRevisarFact' => '$linkRevisarFact',
            'formaPago'       => $Movimiento->formaPago ?? '-',
            'fechaInicio'     => $fechaInicio,
            'guia'            => $Movimiento->reception?->firstCarrierGuide?->numero ?? '-',
            'placa'           => $Movimiento->reception->id ?? '-',
            'typeSale'        => $Movimiento->typeSale ?? '-',
            'codeDetraction'  => $Movimiento->codeDetraction ?? '-',
            'comentario'      => $Movimiento->comment ?? '-',
        ];

        // Utiliza el método loadView() directamente en la fachada PDF
        $pdf           = PDF::loadView('caja-pdf-ticket', $dataE);
        $canvas        = $pdf->getDomPDF()->get_canvas();
        $contenidoAlto = $canvas->get_height();
        $pdf->setPaper([15, 5, 172, 439], 'portrait');

        $fileName = 'DOCUMENTO DE PAGO- ' . $dateTime . '.pdf';
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->stream($fileName);
    }
    public function applyNameFilter($query, $searchTermUpper)
    {
        // Crear un campo virtual concatenado
        $query->where(DB::raw("UPPER(CONCAT_WS(' ', names, fatherSurname, motherSurname, businessName))"), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(documentNumber)'), 'LIKE', '%' . $searchTermUpper . '%');
    }

    public function ticketbackbox(Request $request, $idMov = 10)
    {

        $Movimiento = Moviment::find($idMov);

        if (! $Movimiento) {
            abort(404, 'Movimiento No Encontrado');

        }
        if ($Movimiento->driverExpense_id != null) {
            return ($this->ticketbackbox($request, $idMov));
        }
        // Verifica si $expense_driver y sus relaciones no son nulas antes de acceder
        $expense_driver = DriverExpense::with([
            'moviment',
            'expensesConcept',
            'programming',
            'programming.detailsWorkers',
        ])
            ->where('expensesConcept_id', 21)
            ->where('id', $Movimiento->driverExpense_id)
            ->first();

        $driver = optional(
            optional($expense_driver->programming)
                ->detailsWorkers
                ->where('function', 'driver')
                ->first()
        );

        // Verifica si se encontró un `driver` antes de buscarlo en `Worker`
        $driver = $driver ? Worker::with('person')->find($driver->id) : null;

        // Inicializar el array de detalles
        $detalles = [];

        $box        = $Movimiento->box;
        $sucursal   = $Movimiento->branchOffice;
        $detalles[] = [
            "descripcion"              => 'TRANSFERENCIA DE FONDOS (EGRESOS)' ?? '-',
            "cantidad"                 => 1, // Cantidad fija (es un servicio)
            "precioventaunitarioxitem" => $expense_driver?->moviment?->total ?? 0,
        ];

        $tipoDocumento = '';
        $num           = $Movimiento->sequentialNumber;

        $dateTime       = Carbon::now()->format('Y-m-d H:i:s');
        $personaCliente = Person::withTrashed()->find($Movimiento->person_id);
        $fechaInicio    = $Movimiento->created_at;
        $rucOdni        = $personaCliente->documentNumber;

        if (strtoupper($personaCliente->typeofDocument) != 'DNI') {
            $nombreCliente = $personaCliente->businessName;
            $direccion     = $personaCliente->fiscalAddress ?? '-';
        } else {
            $nombreCliente = $personaCliente->names . ' ' . $personaCliente->fatherSurname . ' ' . $personaCliente->motherSurname;
            $direccion     = $personaCliente->address ?? '-';
        }

        $formapago   = DB::select('SELECT obtenerFormaPagoPorCaja(:id) AS forma_pago', ['id' => $Movimiento->id]);
        $typePayment = $formapago[0]->forma_pago ?? '-';

        $dataE = [
            'title'           => 'TICKET DE MOVIMIENTO DE REGRESO A CAJA',
            'ruc_dni'         => $rucOdni,
            'direccion'       => $direccion,
            'idMovimiento'    => $Movimiento->id,
            'tipoElectronica' => $tipoDocumento,
            'typePayment'     => $typePayment,
            'numeroVenta'     => $num,
            'numeroCaja'      => $Movimiento->correlative,
            'porcentaje'      => $Movimiento->percentDetraction,
            'mov_id'          => $Movimiento->mov_id ?? null,
            'fechaemision'    => $Movimiento->created_at->format('Y-m-d H:i:s'),

            'cliente'         => $nombreCliente,
            'detalles'        => $detalles,
            'cuentas'         => $Movimiento->installments,
            'vuelto'          => '0.00',
            'totalPagado'     => $Movimiento->total,
            'linkRevisarFact' => '$linkRevisarFact',
            'formaPago'       => $Movimiento->formaPago ?? '-',
            'fechaInicio'     => $fechaInicio,
            'guia'            => $Movimiento->reception?->firstCarrierGuide?->numero ?? '-',
            'placa'           => $Movimiento->reception->id ?? '-',
            'typeSale'        => $Movimiento->typeSale ?? '-',
            'codeDetraction'  => $Movimiento->codeDetraction ?? '-',
            'comentario'      => $Movimiento->comment ?? '-',
            'usuario'         => ($this->namePerson($Movimiento->user->worker->person) ?? ''),
            'programacion'    => $expense_driver->programming ?? '-',
            'conductor'       => ($this->namePerson($driver->person) ?? ''),
            'caja'            => $box->name ?? '',
            'sucursal'        => $sucursal->name ?? '',
        ];

        // Utiliza el método loadView() directamente en la fachada PDF
        $pdf           = PDF::loadView('back_caja-ticket', $dataE);
        $canvas        = $pdf->getDomPDF()->get_canvas();
        $contenidoAlto = $canvas->get_height();
        $pdf->setPaper([15, 5, 172, 450], 'portrait');

        $fileName = 'DOCUMENTO DE PAGO- ' . $dateTime . '.pdf';
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->stream($fileName);
    }
    public function ticketrecepcion(Request $request, $idMov = 10)
    {

        $reception = Reception::find($idMov);

        if (! $reception) {
            abort(404, 'Reception No Encontrado');

        }

        $data = [
            "reception"    => $reception,
            "branchoffice" => $reception->branchOffice,
            "sale"         => $reception->moviment,
            "totalDetalle" => $reception->paymentAmount,
            "detalles"     => [],
        ];

        // Utiliza el método loadView() directamente en la fachada PDF
        $pdf = PDF::loadView('recepcion-venta-ticket', $data);
        // $canvas        = $pdf->getDomPDF()->get_canvas();
        // $contenidoAlto = $canvas->get_height();
        $pdf->setPaper([15, 5, 172, 550], 'portrait');

        $fileName = 'Ticket-recepcion-' . $reception->codeReception . '.pdf';
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->stream($fileName);
    }
}
