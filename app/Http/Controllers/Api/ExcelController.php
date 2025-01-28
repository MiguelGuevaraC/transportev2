<?php

namespace App\Http\Controllers\Api;

use App\Exports\DocumentExport;
use App\Exports\DriverExpensesExport;
use App\Exports\GuidesExport;
use App\Exports\ManifiestoExport;
use App\Exports\VehiclesExport;
use App\Exports\VentasExport;
use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\CarrierGuide;
use App\Models\CreditNote;
use App\Models\Document;
use App\Models\DriverExpense;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\Programming;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function reporteDocumentsExcel(Request $request)
    {

        $number = $request->input('number'); // Número de documento
        $vehicle_id = $request->input('vehicle_id'); // ID de vehículo
        $startDate = $request->input('startDate'); // Fecha de inicio (created_at >=)
        $endDate = $request->input('endDate'); // Fecha de fin (created_at <=)
        $status = $request->input('status');
        // Construir la consulta con filtros condicionales
        $query = Document::with(['vehicle'])->where('state', 1);

        // Filtrar por número de documento si está presente
        if (!empty($number)) {
            $query->where('number', 'LIKE', "%$number%");
        }
        if (!empty($status)) {
            $query->where('status', 'LIKE', "%$status%");
        }
        // Filtrar por ID de vehículo si está presente
        if (!empty($vehicle_id)) {
            $query->where('vehicle_id', $vehicle_id);
        }

        // Filtrar por fechas de creación (rangos)
        if (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate);
        }
        $documents = $query->orderBy('id', 'desc')
            ->get();
        $exportData = [];

        foreach ($documents as $document) {
            $recept = Document::find($document->id);

            // Construir el array de exportación con validaciones y convertir todo a string
            $exportData[] = [
                'FECHA VENCIMIENTO' => (string) ($document->dueDate ?? ''),
                'NUMERO' => (string) ($document->number ?? ''),
                'ESTADO' => (string) ($document->status),
                'VEHÍCULO' => (string) (($document->vehicle->currentPlate) ?? ''),
                'DESCRIPCIÓN' => (string) ($document->description ?? ''),
                'DOCUMENTO' => (string) ("https://develop.garzasoft.com/transporte/public" . $document->pathFile),
            ];

        }

        // Agregar una fila adicional para los totales, convertir todo a string

        return Excel::download(new DocumentExport($exportData, $startDate, $endDate), 'reporte_documentos.xlsx');
    }

    
    public function applyNameFilter($query, $searchTermUpper)
    {
        // Crear un campo virtual concatenado
        $query->where(DB::raw("UPPER(CONCAT_WS(' ', names, fatherSurname, motherSurname, businessName))"), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(documentNumber)'), 'LIKE', '%' . $searchTermUpper . '%');
    }

    public function guidesExcel(Request $request)
    {

        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice = BranchOffice::find($branch_office_id);
        }

        // Paginación
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (!is_numeric($perPage) || (int) $perPage <= 0) {
            $perPage = 10;
        }

        // FILTROS
        $numero = $request->input('numero');
        $id_sucursal = $request->input('branch_office_id');

        $document = $request->input('document');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $route = $request->input('route');
        $typeGuia = $request->input('typeGuia');
        $statusGuia = $request->input('statusGuia');
        $statusFacturado = $request->input('statusFacturado');
        $observation = $request->input('observation');
        $vehicles = $request->input('vehicles');
        $drivers = $request->input('drivers');

        
        $nombreClientePaga = $request->input('nombreClientePaga');
        $nombreRemitente = $request->input('nombreRemitente');
        $nombreDestinatario = $request->input('nombreDestinatario');

        // Iniciar consulta base
        $carrierGuides = CarrierGuide::with('tract', 'platform', 'motive', 'origin', 'destination', 'sender', 'recipient', 'branchOffice',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
            'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        );

        // Aplicación de los filtros con comparaciones en minúsculas
        if (!empty($numero)) {
            $carrierGuides->whereRaw('LOWER(numero) LIKE ?', ['%' . strtolower($numero) . '%']);
        }

        if (!empty($document)) {
            $carrierGuides->whereRaw('LOWER(document) LIKE ?', ['%' . strtolower($document) . '%']);
        }

        if (!empty($drivers)) {
            $lowerDrivers = strtolower($drivers);
            $carrierGuides->where(function ($query) use ($lowerDrivers) {
                $query->whereHas('driver.person', function ($q) use ($lowerDrivers) {
                    $q->whereRaw("LOWER(CONCAT(names, ' ', fatherSurname, ' ', motherSurname)) LIKE ?", ["%{$lowerDrivers}%"]);
                })->orWhereHas('copilot.person', function ($q) use ($lowerDrivers) {
                    $q->whereRaw("LOWER(CONCAT(names, ' ', fatherSurname, ' ', motherSurname)) LIKE ?", ["%{$lowerDrivers}%"]);
                });
            });
        }

        if (!empty($route)) {
            $routeParts = explode('-', $route);

            if (count($routeParts) === 1) {
                $carrierGuides->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->orWhereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                });
            } elseif (count($routeParts) === 2) {
                $carrierGuides->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->whereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[1]) . '%']);
                });
            }
        }

        if (!empty($nombreClientePaga)) {
            $nombreClientePagaUpper = strtoupper($nombreClientePaga);

            $carrierGuides->where(function ($query) use ($nombreClientePagaUpper) {
                // Búsqueda en sender
                $query->orWhereHas('payResponsible', function ($subQuery) use ($nombreClientePagaUpper) {
                    $subQuery->where(function ($q) use ($nombreClientePagaUpper) {
                        $this->applyNameFilter($q, $nombreClientePagaUpper);
                    });
                });
            });
        }
        if (!empty($nombreRemitente)) {
            $nombreRemitenteUpper = strtoupper($nombreRemitente);

            $carrierGuides->where(function ($query) use ($nombreRemitenteUpper) {
                // Búsqueda en sender
                $query->orWhereHas('sender', function ($subQuery) use ($nombreRemitenteUpper) {
                    $subQuery->where(function ($q) use ($nombreRemitenteUpper) {
                        $this->applyNameFilter($q, $nombreRemitenteUpper);
                    });
                });
            });
        }
        if (!empty($nombreDestinatario)) {
            $nombreDestinatarioUpper = strtoupper($nombreDestinatario);

            $carrierGuides->where(function ($query) use ($nombreDestinatarioUpper) {
                // Búsqueda en sender
                $query->orWhereHas('recipient', function ($subQuery) use ($nombreDestinatarioUpper) {
                    $subQuery->where(function ($q) use ($nombreDestinatarioUpper) {
                        $this->applyNameFilter($q, $nombreDestinatarioUpper);
                    });
                });
            });
        }

        if (!empty($vehicles)) {
            $carrierGuides->where(function ($query) use ($vehicles) {
                $lowerVehicles = strtolower($vehicles);
                $query->whereHas('tract', function ($query) use ($lowerVehicles) {
                    $query->whereRaw('LOWER(currentPlate) LIKE ?', ['%' . $lowerVehicles . '%'])
                        ->orWhereRaw('LOWER(numberMtc) LIKE ?', ['%' . $lowerVehicles . '%']);
                })->orWhereHas('platform', function ($query) use ($lowerVehicles) {
                    $query->whereRaw('LOWER(currentPlate) LIKE ?', ['%' . $lowerVehicles . '%'])
                        ->orWhereRaw('LOWER(numberMtc) LIKE ?', ['%' . $lowerVehicles . '%']);
                });
            });
        }

        if (!empty($typeGuia)) {
            $carrierGuides->whereRaw('LOWER(type) = ?', [strtolower($typeGuia)]);
        }
        if (!empty($id_sucursal)) {
            $carrierGuides->where('branchOffice_id', $id_sucursal);
        }

        if (!empty($statusGuia)) {
            $carrierGuides->whereRaw('LOWER(status) = ?', [strtolower($statusGuia)]);
        }

        if (!empty($statusFacturado)) {
            $carrierGuides->whereRaw('LOWER(status_facturado) = ?', [strtolower($statusFacturado)]);
        }

        if (!empty($observation)) {
            $carrierGuides->whereRaw('LOWER(observation) LIKE ?', ['%' . strtolower($observation) . '%']);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $carrierGuides->whereBetween(DB::raw('DATE(transferStartDate)'), [$startDate, $endDate]);
        } elseif (!empty($startDate)) {
            $carrierGuides->whereDate('transferStartDate', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $carrierGuides->whereDate('transferStartDate', '<=', $endDate);
        }

        // Ordenar y paginar
        $carrierGuides = $carrierGuides->orderBy(DB::raw('CAST(SUBSTRING(numero, 6) AS UNSIGNED)'), 'desc')
            ->get();

        $sumFlete = 0;
        $sumaSaldo = 0;
        $sumaPeso = 0;
        $index = 1;
        foreach ($carrierGuides as $carrier) {
            $reception = $carrier->reception ?? null;
            $details = $reception ? $reception->details()->pluck('description')->toArray() : [];
            $flete = 0;

            if ($reception) {
                // Calcular flete
                $flete = $reception->conditionPay == 'Credito'
                ? $reception->creditAmount ?? 0
                : $reception->paymentAmount ?? 0;

                $sumFlete += $flete;
                $sumaSaldo += $reception->debtAmount ?? 0;
                $sumaPeso += $reception->netWeight ?? 0;
            }

            // Añadir los datos de la guía a la exportación
            $exportData[] = [
                'N°' => $index++,
                'CARGA MATERIAL' => $details ? implode(', ', $details) : 'Sin detalles',
                'REMITENTE' => $this->namePerson($carrier?->sender ?? null) ?? '-',
                'REM. RUC/DNI' => $carrier?->sender?->documentNumber ?? '-',
                'DESTINATARIO' => $this->namePerson($carrier?->recipient ?? null) ?? '-',
                'DEST. RUC/DNI' => $carrier?->recipient?->documentNumber ?? '-',
                'PUNTO PARTIDA' => $carrier?->origin?->name ?? '-',
                'PUNTO LLEGADA' => $carrier?->destination?->name ?? '-',
                'DOCUMENTOS ANEXOS' => $reception?->comment ?? '-',
                'GUÍA GRT' => $carrier?->numero ?? '-',
                'TOTAL PESO' => $reception?->netWeight ?? 0,
                'TOTAL FLETE' => $flete,
                'SALDO' => $reception?->debtAmount ?? 0,
                'COND. PAGO' => $reception?->conditionPay ?? '-',
                'ESTADO ENTREGA' => $carrier?->status ?? '-',
            ];
        }

        // Agregar fila de totales al final de la segunda tabla
        $exportData[] = [
            'N°' => '',
            'CARGA MATERIAL' => '',
            'REMITENTE' => '',
            'REM. RUC/DNI' => '-',
            'DESTINATARIO' => '',
            'DEST. RUC/DNI' => '-',
            'PUNTO PARTIDA' => '',
            'PUNTO LLEGADA' => '',
            'DOCUMENTOS ANEXOS' => '',
            'GUÍA GRT' => 'TOTAL',
            'TOTAL PESO' => $sumaPeso,
            'TOTAL FLETE' => $sumFlete,
            'SALDO' => $sumaSaldo,
            'COND. PAGO' => '',
            'ESTADO ENTREGA' => '',
        ];
        return Excel::download(new GuidesExport($exportData, $startDate, $endDate), 'reporte_guias.xlsx');

    }
    public function reporteVehiclesExcel(Request $request)
    {

        $branch_office_id = $request->input('branch_office_id');
        $sinProgramaciones = $request->input('sinProgramaciones'); //

        $start = $request->input('start'); // Fecha de inicio
        $end = $request->input('end'); // Fecha de fin

        $placa = $request->input('placa');
        $mtc = $request->input('mtc');
        $marca = $request->input('marca');
        $modelo = $request->input('modelo');

        // Validar Branch Office
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }

        // Obtener el usuario autenticado
        $user = auth()->user();
        $user_id = $user->id ?? '';

        // Construir consulta inicial
        $vehiclesQuery = Vehicle::with([
            "modelFunctional",
            "photos",
            "documents",
            "typeCarroceria.typeCompany",
            "responsable.worker",
            "companyGps",
            'branchOffice',
        ])->orderBy('id', 'desc');

        if ($sinProgramaciones == "1" or $sinProgramaciones == "true") {
            $vehiclesQuery->whereDoesntHave('tractProgrammings')
                ->whereDoesntHave('platformProgrammings');
        }

        if (!empty($modelo)) {
            $vehiclesQuery->whereHas('modelFunctional', function ($query) use ($modelo) {
                $query->where('name', 'LIKE', '%' . $modelo . '%');
            });
        }

        // Filtros adicionales por LIKE para otros campos
        if (!empty($placa)) {
            $vehiclesQuery->where('currentPlate', 'LIKE', '%' . $placa . '%');
        }

        if (!empty($mtc)) {
            $vehiclesQuery->where('numberMtc', 'LIKE', '%' . $mtc . '%');
        }

        if (!empty($marca)) {
            $vehiclesQuery->where('brand', 'LIKE', '%' . $marca . '%');
        }

        // Validación de la paginación
        $pagination = $request->input('per_page', 10); // Valor por defecto: 10
        if (!is_numeric($pagination) || $pagination <= 0) {
            $pagination = 10; // Restablecer a valor por defecto
        }

        // Aplicar paginación
        $list = $vehiclesQuery->get();

        foreach ($list as $vehicle) {

            $exportData[] = [
                'PLACA' => $vehicle->currentPlate ?? '',
                'MTC' => $vehicle->numberMtc ?? '',
                'MARCA' => $vehicle->brand ?? '',
                'MODELO' => $vehicle?->modelFunctional?->name ?? '',
                'TIPO VEHICULO' => $vehicle?->typeCarroceria?->typeCompany?->description ?? '',
                'COLOR' => $vehicle?->color ?? '',
                'RESPONSABLE' => $this->namePerson($vehicle?->responsable) ?? '',
            ];
        }

        // Agregar una fila adicional para los totales, convertir todo a string

        return Excel::download(new VehiclesExport($exportData, $start, $end), 'reporte_documentos.xlsx');
    }

    public function reporteManifiestoExcel(Request $request, $id)
    {
        $programming = Programming::find($id);

        if (!$programming) {
            return response()->json(['message' => 'Programming not found'], 422);
        }

        // Obtener las guías de transportista asociadas
        // Obtener las guías de transportista asociadas, excluyendo los eliminados
        $carrierGuides = $programming->carrierGuides()->whereNull('deleted_at')->get(); // Solo guías no eliminadas

// Pluck de los IDs de las guías desde la relación, excluyendo las eliminadas
        $relationGuides = $programming->carrierGuides()->whereNull('deleted_at')->pluck('id')->toArray(); // IDs de las guías desde la relación

// Obtén las guías directamente desde la tabla, excluyendo los eliminados
        $dbGuides = DB::table('carrier_by_programmings')
            ->where('programming_id', $id)
            ->whereNull('deleted_at') // Excluir los registros eliminados
            ->pluck('carrier_guide_id') // Obtén solo los IDs de las guías
            ->toArray();

// Combina ambas fuentes y elimina duplicados
        $uniqueGuidesIds = collect($relationGuides)
            ->merge($dbGuides)
            ->unique() // Elimina duplicados
            ->values() // Re-indexa los valores
            ->toArray();

// Obtén los detalles completos de las guías únicas, excluyendo las eliminadas
        $uniqueGuides = CarrierGuide::whereIn('id', $uniqueGuidesIds)
            ->whereNull('deleted_at') // Excluir los eliminados
            ->get();

// Reemplaza en `$object`
        $carrierGuides = $uniqueGuides;

        // Primera tabla: Información del manifiesto
        $manifiestoData = [
            [
                'NUMERO' => (string) $programming?->numero ?? '-',
                'F. VIAJE' => (string) $programming?->departureDate ?? '-',
                'ORIGEN' => (string) $programming?->origin?->name ?? '-',
                'DESTINO' => (string) $programming?->destination?->name ?? '-',
                'TRACTO' => (string) $programming?->tract?->currentPlate ?? '-',
                'CARRETA' => (string) $programming?->platForm?->currentPlate ?? '-',
                'TOTAL PESO' => (string) $programming?->totalWeight ?? 0,
                'ESTADO' => (string) $programming?->status ?? '-',
                'ESTADO DE GASTO' => (string) $programming?->statusLiquidacion ?? '-',
                'LIQUIDADO' => (string) $programming?->dateLiquidacion ? 'Sí' : 'No',
            ],
        ];

        $exportData = [];
        $index = 1;
        $sumFlete = 0;
        $sumaPeso = 0;
        $sumaSaldo = 0;

        // Segunda tabla: Información de las guías de transportista
        foreach ($carrierGuides as $carrier) {
            $reception = $carrier->reception ?? null;
            $details = $reception ? $reception->details()->pluck('description')->toArray() : [];
            $flete = 0;

            if ($reception) {
                // Calcular flete
                $flete = $reception->conditionPay == 'Credito'
                ? $reception->creditAmount ?? 0
                : $reception->paymentAmount ?? 0;

                $sumFlete += $flete;
                $sumaSaldo += $reception->debtAmount ?? 0;
                $sumaPeso += $reception->netWeight ?? 0;
            }

            // Añadir los datos de la guía a la exportación
            $exportData[] = [
                'N°' => $index++,
                'CARGA MATERIAL' => $details ? implode(', ', $details) : 'Sin detalles',
                'REMITENTE' => $this->namePerson($reception?->firstCarrierGuide?->sender ?? null) ?? '-',
                'REM. RUC/DNI' => $reception?->firstCarrierGuide?->sender?->documentNumber ?? '-',
                'DESTINATARIO' => $this->namePerson($reception?->firstCarrierGuide?->recipient ?? null) ?? '-',
                'DEST. RUC/DNI' => $reception?->firstCarrierGuide?->recipient?->documentNumber ?? '-',
                'PUNTO PARTIDA' => $reception?->firstCarrierGuide?->origin?->name ?? '-',
                'PUNTO LLEGADA' => $reception?->firstCarrierGuide?->destination?->name ?? '-',
                'DOCUMENTOS ANEXOS' => $reception?->firstCarrierGuide?->document ?? '-',
                'GUÍA GRT' => $reception?->firstCarrierGuide?->numero ?? '-',
                'TOTAL PESO' => $reception?->netWeight ?? 0,
                'TOTAL FLETE' => $flete,
                'SALDO' => $reception?->debtAmount ?? 0,
                'COND. PAGO' => $reception?->conditionPay ?? '-',
                'ESTADO ENTREGA' => $carrier?->status ?? '-',
            ];
        }

        // Agregar fila de totales al final de la segunda tabla
        $exportData[] = [
            'N°' => '',
            'CARGA MATERIAL' => '',
            'REMITENTE' => '',
            'REM. RUC/DNI' => '-',
            'DESTINATARIO' => '',
            'DEST. RUC/DNI' => '-',
            'PUNTO PARTIDA' => '',
            'PUNTO LLEGADA' => '',
            'DOCUMENTOS ANEXOS' => '',
            'GUÍA GRT' => 'TOTAL',
            'TOTAL PESO' => $sumaPeso,
            'TOTAL FLETE' => $sumFlete,
            'SALDO' => $sumaSaldo,
            'COND. PAGO' => '',
            'ESTADO ENTREGA' => '',
        ];

        // Exportar los datos en dos tablas separadas
        return Excel::download(new ManifiestoExport($manifiestoData, $exportData, $programming->numero), 'Manifiesto-' . $programming->numero . '.xlsx');
    }
    public function reporteDriverConceptExcel(Request $request, $id)
    {
        $programming = Programming::find($id);

        if (!$programming) {
            return response()->json(['message' => 'Programming not found'], 422);
        }

        // Obtener las guías de transportista asociadas
        $driverExpenses = $programming->driverExpenses;
        // Clonar la consulta para calcular los totales de ingreso y egreso
        $totalIngreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Ingreso');
            $q->where('selectTypePay', 'Efectivo')
                ->orWhere('selectTypePay', 'Descuento_sueldo')
                ->orWhere('selectTypePay', 'Proxima_liquidacion');
        })->sum('amount');

        $totalEgreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Egreso');
            $q->where('selectTypePay', 'Efectivo')
                ->orWhere('selectTypePay', 'Descuento_sueldo')
                ->orWhere('selectTypePay', 'Proxima_liquidacion');
        })->sum('amount');

