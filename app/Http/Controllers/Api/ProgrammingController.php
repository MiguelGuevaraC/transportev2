<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgrammingRequest\IndexProgrammingRequest;
use App\Http\Resources\ProgrammingResource;
use App\Models\Bitacora;
use App\Models\BranchOffice;
use App\Models\CarrierByProgramming;
use App\Models\CarrierGuide;
use App\Models\DetailWorker;
use App\Models\Document;
use App\Models\Programming;
use App\Models\Reception;
use App\Models\Vehicle;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProgrammingController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/programming",
     *     summary="Get all programming",
     *     tags={"Programming"},
     *     description="Show all programming",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of programming",

     *     ),
     *        @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function index(Request $request)
    {
        // Actualizar el estado de los documentos de manera eficiente
        Document::query()->update([
            'status' => DB::raw("CASE
                WHEN dueDate < NOW() THEN 'Vencido'
                ELSE 'Vigente'
            END"),
        ]);

        // Actualizar el estado de las licencias de los trabajadores de manera eficiente
        Worker::query()->update([
            'statusLicencia' => DB::raw("CASE
                WHEN licencia_date IS NULL THEN NULL
                WHEN licencia_date IS NOT NULL AND licencia_date < NOW() THEN 'Vencido'
                WHEN licencia_date IS NOT NULL THEN 'Vigente'
                ELSE statusLicencia
            END"),
        ]);

        // Obtener los parámetros de búsqueda
        $number = $request->input('number');
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $route = $request->input('route');

        $vehicles = $request->input('vehicles');
        $drivers = $request->input('drivers');
        $status = $request->input('status');
        $statusExpense = $request->input('statusExpense');
        $branch_office_id = $request->input('branch_office_id');
        $startDate = $request->input('startDate'); // Para filtrar por la fecha de salida (departureDate)
        $endDate = $request->input('endDate');   // Para filtrar por la fecha de salida (departureDate)

        // Verificar la oficina principal
        if (!empty($branch_office_id) && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }
        //  else {
        //     $branch_office_id = auth()->user()->worker->branchOffice_id;
        //     $branchOffice = BranchOffice::find($branch_office_id);
        // }

        // Validación de la paginación
        $perPage = $request->input('per_page', 10); // Valor por defecto 10
        if (!is_numeric($perPage) || $perPage <= 0) {
            $perPage = 10; // Valor por defecto
        }

        // Construir la consulta de programación
        $programmingQuery = Programming::with([
            'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice',
            'programming',
            'reprogramming',
            // Relación con función de cierre para incluir eliminados
            'detailsWorkers.worker' => function ($query) {
                $query->withTrashed(); // Incluye los registros de trabajadores eliminados de forma suave
            },
            'detailsWorkers.worker.person' => function ($query) {
                $query->withTrashed(); // Incluye los registros de personas eliminadas de forma suave
            },
        ])
            // ->where('branchOffice_id', $branch_office_id)
            ->orderBy('id', 'desc');

        if (!empty($branch_office_id)) {
            $programmingQuery->where('branchOffice_id', $branch_office_id);
        }
        // Filtros adicionales
        if (!empty($number)) {
            $programmingQuery->whereRaw('LOWER(numero) LIKE ?', ['%' . strtolower($number) . '%']);
        }

        if (!empty($origin)) {
            $programmingQuery->whereHas('origin', function ($query) use ($origin) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($origin) . '%']);
            });
        }
        if (!empty($destination)) {
            $programmingQuery->whereHas('destination', function ($query) use ($destination) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($destination) . '%']);
            });
        }

        if (!empty($route)) {
            $routeParts = explode('-', $route);

            // Si sólo se ingresó una parte de la ruta (ej. "Chiclayo")
            if (count($routeParts) === 1) {
                $programmingQuery->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->orWhereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                });
            } elseif (count($routeParts) === 2) {
                // Si se ingresó el formato "Origen-Destino" (ej. "Amazonas-Chiclayo")
                $programmingQuery->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->whereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[1]) . '%']);
                });
            }
        }

        if (!empty($vehicles)) {
            $programmingQuery->where(function ($query) use ($vehicles) {
                $query->whereHas('tract', function ($query) use ($vehicles) {
                    $query->whereRaw('LOWER(currentPlate) LIKE ?', ['%' . strtolower($vehicles) . '%'])
                        ->orWhereRaw('LOWER(numberMtc) LIKE ?', ['%' . strtolower($vehicles) . '%']);
                })->orWhereHas('platform', function ($query) use ($vehicles) {
                    $query->whereRaw('LOWER(currentPlate) LIKE ?', ['%' . strtolower($vehicles) . '%'])
                        ->orWhereRaw('LOWER(numberMtc) LIKE ?', ['%' . strtolower($vehicles) . '%']);
                });
            });
        }

        if (!empty($drivers)) {
            $programmingQuery->whereHas('detailsWorkers.worker.person', function ($query) use ($drivers) {
                $query->whereRaw('LOWER(CONCAT(names, " ", fatherSurname, " ", motherSurname)) LIKE ?', ['%' . strtolower($drivers) . '%']);
            });
        }

        if (!empty($status)) {
            $programmingQuery->where('status', $status);
        }

        if (!empty($statusExpense)) {
            $programmingQuery->where('statusLiquidacion', $statusExpense);
        }

        if (!empty($startDate) && !empty($endDate)) {
            // Comparación entre dos fechas, solo considerando el año, mes y día
            $programmingQuery->whereBetween(DB::raw('DATE(departureDate)'), [$startDate, $endDate]);
        } elseif (!empty($startDate)) {
            // Comparación de fecha de inicio, solo considerando el año, mes y día
            $programmingQuery->whereDate('departureDate', '>=', $startDate);
        } elseif (!empty($endDate)) {
            // Comparación de fecha de fin, solo considerando el año, mes y día
            $programmingQuery->whereDate('departureDate', '<=', $endDate);
        }

        // Agregar paginación
        $list = $programmingQuery->paginate($perPage);

        $list->getCollection()->each(function ($programming) {
            // Año de la programación
            $year = Carbon::parse($programming->created_at)->year;

            // ID del vehículo
            $vehicleId = $programming->tract->serie;

            // ID de la oficina (branchOffice_id) que se usará como parte del número de programación
            $branchOfficeId = str_pad($programming->branchOffice_id, 3, '0', STR_PAD_LEFT); // Aseguramos que tenga 3 dígitos

            // Obtener el contador de programaciones para el vehículo y año
            $count = Programming::whereYear('created_at', $year)
                ->whereHas('tract', fn($query) => $query->where('id', $vehicleId))
                ->count() + 1; // +1 para el número de programación

            // Formatear el número de programación con el año, ID del vehículo y el contador
            $programming->numero = 'P' . str_pad($branchOfficeId, 3, '0', STR_PAD_LEFT) . '-' . $year . '-' . str_pad($vehicleId, 2, '0', STR_PAD_LEFT) . '-' . $ultimo6Caracteres = substr($programming->numero, -8);
            ;
            $programming->updateTotalDriversExpenses();
        });

        return response()->json([
            'total' => $list->total(),
            'data' => $list->items(),
            'current_page' => $list->currentPage(),
            'last_page' => $list->lastPage(),
            'per_page' => $list->perPage(),
            'first_page_url' => $list->url(1),
            'from' => $list->firstItem(),
            'next_page_url' => $list->nextPageUrl(),
            'path' => $list->path(),
            'prev_page_url' => $list->previousPageUrl(),
            'to' => $list->lastItem(),
        ], 200);
    }

    public function list(IndexProgrammingRequest $request)
    {
        $driverId = $request->driver_id;
        $programmingId = $request->programming_id;


        $query = Programming::with(['detailsWorkers'])
            ->where('statusLiquidacion', '=', 'Pendiente');

        if ($driverId) {
            $query->whereHas('detailsWorkers', function ($q) use ($driverId) {
                $q->where('worker_id', $driverId)->whereIn('function', ['driver']);
            });

            if (
                Programming::where('id', $programmingId)->whereHas('detailsWorkers', function ($q) use ($driverId) {
                    $q->where('worker_id', $driverId)->whereIn('function', ['driver']);
                })->exists()
            ) {
                $query->where('id', '>', $programmingId);
            }
        }

        return $this->getFilteredResults($query, $request, Programming::filters, Programming::sorts, ProgrammingResource::class);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/programming",
     *     summary="Create a new programming",
     *     tags={"Programming"},
     *     description="Create a new programming",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Pogramming data",
     *     @OA\JsonContent(
     *
     *
     *         @OA\Property(property="departureDate", type="integer", example="2024-05-08", description="Subcontrato"),
     *         @OA\Property(property="estimatedArrivalDate", type="string", format="date", example="2024-05-08", description="Fecha de inicio de transferencia"),

     *         @OA\Property(property="tract_id", type="integer", example=1, description="ID del tracto"),
     *         @OA\Property(property="platform_id", type="integer", example=2, description="ID de la plataforma"),
     *         @OA\Property(property="origin_id", type="integer", example=1, description="ID de origen"),
     *         @OA\Property(property="destination_id", type="integer", example=2, description="ID de destino"),
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     *
     *         @OA\Property(property="driver_id", type="integer", example=1, description="ID driver"),
     *         @OA\Property(property="copilot_id", type="integer", example=1, description="ID copilot"),
     *         @OA\Property(property="assistant1_id", type="integer", example=1, description="ID assistant 1"),
     *         @OA\Property(property="assistant2_id", type="integer", example=1, description="ID assistant 2"),
     *         @OA\Property(property="assistant3_id", type="integer", example=1, description="ID assistant 3"),
     *         @OA\Property(property="assistant4_id", type="integer", example=1, description="ID assistant 4"),
     *@OA\Property(property="isload", type="integer", example=0, description="isload"),
     *               @OA\Property(
     *                 property="detailsReceptions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="idDetail", type="integer", example="1"),
     *
     *                  ),
     *             ),

     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Programming created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Programming created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error: The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        // Validación de la solicitud

        $validator = validator()->make($request->all(), [
            'tract_id' => ['required', Rule::exists('vehicles', 'id')->whereNot('status', 'Ocupado')],
            'platform_id' => ['nullable', Rule::exists('vehicles', 'id')->whereNot('status', 'Ocupado')],
            'driver_id' => ['nullable', Rule::exists('workers', 'id')->whereNot('status', 'Ocupado')],
            'copilot_id' => ['nullable', Rule::exists('workers', 'id')->whereNot('status', 'Ocupado')],
            'origin_id' => 'required|exists:places,id',
            'destination_id' => 'required|exists:places,id',
            'branch_office_id' => 'nullable|exists:branch_offices,id',
            'assistant1_id' => 'nullable|exists:workers,id',
            'assistant2_id' => 'nullable|exists:workers,id',
            'assistant3_id' => 'nullable|exists:workers,id',
            'assistant4_id' => 'nullable|exists:workers,id',
            'detailsReceptions' => 'nullable|array',
            'isload' => 'nullable|boolean',

            // Campos del JSON data_tercerizar_programming
            'is_tercerizar_programming' => 'required|boolean',
            'data_tercerizar_programming.driver_names' => 'required_if:is_tercerizar_programming,1|string|max:255',
            'data_tercerizar_programming.vehicle_plate' => 'required_if:is_tercerizar_programming,1|string|max:20',
            'data_tercerizar_programming.company_names' => 'required_if:is_tercerizar_programming,1|string|max:255',
            'data_tercerizar_programming.is_igv' => 'required_if:is_tercerizar_programming,1|boolean',
            'data_tercerizar_programming.monto' => 'required_if:is_tercerizar_programming,1|numeric|min:0',
        ], [
            'tract_id.exists' => 'El tracto seleccionado está ocupado.',
            'platform_id.exists' => 'La plataforma seleccionada está ocupada.',
            'driver_id.exists' => 'El conductor seleccionado está ocupado.',
            'copilot_id.exists' => 'El copiloto seleccionado está ocupado.',

            'data_tercerizar_programming.driver_names.required_if' => 'El nombre del conductor es obligatorio en programación tercerizada.',
            'data_tercerizar_programming.vehicle_plate.required_if' => 'La placa del vehículo es obligatoria en programación tercerizada.',
            'data_tercerizar_programming.company_names.required_if' => 'El nombre de la empresa es obligatorio en programación tercerizada.',
            'data_tercerizar_programming.is_igv.required_if' => 'Debe indicar si incluye IGV en programación tercerizada.',
            'data_tercerizar_programming.monto.required_if' => 'El monto es obligatorio en programación tercerizada.',
        ]);

        // Manejo de la sucursal
        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json(["message" => "Branch Office Not Found"], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice = BranchOffice::find($branch_office_id);
        }

        // Generación del número de programación
        $tipo = 'P' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
        $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);
        $resultado = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM programmings WHERE SUBSTRING(numero, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $data = [
            'numero' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'state' => $request->input('state') ?? 'Generada',
            'departureDate' => $request->input('departureDate') ?? null,
            'estimatedArrivalDate' => $request->input('estimatedArrivalDate') ?? null,
            'tract_id' => $request->input('tract_id'),
            'platForm_id' => $request->input('platform_id'),
            'origin_id' => $request->input('origin_id'),
            'destination_id' => $request->input('destination_id'),
            'branchOffice_id' => $branch_office_id,
            'isload' => $request->input('isload', 0),
            'user_created_id' => Auth::user()->id,

            'is_tercerizar_programming' => $request->input('is_tercerizar_programming'),
            'data_tercerizar_programming' => $request->input('data_tercerizar_programming'),


        ];

        // Creación del objeto Programming
        $object = Programming::create($data);

        // Actualización del estado de los vehículos
        if ($object->tract_id) {
            Vehicle::find($object->tract_id)->update(['status' => 'Ocupado']);
        }
        if ($object->platForm_id) {
            Vehicle::find($object->platForm_id)->update(['status' => 'Ocupado']);
        }

        if ($object) {
            // Asignación de trabajadores al objeto Programming
            $workers = [
                'driver' => $request->input('driver_id'),
                'copilot' => $request->input('copilot_id'),
                'assistant1' => $request->input('assistant1_id'),
                'assistant2' => $request->input('assistant2_id'),
                'assistant3' => $request->input('assistant3_id'),
                'assistant4' => $request->input('assistant4_id'),
            ];

            // Filtrar los IDs que no sean nulos y asignarlos
            $workers = array_filter($workers);
            foreach ($workers as $function => $worker_id) {
                if ($worker_id) {
                    DetailWorker::create([
                        'function' => $function,
                        'programming_id' => $object->id,
                        'worker_id' => $worker_id,
                    ]);

                    $workerProgramming = Worker::find($worker_id);
                    $workerProgramming->update(['status' => 'Ocupado']);
                }
            }

            // Agregar detalles de recepciones si es necesario
            if ($request->input('isload') == 1) {
                $detailsReceptions = $request->input('detailsReceptions');

                foreach ($detailsReceptions as $detail) {

                    $detailReception = CarrierGuide::find($detail['idDetail']);
                    $detailReception->status = 'En Transito';
                    $detailReception->save();
                    if ($detailReception) {
                        $detailReception->update(['programming_id' => $object->id]);
                        CarrierByProgramming::create([
                            'programming_id' => $object->id,
                            'carrier_guide_id' => $detailReception->id,
                        ]);
                    }
                }
            }

            // Cálculo de pesos, cantidades y montos
            $totalWeight = 0;
            $detailQuantity = 0;
            $totalAmount = 0;

            foreach ($object->carrierGuides as $guide) {
                $reception = Reception::find($guide->reception_id);
                if ($reception) {
                    $totalWeight += $reception->netWeight;
                    $detailQuantity += $reception->details->count();
                    $totalAmount += $reception->details->sum('paymentAmount');
                }
            }

            $object->update([
                'totalWeight' => $totalWeight,
                'carrierQuantity' => $object->carrierGuides->count(),
                'detailQuantity' => $detailQuantity,
                'totalAmount' => $totalAmount,
            ]);
        }

        // Retornar la respuesta con la información relacionada
        $object = Programming::with([
            'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice',
            'programming',
            'reprogramming',
            // Relación con función de cierre para incluir eliminados
            'detailsWorkers.worker' => function ($query) {
                $query->withTrashed(); // Incluye los registros de trabajadores eliminados de forma suave
            },
            'detailsWorkers.worker.person' => function ($query) {
                $query->withTrashed(); // Incluye los registros de personas eliminadas de forma suave
            },
        ])->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),     // ID del usuario que realiza la acción
            'record_id' => $object->id,    // El ID del usuario afectado
            'action' => 'POST',         // Acción realizada
            'table_name' => 'programmings', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Guardar Programación', // Descripción de la acción
            'ip_address' => $request->ip(),          // Dirección IP del usuario
            'user_agent' => $request->userAgent(),   // Información sobre el navegador/dispositivo
        ]);

        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/programming/{id}",
     *     summary="Get a programming by ID",
     *     tags={"Programming"},
     *     description="Retrieve a programming by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the programming to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programming found"

     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function show($id)
    {
        $object = Programming::with(
            'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailsWorkers.worker.person',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice',
            'programming'
        )->find($id);
        if (!$object) {
            return response()->json(['message' => 'Programming not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/programming/{id}",
     *     summary="Update an existing programming",
     *     tags={"Programming"},
     *     description="Update an existing programming",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the programming to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Pogramming data",
     *     @OA\JsonContent(
     *
     *
     *         @OA\Property(property="departureDate", type="integer", example="2024-05-08", description="Subcontrato"),
     *         @OA\Property(property="estimatedArrivalDate", type="string", format="date", example="2024-05-08", description="Fecha de inicio de transferencia"),

     *         @OA\Property(property="tract_id", type="integer", example=null, description="ID del tracto"),
     *         @OA\Property(property="platform_id", type="integer", example=null, description="ID de la plataforma"),
     *         @OA\Property(property="origin_id", type="integer", example=1, description="ID de origen"),
     *         @OA\Property(property="destination_id", type="integer", example=2, description="ID de destino"),
     *
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     *         @OA\Property(property="driver_id", type="integer", example=1, description="ID driver"),
     *         @OA\Property(property="copilot_id", type="integer", example=1, description="ID copilot"),
     *         @OA\Property(property="assistant1_id", type="integer", example=1, description="ID assistant 1"),
     *         @OA\Property(property="assistant2_id", type="integer", example=1, description="ID assistant 2"),
     *         @OA\Property(property="assistant3_id", type="integer", example=1, description="ID assistant 3"),
     *         @OA\Property(property="assistant4_id", type="integer", example=1, description="ID assistant 4"),
     *@OA\Property(property="isload", type="integer", example=0, description="isload"),
     *               @OA\Property(
     *                 property="detailsReceptions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="idDetail", type="integer", example="1"),
     *
     *                  ),
     *             ),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Programming updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function update(Request $request, $id)
    {

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'tract_id' => 'nullable|exists:vehicles,id',
            'platform_id' => 'nullable|exists:vehicles,id',
            'origin_id' => 'nullable|exists:places,id',
            'destination_id' => 'nullable|exists:places,id',
            'driver_id' => 'nullable|exists:workers,id',
            'copilot_id' => 'nullable|exists:workers,id',
            'assistant1_id' => 'nullable|exists:workers,id',
            'assistant2_id' => 'nullable|exists:workers,id',
            'assistant3_id' => 'nullable|exists:workers,id',
            'assistant4_id' => 'nullable|exists:workers,id',
            'detailsReceptions' => 'nullable|array',
            'branch_office_id' => 'nullable|exists:branch_offices,id',
            'isload' => 'nullable|boolean',

            // Validación opcional de campos internos del JSON en update
            'is_tercerizar_programming' => 'nullable|boolean',
            'data_tercerizar_programming.driver_names' => 'nullable|string|max:255',
            'data_tercerizar_programming.vehicle_plate' => 'nullable|string|max:20',
            'data_tercerizar_programming.company_names' => 'nullable|string|max:255',
            'data_tercerizar_programming.is_igv' => 'nullable|boolean',
            'data_tercerizar_programming.monto' => 'nullable|numeric|min:0',
        ], [
            // Mensajes personalizados (opcional)
            'tract_id.exists' => 'El tracto seleccionado no existe.',
            'platform_id.exists' => 'La plataforma seleccionada no existe.',
            'driver_id.exists' => 'El conductor seleccionado no existe.',
            'copilot_id.exists' => 'El copiloto seleccionado no existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $object = Programming::find($id);
        if (!$object) {
            return response()->json(['message' => 'Programming not found'], 422);
        }

        // Revertir el estado de los vehículos a "Disponible" si se van a cambiar
        if ($object->tract_id != $request->input('tract_id')) {
            $tract = Vehicle::withTrashed()->find($object->tract_id);
            if ($tract) {
                $tract->status = "Disponible";
                $tract->save();
            }
        }

        if ($object->platForm_id != $request->input('platform_id')) {
            $platform = Vehicle::withTrashed()->find($object->platForm_id);
            if ($platform) {
                $platform->status = "Disponible";
                $platform->save();
            }
        }


        // Actualizar los datos de la programación
        $data = [
            'departureDate' => $request->input('departureDate') ?? null,
            'estimatedArrivalDate' => $request->input('estimatedArrivalDate') ?? null,
            'tract_id' => $request->input('tract_id') ?? null,
            'platForm_id' => $request->input('platform_id') ?? null,
            'origin_id' => $request->input('origin_id') ?? null,
            'destination_id' => $request->input('destination_id') ?? null,
            'branch_office_id' => $request->input('branch_office_id') ?? null,
            'isload' => $request->input('isload') ?? null,
            'user_edited_id' => Auth::user()->id,
            'is_tercerizar_programming' => $request->input('is_tercerizar_programming'),
            'data_tercerizar_programming' => $request->input('data_tercerizar_programming'),

        ];

        $object->update($data);
        $object = Programming::find($id);
        // Actualizar el estado de los nuevos vehículos a "Ocupado"
        if ($object->tract_id) {
            Vehicle::withTrashed()->find($object->tract_id)->update(['status' => 'Ocupado']);
        }
        if ($object->platForm_id) {
            Vehicle::withTrashed()->find($object->platForm_id)->update(['status' => 'Ocupado']);
        }

        if ($object) {
            // Actualizar los trabajadores asignados
            $workers = [
                'driver' => $request->input('driver_id'),
                'copilot' => $request->input('copilot_id'),
                'assistant1' => $request->input('assistant1_id'),
                'assistant2' => $request->input('assistant2_id'),
                'assistant3' => $request->input('assistant3_id'),
                'assistant4' => $request->input('assistant4_id'),
            ];

            $currentDetailIds = $object->detailsWorkers()->pluck('id')->toArray();
            $object->detailsWorkers()->with('worker')->get()->each(function ($detailWorker) {
                $detailWorker->worker->update(['status' => 'Disponible']);
            });

            $newDetailIds = [];

            foreach ($workers as $function => $worker_id) {
                if ($worker_id) {
                    $detail = DetailWorker::where('programming_id', $object->id)
                        ->where('function', $function)
                        ->first();

                    if ($detail) {
                        $detail->update([
                            'worker_id' => $worker_id,
                        ]);
                        Worker::find($worker_id)->update(['status' => 'Ocupado']);
                        $newDetailIds[] = $detail->id;
                    } else {
                        $newDetail = DetailWorker::create([
                            'function' => $function,
                            'programming_id' => $object->id,
                            'worker_id' => $worker_id,
                        ]);
                        Worker::find($worker_id)->update(['status' => 'Ocupado']);
                        $newDetailIds[] = $newDetail->id;
                    }
                }
            }

            $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
            DetailWorker::whereIn('id', $detailsToDelete)->delete();

            // Actualizar detalles de recepción si es necesario
            if ($request->input('isload') == 1) {
                $currentDetailReceptionIds = $object->carrierGuides()->pluck('id')->toArray(); // IDs de las guías

                // Obtén las guías directamente desde la relación
                $relationGuides = $object->carrierGuides->pluck('id')->toArray(); // IDs de las guías desde la relación

                // Obtén las guías directamente desde la tabla 'carrier_by_programmings'
                $dbGuides = DB::table('carrier_by_programmings')
                    ->where('programming_id', $id)
                    ->pluck('carrier_guide_id') // Obtén solo los IDs de las guías
                    ->toArray();                // Convertirlo en array nativo

                // Combina ambas fuentes y elimina duplicados
                $currentDetailReceptionIds = array_merge($currentDetailReceptionIds, $dbGuides); // Combina los arrays
                $currentDetailReceptionIds = array_unique($currentDetailReceptionIds);           // Elimina los duplicados
                $currentDetailReceptionIds = array_values($currentDetailReceptionIds);           // Reindexa el array para que los índices sean consecutivos

                // Si prefieres trabajar con una colección en lugar de un array nativo
                $currentDetailReceptionIds = collect($currentDetailReceptionIds)
                    ->merge($dbGuides)
                    ->unique()
                    ->values()
                    ->toArray(); // Convertir la colección a un array

                $detailsReceptions = $request->input('detailsReceptions', []);

                $newReceptionDetailIds = [];

                foreach ($detailsReceptions as $detailData) {
                    if (isset($detailData['idDetail']) && $detailData['idDetail'] !== 'null') {
                        $newReceptionDetailIds[] = $detailData['idDetail'];
                        $detailReception = CarrierGuide::find($detailData['idDetail']);

                        if ($detailReception) {
                            $detailReception->update(['status' => 'En Transito']);
                            $detailReception->update(['programming_id' => $object->id]);

                            $carrierByProgramming = CarrierByProgramming::where([
                                'programming_id' => $object->id,
                                'carrier_guide_id' => $detailReception->id,
                            ])->first();

                            if ($carrierByProgramming) {

                                // Si ya existe, actualiza
                                $carrierByProgramming->update([
                                    'programming_id' => $object->id,
                                    'carrier_guide_id' => $detailReception->id,
                                ]);
                            } else {
                                // Si no existe, crea uno nuevo
                                CarrierByProgramming::create([
                                    'programming_id' => $object->id,
                                    'carrier_guide_id' => $detailReception->id,
                                ]);
                            }
                        }
                    }
                }

                // Remover recepciones no incluidas en la nueva lista

                $detailsToDelete = array_diff($currentDetailReceptionIds, $newReceptionDetailIds);

                if (!empty($detailsToDelete)) {
                    // Actualiza CarrierByProgramming para eliminar las relaciones
                    CarrierByProgramming::whereIn('carrier_guide_id', $detailsToDelete)
                        ->where('programming_id', $object->id)
                        ->delete(); // Elimina las relaciones antiguas

                    // También actualiza programming_id en CarrierGuide
                    CarrierGuide::whereIn('id', $detailsToDelete)->update(
                        [
                            'programming_id' => null,
                            'status' => 'Pendiente',
                        ]
                    );
                }
            } else {
                $object->carrierGuides()->update(['programming_id' => null]);
            }

            // Recalcular pesos, cantidades y montos
            $totalWeight = 0;
            $detailQuantity = 0;
            $totalAmount = 0;

            foreach ($object->carrierGuides as $guide) {
                $reception = Reception::find($guide->reception_id);
                if ($reception) {
                    $totalWeight += $reception->netWeight;
                    $detailQuantity += $reception->details->count();
                    $totalAmount += $reception->details->sum('paymentAmount');
                }
            }

            $object->update([
                'totalWeight' => $totalWeight,
                'carrierQuantity' => $object->carrierGuides->count(),
                'detailQuantity' => $detailQuantity,
                'totalAmount' => $totalAmount,
            ]);
        }

        // Devolver la respuesta con la información relacionada
        $object = Programming::with([
           'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice',
            'programming',
            'reprogramming',
            // Relación con función de cierre para incluir eliminados
            'detailsWorkers.worker' => function ($query) {
                $query->withTrashed(); // Incluye los registros de trabajadores eliminados de forma suave
            },
            'detailsWorkers.worker.person' => function ($query) {
                $query->withTrashed(); // Incluye los registros de personas eliminadas de forma suave
            },
        ])->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),     // ID del usuario que realiza la acción
            'record_id' => $object->id,    // El ID del usuario afectado
            'action' => 'PUT',          // Acción realizada
            'table_name' => 'programmings', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Editar Programación', // Descripción de la acción
            'ip_address' => $request->ip(),         // Dirección IP del usuario
            'user_agent' => $request->userAgent(),  // Información sobre el navegador/dispositivo
        ]);
        return response()->json($object, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/reprogramming/{id}",
     *     summary="reprogram an existing programming",
     *     tags={"Programming"},
     *     description="reprogram an existing programming",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the programming to reprogram",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Pogramming data",
     *     @OA\JsonContent(
     *
     *         @OA\Property(property="estimatedArrivalDate", type="string", format="date", example="2024-05-08", description="Fecha de inicio de transferencia"),
     *         @OA\Property(property="destination_id", type="integer", example=2, description="ID de destino"),
     *
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),



     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Programming  successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function reprogramming(Request $request, $id)
    {

        $object = Programming::find($id);
        if (!$object) {
            return response()->json(['message' => 'Programming not found'], 422);
        }

        $validator = validator()->make($request->all(), [
            'destination_id' => 'required|exists:places,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

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
        $tipo = 'P' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);

        $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);

        $resultado = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM programmings WHERE SUBSTRING(numero, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $originReprogramming = $object->destination_id;

        $data = [

            'numero' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'state' => 'Generada',
            'departureDate' => Carbon::now() ?? null,
            'estimatedArrivalDate' => $request->input('estimatedArrivalDate') ?? null,
            'tract_id' => $object->tract_id ?? null,
            'platForm_id' => $object->platForm_id ?? null,
            'origin_id' => $originReprogramming ?? null,
            'destination_id' => $request->input('destination_id') ?? null,
            'branchOffice_id' => $branch_office_id,
            'isload' => 0,   //No lleva carga
            'programming_id' => $id, //No lleva carga
        ];

        $objectReprogramming = Programming::create($data);

        if ($objectReprogramming) {

            $workers = [
                'driver' => $object->detailsWorkers->where('function', 'driver')->pluck('worker_id')->first(),
                'copilot' => $object->detailsWorkers->where('function', 'copilot')->pluck('worker_id')->first(),
                'assistant1' => $object->detailsWorkers->where('function', 'assistant1')->pluck('worker_id')->first(),
                'assistant2' => $object->detailsWorkers->where('function', 'assistant2')->pluck('worker_id')->first(),
                'assistant3' => $object->detailsWorkers->where('function', 'assistant3')->pluck('worker_id')->first(),
            ];

            // Filtrar los IDs que no sean nulos
            $workers = array_filter($workers);

            $i = 0;
            foreach ($workers as $function => $worker_id) {

                if (Worker::find($worker_id)) {
                    $detail = DetailWorker::create([
                        'function' => $function,
                        'programming_id' => $objectReprogramming->id,
                        'worker_id' => $worker_id,
                    ]);
                }

                $workerProgramming = Worker::find($worker_id);
                $workerProgramming->update(['status' => 'Ocupado']);
                $i++;
            }

        }

        $objectReprogramming = Programming::with(
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailsWorkers.worker.person',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice',
            'programming'
        )->find($objectReprogramming->id);

        Bitacora::create([
            'user_id' => Auth::id(),               // ID del usuario que realiza la acción
            'record_id' => $objectReprogramming->id, // El ID del usuario afectado
            'action' => 'POST',                   // Acción realizada
            'table_name' => 'programmings',           // Tabla afectada
            'data' => json_encode($objectReprogramming),
            'description' => 'Reprogramación',     // Descripción de la acción
            'ip_address' => $request->ip(),        // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        return response()->json($objectReprogramming, 200);

    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/programming/{id}",
     *     summary="Delete a Programming",
     *     tags={"Programming"},
     *     description="Delete a Programming by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Programming to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programming deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *        @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function destroy(Request $request, $id)
    {
        try {
            $object = Programming::find($id);
            if (!$object) {
                return response()->json(['message' => 'Programming not found'], 422);
            }

            // Actualizar el estado de los trabajadores a 'Disponible'
            $object->detailsWorkers()->with('worker')->get()->each(function ($detailWorker) {
                $detailWorker->worker->update(['status' => 'Disponible']);
            });

            // Actualizar el estado de los vehículos a 'Disponible' si están asignados
            if ($object->tract_id != null) {
                Vehicle::find($object->tract_id)->update(['status' => 'Disponible']);
            }
            if ($object->platForm_id != null) {
                Vehicle::find($object->platForm_id)->update(['status' => 'Disponible']);
            }

            // Guardar el ID del usuario que realiza la eliminación
            $object->user_deleted_id = Auth::user()->id;

            // Eliminar cada CarrierGuide asociado a la programación
            // $object->carrierGuides()->each(function ($carrierGuide) {
            //     $carrierGuide->delete();
            // });

            // Actualizar el campo programming_id de cada guía asociada a null
            CarrierGuide::where('programming_id', $object->id)->update(['programming_id' => null]);

            $object->save();
            $object2 = Programming::with(
                'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
                'origin',
                'destination',
                'detailsWorkers',
                'detailsWorkers.worker.person',
                'detailReceptions',
                'carrierGuides.reception.details',
                'branchOffice'
            )->find($object->id);

            $object->delete();
            Bitacora::create([
                'user_id' => Auth::id(),     // ID del usuario que realiza la acción
                'record_id' => $object2->id,   // El ID del usuario afectado
                'action' => 'DELETE',       // Acción realizada
                'table_name' => 'programmings', // Tabla afectada
                'data' => json_encode($object2),
                'description' => 'Eliminar Programación', // Descripción de la acción
                'ip_address' => $request->ip(),           // Dirección IP del usuario
                'user_agent' => $request->userAgent(),    // Información sobre el navegador/dispositivo
            ]);
            return response()->json(['message' => 'Programming deleted successfully']);

        } catch (\Exception $e) {
            // Registrar el error en el log
            Log::error("Error deleting Programming with ID {$id}: " . $e->getMessage());

            // return response()->json(['message' => 'Ocurrió un error mientras se elimina'], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/transportedev/public/api/finishProgramming/{id}",
     *     summary="Get a programming by ID",
     *     tags={"Programming"},
     *     description="Retrieve a programming by its ID and mark it as finished",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the programming to retrieve and mark as finished",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Programming found and marked as finished",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="status", type="string", example="Finalizado"),

     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Programming not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Programming not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function finishProgramming(Request $request, $id)
    {
        $object = Programming::find($id);
        if (!$object) {
            return response()->json(['message' => 'Programming not found'], 422);
        }
        $object->detailsWorkers()->with('worker')->get()->each(function ($detailWorker) {
            $detailWorker->worker->update(['status' => 'Disponible']);
        });
        $object->status = 'Finalizado';
        $object->actualArrivalDate = now();
        $object->save();
        if ($object->tract_id != null) {
            // Incluir vehículos eliminados lógicamente usando withTrashed()
            $tractVehicle = Vehicle::withTrashed()->find($object->tract_id);

            // Verificar si el vehículo existe antes de intentar actualizar su estado
            if ($tractVehicle) {
                $tractVehicle->update(['status' => 'Disponible']);
            }
        }

        if ($object->platForm_id != null) {
            // Incluir vehículos eliminados lógicamente usando withTrashed()
            $platformVehicle = Vehicle::withTrashed()->find($object->platForm_id);

            // Verificar si el vehículo existe antes de intentar actualizar su estado
            if ($platformVehicle) {
                $platformVehicle->update(['status' => 'Disponible']);
            }
        }

        $object->carrierGuides()->update(['status' => 'Entregado']); // O el estado que desees

        $object = Programming::with([
           'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice',
            'programming',
            'reprogramming',
            // Relación con función de cierre para incluir eliminados
            'detailsWorkers.worker' => function ($query) {
                $query->withTrashed(); // Incluye los registros de trabajadores eliminados de forma suave
            },
            'detailsWorkers.worker.person' => function ($query) {
                $query->withTrashed(); // Incluye los registros de personas eliminadas de forma suave
            },
        ])->find($object->id);
        Bitacora::create([
            'user_id' => Auth::id(),     // ID del usuario que realiza la acción
            'record_id' => $object->id,    // El ID del usuario afectado
            'action' => 'DELETE',       // Acción realizada
            'table_name' => 'programmings', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Finalizando Programación', // Descripción de la acción
            'ip_address' => $request->ip(),              // Dirección IP del usuario
            'user_agent' => $request->userAgent(),       // Información sobre el navegador/dispositivo
        ]);
        return response()->json($object, 200);
    }

    public function getPlatformByVehicleId($vehicleId)
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return response()->json(['message' => 'Vehículo no Encontrado'], 422);
        }
        $programming = Programming::select('tract_id', 'platForm_id')
            ->join('vehicles', 'programmings.tract_id', '=', 'vehicles.id')
            ->where('vehicles.id', $vehicleId)
            ->whereNotNull('programmings.platForm_id')
            ->orderBy('programmings.id', 'desc')
            ->first(); // Cambiamos LIMIT 1 por first()

        // Estructura de respuesta
        if ($programming) {
            return response()->json([
                'tract_id' => $programming->tract_id,
                'platForm_id' => $programming->platForm_id,
            ], 200); // Código 200
        } else {
            return response()->json([
                'tract_id' => (int) $vehicleId, // Convertir a cadena
                'platForm_id' => null,
            ], 200); // Código 200
        }
    }

    public function programmingLiquidado(Request $request, $id)
    {
        $object = Programming::find($id);
        if (!$object) {
            return response()->json(['message' => 'Programación no encontrada'], 422);
        }
        //AGREGAR UNA VALIDACIÓN

        if ($object->statusLiquidacion == 'Liquidada') {
            return response()->json(['message' => 'Programación ya liquidada'], 422);
        }

        $object->dateLiquidacion = now();
        $object->statusLiquidacion = 'Liquidada';
        $object->save();
        $object = Programming::with(
        'tract',
            'platform',
            'tract.typeCarroceria',
            'tract.typeCarroceria.typeCompany',
            'tract.photos',
            'platform.photos',
            'origin',
            'destination',
            'detailsWorkers',
            'detailsWorkers.worker.person',
            'detailReceptions',
            'carrierGuides.reception.details',
            'branchOffice'
        )->find($id);

        Bitacora::create([
            'user_id' => Auth::id(),     // ID del usuario que realiza la acción
            'record_id' => $object->id,    // El ID del usuario afectado
            'action' => 'DELETE',       // Acción realizada
            'table_name' => 'programmings', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Liquidado Programación', // Descripción de la acción
            'ip_address' => $request->ip(),            // Dirección IP del usuario
            'user_agent' => $request->userAgent(),     // Información sobre el navegador/dispositivo
        ]);

        return response()->json(
            $object
            ,
            200
        ); // Código 200
    }
}