// Calcular el saldo (diferencia entre ingresos y egresos)
        $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');

        // Primera tabla: Información del manifiesto
        $manifiestoData = [
            [
                'NUMERO' => (string) $programming?->numero ?? '-',
                'F. VIAJE' => (string) $programming?->departureDate ?? '-',
                'INGRESOS VIAJE' => (string) $totalIngreso,
                'EGRESOS VIAJE' => (string) $totalEgreso,
                'SALDO VIAJE' => (string) $saldo,

                'ORIGEN' => (string) $programming?->origin?->name ?? '-',
                'DESTINO' => (string) $programming?->destination?->name ?? '-',
                'TRACTO' => (string) $programming?->tract?->currentPlate ?? '-',
                'CARRETA' => (string) $programming?->platForm?->currentPlate ?? '-',
                'TOTAL PESO' => (string) $programming?->totalWeight ?? 0,
                'ESTADO' => (string) $programming?->status ?? '-',
                'ESTADO DE GASTO' => (string) $programming?->statusLiquidacion ?? '-',

                'LIQUIDADO' => (string) $programming?->dateLiquidacion ? 'Sí' : 'No',
            ],
        ];

        $exportData = [];
        $index = 1;
        $sumtotal = 0;
        $sumaPeso = 0;
        $sumaSaldo = 0;

        // Segunda tabla: Información de las guías de transportista
        $sumtotal = 0;
        $index = 1; // Asegurarse de que el índice esté inicializado

        foreach ($driverExpenses as $expense) {
            // Convertir el total a número flotante, con un valor por defecto de 0, y redondearlo a 2 decimales
            $monto = round((float) ($expense?->total ?? 0), 2);

            // Obtener el tipo de concepto
            $typeConcept = $expense?->expensesConcept?->typeConcept ?? '';

            // Sumar o restar según el tipo de movimiento
            if (strtolower($typeConcept) == 'ingreso') {
                $sumtotal = round($sumtotal + $monto, 2); // Redondear después de sumar
            } else {
                $sumtotal = round($sumtotal - $monto, 2); // Redondear después de restar
            }

            // Añadir los datos de la guía a la exportación
            $exportData[] = [
                'N°' => $index++,
                'CONCEPTO GASTO' => $expense?->expensesConcept?->name ?? '',
                'CANTIDAD' => $expense?->quantity ?? '1',
                'LUGAR' => $expense?->place ?? '-',
                'KM' => $expense?->KM ?? '-',
                'GALONES' => $expense?->gallons ?? '-',
                'NRO OPERACION' => $expense?->operationNumber ?? '-',
                'IGV' => $expense?->igv ?? '0',
                'EXONERADO' => $expense?->exonerado ?? '0',
                'GRAVADO' => $expense?->gravado ?? '0',
                'BANCO' => $expense?->bank?->name ?? 'Sin Banco',
                'PROVEEDOR' => $expense?->proveedor ? $this->namePerson($expense->proveedor) : 'Sin Proveedor',
                'COMENTARIO' => $expense?->comment ?? '-',
                'TIPO MOVIMIENTO' => $typeConcept,
                'TIPO DE PAGO' => $expense?->selectTypePay ?? '-',
                'FECHA' => $expense?->date_expense ?? 'Sin Fecha',
                'TOTAL' => number_format($monto, 2, '.', ''), // Formatear a 2 decimales como string
            ];
        }

        // Agregar fila de totales al final de la segunda tabla
        $exportData[] = [
            'N°' => '',
            'CONCEPTO GASTO' => '',
            'CANTIDAD' => '',
            'LUGAR' => '',
            'KM' => '',
            'GALONES' => '',
            'NRO OPERACION' => '',
            'IGV' => '',
            'EXONERADO' => '',
            'GRAVADO' => '',
            'BANCO' => '',
            'PROVEEDOR' => '',
            'COMENTARIO' => '',
            'TIPO MOVIMIENTO' => '',
            'TIPO DE PAGO' => '',
            'FECHA' => '',
            'TOTAL' => (string) $sumtotal,
        ];

        // Exportar los datos en dos tablas separadas
        return Excel::download(new DriverExpensesExport($manifiestoData, $exportData, $programming->numero), 'GASTOS-VIAJE-' . $programming->numero . '.xlsx');
    }

    public function namePerson($person)
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
        // return $cadena . ' ' . ($person->documentNumber == null ? '?' : $typeD . ' ' . $person->documentNumber);
        return $cadena;
    }

    public function reporteVentasExcel(Request $request)
    {
        // Variables de entrada
        $branch_office_id = $request->input('branch_office_id');
        $typeDocument = $request->input('typeDocument');
        $status = $request->input('status');
        $personId = $request->input('person_id');
        $start = $request->input('start'); // Fecha de inicio
        $end = $request->input('end'); // Fecha de fin
        $sequentialNumber = $request->input('sequentialNumber'); // Número secuencial (opcional)

        // Buscar sucursal
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }
        // else {
        //     $branch_office_id = auth()->user()->worker->branchOffice_id;
        //     $branchOffice = BranchOffice::find($branch_office_id);
        // }

        // Buscar caja
        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }
        $ventas = Moviment::query()

            ->when(!empty($branch_office_id), fn($query) => $query->where('branchOffice_id', $branch_office_id))
            ->when(!empty($typeDocument), fn($query) => $query->where('typeDocument', $typeDocument))
            ->when(!empty($box_id), fn($query) => $query->where('box_id', $box_id))
            ->when(!empty($personId), fn($query) => $query->where('person_id', $personId))
            ->when(!empty($status), fn($query) => $query->where('status', $status))
            ->when(!empty($start), fn($query) => $query->where('paymentDate', '>=', $start))
            ->when(!empty($end), fn($query) => $query->where('paymentDate', '<=', $end))
            ->when(!empty($sequentialNumber), fn($query) => $query->where('sequentialNumber', 'LIKE', "%$sequentialNumber%"))
            ->where('movType', 'Venta')
            ->with([
                'receptions',
                'branchOffice',
                'paymentConcept',
                'box',
                'detailsMoviment',
                'reception.details',
                'person',
                'user.worker.person',
                'installments',
                'installments.payInstallments',
            ])
            ->orderBy('id', 'desc')
            ->get();
        $exportDataSinNotaCredito = [];
        $exportDataConNotaCredito = [];

        // Variables de totales

        $totalIgv = 0;
        $totalOperacionGravada = 0;
        $totalImporteTotal = 0;
        $dateinstallment = "--/--/--";
        $iterador = 1;
        $condicion = 'NO ENCONTRADO EN EL FACTURADOR';
        // Procesar las ventas sin nota de crédito
        foreach ($ventas as $moviment) {

            $newRequest = new Request();

            // Agregar el campo 'nombre' al nuevo request
            $newRequest->merge(['nombre' => $moviment->sequentialNumber]);
            if ($moviment->getstatus_fact == null || $moviment->getstatus_fact != "DECLARADO Y ACTIVO") {
                $condicion = $this->getStatusFacturacion($newRequest);
                $condicion = $condicion == '' ? 'NO ENCONTRADO EN EL FACTURADOR' : $condicion;
                $movimentupdatestatus = Moviment::find($moviment->id);
                $movimentupdatestatus->getstatus_fact = $condicion;
                $movimentupdatestatus->save();
            } else {
                $condicion = $moviment->getstatus_fact;
            }
            // Llamar a la función pasando el nuevo request

            $dateinstallment = "--/--/--";
            $installment = Installment::withTrashed()->where("moviment_id", $moviment->id)
                ->orderBy('created_at', 'desc')->first();

            if ($installment) { // Verifica si existe un registro
                $dateinstallment = $installment->date
                ? Carbon::parse($installment->date)->format('d/m/y')
                : "--/--/--";
            } else {
                $movcaja = Moviment::withTrashed()->where("mov_id", $moviment->id)->first();
                if ($movcaja) {
                    $dateinstallment = $movcaja->paymentDate
                    ? Carbon::parse($movcaja->paymentDate)->format('d/m/y')
                    : "--/--/--";
                }
            }

            // if (!$moviment->creditNote) { // Ventas sin nota de crédito
            if (true) {
                // Lógica similar para calcular los totales

                $personaCliente = $moviment->person ?? null;
                $nombreCliente = '-';
                if ($personaCliente) {
                    $nombreCliente = strtoupper($personaCliente->typeofDocument) != 'DNI'
                    ? ($personaCliente->businessName ?? '-')
                    : trim(($personaCliente->names ?? '') . ' ' . ($personaCliente->fatherSurname ?? '') . ' ' . ($personaCliente->motherSurname ?? ''));
                }

                // Cálculos
                $totalDetalle = $moviment->total ?? 0;
                $afecto = $totalDetalle ? $totalDetalle / 1.18 : 0;
                $igv = $afecto * 0.18;
                $total = $afecto + $igv;
                $totalOperacionGravada += $afecto;
                $totalImporteTotal += $total;
                $totalIgv += $igv;
                // Calcular detracción usando percentDetraction
                // $montoDetraccion = isset($moviment->percentDetraction) ? round($totalDetalle * ($moviment->percentDetraction / 100), 2) : 0;
                // $saldoNeto = $totalDetalle - $montoDetraccion;

                // Sumar totales

                // Asignar tipo de documento en base al prefijo del correlativo
                $correlativo = $moviment->sequentialNumber;
                $tipoDocumento = '';
                $serie = '';
                $numeroC = '';

                if (str_starts_with($correlativo, 'B')) {
                    $tipoDocumento = 'BOLETA DE VENTA'; // Boleta
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'T')) {
                    $tipoDocumento = 'TICKET VENTA'; // Factura
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'F')) {
                    $tipoDocumento = 'FACTURA VENTA'; // Factura
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'FC')) {
                    $tipoDocumento = 'NOTA DE CRÉDITO'; // Nota de crédito para factura
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'BC')) {
                    $tipoDocumento = 'NOTA DE CRÉDITO'; // Nota de crédito para boleta
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                }
                // Sumar al total de ventas sin nota
                $fechaEmision = $moviment->paymentDate ? Carbon::parse($moviment->paymentDate)->format('d/m/y') : '';

                $exportDataSinNotaCredito[] = [
                    'NRO' => $iterador++,
                    'FECHA EMISIÓN' => $fechaEmision,
                    'CONDICIÓN' => $condicion,
                    'FORMA DE PAGO' => $moviment->typePayment == 'Contado' ? 'CONTADO' : 'CRÉDITO',
                    'FECHA CREDITO / CONTADO' => $dateinstallment,
                    'TIPO C.' => $tipoDocumento,
                    'SERIE C.' => (string) $serie,
                    'NUMERO C.' => (string) $numeroC,
                    'TIPO DOC' => (string) $personaCliente->typeofDocument,
                    'NUMERO DOC' => (string) $personaCliente->documentNumber ?? '',
                    'RAZON SOCIAL' => (string) $nombreCliente,
                    'GUIAS' => (string) implode(',', $moviment?->detalles()->pluck('guia')->toArray() ?? []),
                    'OPERACIÓN GRAVADA' => (string) number_format($afecto, 2, '.', ''),
                    'IGV' => (string) number_format($igv, 2, '.', ''),
                    'IMPORTE TOTAL' => (string) number_format($total, 2, '.', ''),
                    'FECHA REF. COMPROBANTE' => '',
                    'SERIE REF. COMPROBANTE' => '',
                    'NÚMERO REF. COMPROBANTE' => '',
                ];
            }
        }

// Agregar fila de totales para ventas sin nota de crédito
        $exportDataSinNotaCredito[] = [
            'NRO' => '',
            'FECHA EMISIÓN' => '',
            'CONDICIÓN' => '',
            'FORMA DE PAGO' => '',
            'FECHA CREDITO / CONTADO' => '',
            'TIPO C.' => '',
            'SERIE C.' => '',
            'NUMERO C.' => '',
            'TIPO DOC' => '',
            'NUMERO DOC' => '',
            'RAZON SOCIAL' => '',
            'GUIAS' => 'TOTAL',
            'OPERACIÓN GRAVADA' => (string) number_format($totalOperacionGravada, 2, '.', ''),
            'IGV' => (string) number_format($totalIgv, 2, '.', ''),
            'IMPORTE TOTAL' => (string) number_format($totalImporteTotal, 2, '.', ''),
            'FECHA REF. COMPROBANTE' => '',
            'SERIE REF. COMPROBANTE' => '',
            'NÚMERO REF. COMPROBANTE' => '',
        ];
        $exportDataSinNotaCredito[] = [
            'NRO' => '',
            'FECHA EMISIÓN' => '',
            'CONDICIÓN' => '',
            'FORMA DE PAGO' => '',
            'FECHA CREDITO / CONTADO' => '',
            'TIPO C.' => '',
            'SERIE C.' => '',
            'NUMERO C.' => '',
            'TIPO DOC' => '',
            'NUMERO DOC' => '',
            'RAZON SOCIAL' => '',
            'GUIAS' => '',
            'OPERACIÓN GRAVADA' => '',
            'IGV' => '',
            'IMPORTE TOTAL' => '',
            'FECHA REF. COMPROBANTE' => '',
            'SERIE REF. COMPROBANTE' => '',
            'NÚMERO REF. COMPROBANTE' => '',
        ];

// Procesar las ventas con nota de crédito
        $totalIgv2 = 0;
        $totalOperacionGravada2 = 0;
        $totalImporteTotal2 = 0;
        $condicionNC = '';
        $dateinstallmentNC = "--/--/--";

        foreach ($ventas as $moviment) {
            if ($moviment->creditNote) { // Ventas con nota de crédito
                $installment = Installment::withTrashed() // Incluye registros eliminados
                    ->where("moviment_id", $moviment->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $newRequest = new Request();

                // Agregar el campo 'nombre' al nuevo request
                $newRequest->merge(['nombre' => $moviment->creditNote->number]);

                if ($moviment->creditNote->getstatus_fact == null || $moviment->creditNote->getstatus_fact != "DECLARADO Y ACTIVO") {
                    $condicionNC = $this->getStatusFacturacion($newRequest);
                    $condicionNC = $condicionNC == '' ? 'NO ENCONTRADO EN EL FACTURADOR' : $condicionNC;
                    $creditupdatestatus = CreditNote::find($moviment->creditNote->id);
                    $creditupdatestatus->getstatus_fact = $condicionNC;
                    $creditupdatestatus->save();
                } else {
                    $condicionNC = $moviment->creditNote->getstatus_fact;
                }

                $dateinstallmentNC = "--/--/--";

                if ($installment) { // Verifica si existe un registro
                    $dateinstallmentNC = $installment->date
                    ? Carbon::parse($installment->date)->format('d/m/y')
                    : "--/--/--"; // Formatea la fecha si existe
                } else {
                    $movcaja = Moviment::withTrashed()->where("mov_id", $moviment->id)->first();
                    if ($movcaja) {
                        $dateinstallmentNC = $movcaja->paymentDate
                        ? Carbon::parse($movcaja->paymentDate)->format('d/m/y')
                        : "--/--/--"; // Formatea la fecha si existe
                    }
                }

                $personaCliente = $moviment->person ?? null;
                $nombreCliente = '-';
                if ($personaCliente) {
                    $nombreCliente = strtoupper($personaCliente->typeofDocument) != 'DNI'
                    ? ($personaCliente->businessName ?? '-')
                    : trim(($personaCliente->names ?? '') . ' ' . ($personaCliente->fatherSurname ?? '') . ' ' . ($personaCliente->motherSurname ?? ''));
                }

                $notacredito = $moviment->creditNote;
                // Cálculos
                $totalDetalle = $notacredito->total ?? 0;
                $afecto = $totalDetalle ? $totalDetalle / 1.18 : 0;
                $igv = $afecto * 0.18;
                $total = $afecto + $igv;
                $totalOperacionGravada2 += $afecto;
                $totalImporteTotal2 += $total;
                $totalIgv2 += $igv;
                // Calcular detracción usando percentDetraction
                // $montoDetraccion = isset($moviment->percentDetraction) ? round($totalDetalle * ($moviment->percentDetraction / 100), 2) : 0;
                // $saldoNeto = $totalDetalle - $montoDetraccion;

                // Sumar totales

                // Asignar tipo de documento en base al prefijo del correlativo
                $correlativo = $notacredito->number;
                $tipoDocumento = '';
                $serie = '';
                $numeroC = '';

                if (str_starts_with($correlativo, 'B')) {
                    $tipoDocumento = 'BOLETA DE VENTA'; // Boleta
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'T')) {
                    $tipoDocumento = 'TICKET VENTA'; // Factura
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'F')) {
                    $tipoDocumento = 'FACTURA VENTA'; // Factura
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'FC')) {
                    $tipoDocumento = 'NOTA DE CRÉDITO'; // Nota de crédito para factura
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                } elseif (str_starts_with($correlativo, 'BC')) {
                    $tipoDocumento = 'NOTA DE CRÉDITO'; // Nota de crédito para boleta
                    $serie = substr($correlativo, 0, 4);
                    $numeroC = substr($correlativo, 5);
                }

                //ESTO ES PARA VENTA
                $correlativoV = $moviment->sequentialNumber;
                $tipoDocumentoV = '';
                $serieV = '';
                $numeroV = '';

                if (str_starts_with($correlativoV, 'B')) {
                    $tipoDocumento = 'BOLETA DE VENTA'; // Boleta
                    $serieV = substr($correlativoV, 0, 4);
                    $numeroV = substr($correlativoV, 5);
                } elseif (str_starts_with($correlativoV, 'T')) {
                    $tipoDocumento = 'TICKET VENTA'; // Factura
                    $serieV = substr($correlativoV, 0, 4);
                    $numeroV = substr($correlativoV, 5);
                } elseif (str_starts_with($correlativoV, 'F')) {
                    $tipoDocumento = 'FACTURA VENTA'; // Factura
                    $serieV = substr($correlativoV, 0, 4);
                    $numeroV = substr($correlativoV, 5);
                } elseif (str_starts_with($correlativoV, 'FC')) {
                    $tipoDocumento = 'NOTA DE CRÉDITO'; // Nota de crédito para factura
                    $serieV = substr($correlativoV, 0, 4);
                    $numeroV = substr($correlativoV, 5);
                } elseif (str_starts_with($correlativoV, 'BC')) {
                    $tipoDocumento = 'NOTA DE CRÉDITO'; // Nota de crédito para boleta
                    $serieV = substr($correlativoV, 0, 4);
                    $numeroV = substr($correlativoV, 5);
                }
                // Sumar al total de ventas sin nota
                $fechaEmision = $notacredito->created_at ? Carbon::parse($notacredito->created_at)->format('d/m/y') : '';
                $fechaEmisionV = $moviment->paymentDate ? Carbon::parse($moviment->paymentDate)->format('d/m/y') : '';

                $exportDataSinNotaCredito[] = [
                    'NRO' => $iterador++,
                    'FECHA EMISIÓN' => $fechaEmision,
                    'CONDICIÓN' => $condicionNC,
                    'FORMA DE PAGO' => $moviment->typePayment == 'Contado' ? 'CONTADO' : 'CRÉDITO',
                    'FECHA CREDITO / CONTADO' => $dateinstallmentNC,
                    'TIPO C.' => 'NOTA DE CRÉDITO',
                    'SERIE C.' => (string) $serie,
                    'NUMERO C.' => (string) $numeroC,
                    'TIPO DOC' => (string) $personaCliente->typeofDocument,
                    'NUMERO DOC' => (string) $personaCliente->documentNumber ?? '',
                    'RAZON SOCIAL' => (string) $nombreCliente,
                    'GUIAS' => (string) implode(',', $moviment?->detalles()->pluck('guia')->toArray() ?? []),
                    'OPERACIÓN GRAVADA' => (string) number_format(-1 * $afecto, 2, '.', ''),
                    'IGV' => (string) number_format(-1 * $igv, 2, '.', ''),
                    'IMPORTE TOTAL' => (string) number_format(-1 * $total, 2, '.', ''),

                    'FECHA REF. COMPROBANTE' => $fechaEmisionV ?? '',
                    'TIPO REF. COMPROBANTE' => '01',
                    'SERIE REF. COMPROBANTE' => $serieV ?? '',
                    'NÚMERO REF. COMPROBANTE' => $numeroV ?? '',
                ];
            }
        }

// Agregar fila de totales para ventas con nota de crédito
        $exportDataConNotaCredito[] = [
            'NRO' => '',
            'FECHA EMISIÓN' => '',
            'CONDICIÓN' => '',
            'FORMA DE PAGO' => '',
            'FECHA CREDITO / CONTADO' => '',
            'TIPO C.' => '',
            'SERIE C.' => '',
            'NUMERO C.' => '',
            'TIPO DOC' => '',
            'NUMERO DOC' => '',
            'RAZON SOCIAL' => '',
            'GUIAS' => 'TOTAL',
            'OPERACIÓN GRAVADA' => (string) number_format(-1 * $totalOperacionGravada2, 2, '.', ''),
            'IGV' => (string) number_format(-1 * $totalIgv2, 2, '.', ''),
            'IMPORTE TOTAL' => (string) number_format(-1 * $totalImporteTotal2, 2, '.', ''),

            'FECHA REF. COMPROBANTE' => '',

            'SERIE REF. COMPROBANTE' => '',
            'NÚMERO REF. COMPROBANTE' => '',
        ];

// Fila vacía para separar

// // Calcular el total general de ambos tipos de ventas
//         $totalGeneralAfecto = $totalAfectoSinNota + $totalAfectoConNota;
//         $totalGeneralIgv = $totalIgvSinNota + $totalIgvConNota;
        $exportDataConNotaCredito[] = [
            'NRO' => '',
            'FECHA EMISIÓN' => '',
            'CONDICIÓN' => '',
            'FORMA DE PAGO' => '',
            'FECHA CREDITO / CONTADO' => '',
            'TIPO C.' => '',
            'SERIE C.' => '',
            'NUMERO C.' => '',
            'TIPO DOC' => '',
            'NUMERO DOC' => '',
            'RAZON SOCIAL' => '',
            'GUIAS' => '',
            'OPERACIÓN GRAVADA' => '',
            'IGV' => '',
            'IMPORTE TOTAL' => '',
            'FECHA REF. COMPROBANTE' => '',

            'SERIE REF. COMPROBANTE' => '',
            'NÚMERO REF. COMPROBANTE' => '',
        ];
        $exportDataConNotaCredito[] = [
            'NRO' => '',
            'FECHA EMISIÓN' => '',
            'CONDICIÓN' => '',
            'FORMA DE PAGO' => '',
            'FECHA CREDITO / CONTADO' => '',
            'TIPO C.' => '',
            'SERIE C.' => '',
            'NUMERO C.' => '',
            'TIPO DOC' => '',
            'NUMERO DOC' => '',
            'RAZON SOCIAL' => '',
            'GUIAS' => 'TOTALES',
            'OPERACIÓN GRAVADA' => (string) number_format($totalOperacionGravada - $totalOperacionGravada2, 2, '.', ''),
            'IGV' => (string) number_format($totalIgv - $totalIgv2, 2, '.', ''),
            'IMPORTE TOTAL' => (string) number_format($totalImporteTotal - $totalImporteTotal2, 2, '.', ''),

            'FECHA REF. COMPROBANTE' => '',

            'SERIE REF. COMPROBANTE' => '',
            'NÚMERO REF. COMPROBANTE' => '',
        ];

// Combina ambos arrays para exportación
        $exportData = array_merge($exportDataSinNotaCredito, $exportDataConNotaCredito);

        return Excel::download(new VentasExport($exportData, $start, $end), 'reporte_ventas.xlsx');
    }

    public function getStatusFacturacion(Request $request)
    {
        $nombre = $request->input('nombre');

        $fecini = $request->input('fecini', '');
        $fecfin = $request->input('fecfin', '');
        if (empty($nombre)) {
            return response()->json(['error' => 'El campo Nombre es Requerido'], 422);
        }

        $funcion = "getstatusservidor";

        // Construir la URL con los parámetros
        $url = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion' => $funcion,
            'nombresolicitud' => $nombre,
            'empresa_id' => 437,
            'fecini' => $fecini,
            'fecfin' => $fecfin,
        ];
        $url .= '?' . http_build_query($params);

        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($ch);
        $data = [];
        $statusfac = 'NO ENCONTRADO EN EL FACTURADOR';

        // Verificar si ocurrió algún error
        if (curl_errno($ch)) {
            $error = curl_error($ch);

            // Definir la respuesta de error
            $data = [
                "mensaje" => 'Error',
                "nombre-solicitado" => $nombre,
                "response" => [],
                "status" => null,
            ];
            $statusfac = 'NO ENCONTRADO EN EL FACTURADOR';
        } else {
            // Verificar si la respuesta está vacía o no es válida
            if (empty($response)) {

                $data = [
                    "mensaje" => 'Error',
                    "nombre-solicitado" => $nombre,
                    "response" => [],
                    "status" => null,
                ];
                $statusfac = 'NO ENCONTRADO EN EL FACTURADOR';
            } else {
                $responseArray = json_decode($response, true); // Convertir JSON a array

                // Verificar si 'data' existe en la respuesta
                $status = isset($responseArray['data'][0]['descripcion']) ? $responseArray['data'][0]['descripcion'] : null;
                $statusfac = $status;
                $data = [
                    "mensaje" => 'Correcto',
                    "nombre-solicitado" => $nombre,
                    "response" => $responseArray,
                    "status" => $statusfac,
                ];
            }
        }

        // Cerrar cURL
        curl_close($ch);

        return $statusfac;
    }
}
