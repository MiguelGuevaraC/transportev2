<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarrierGuideRequest\SubcontractDataRequest;
use App\Models\Bitacora;
use App\Models\BranchOffice;
use App\Models\CarrierGuide;
use App\Models\District;
use App\Models\Motive;
use App\Models\Person;
use App\Models\Vehicle;
use App\Models\Worker;
use App\Services\CarrierGuideService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CarrierGuideController extends Controller
{

    protected $carrierGuideService;

    public function __construct(CarrierGuideService $CarrierGuideService)
    {
        $this->carrierGuideService = $CarrierGuideService;
    }
    
    /**
     * @OA\Get(
     *     path="/transportev2/public/api/carrierGuide",
     *     summary="Get all carrierGuide",
     *     tags={"CarrierGuide"},
     *     description="Show all carrierGuide",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of carrierGuide",

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

    public function applyNameFilter($query, $searchTermUpper)
    {
        // Crear un campo virtual concatenado
        $query->where(DB::raw("UPPER(CONCAT_WS(' ', names, fatherSurname, motherSurname, businessName))"), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(documentNumber)'), 'LIKE', '%' . $searchTermUpper . '%');
    }
    public function index(Request $request)
    {
        // Obtener el branch_office_id desde la solicitud o usar el del usuario autenticado
        $branch_office_id = $request->input('branch_office_id');
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

        // Paginación
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);

        if (! is_numeric($perPage) || (int) $perPage <= 0) {
            $perPage = 10;
        }

        // FILTROS
        $numero      = $request->input('numero');
        $id_sucursal = $request->input('branch_office_id');

        $document        = $request->input('document');
        $startDate       = $request->input('startDate');
        $endDate         = $request->input('endDate');
        $route           = $request->input('route');
        $typeGuia        = $request->input('typeGuia');
        $statusGuia      = $request->input('statusGuia');
        $statusFacturado = $request->input('statusFacturado');
        $observation     = $request->input('observation');
        $vehicles        = $request->input('vehicles');
        $drivers         = $request->input('drivers');

        $nombreClientePaga  = $request->input('nombreClientePaga');
        $nombreRemitente    = $request->input('nombreRemitente');
        $nombreDestinatario = $request->input('nombreDestinatario');

        // Iniciar consulta base
        $carrierGuides = CarrierGuide::with('tract', 'platform', 'motive', 'origin', 'destination', 'sender', 'recipient', 'branchOffice',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
            'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        );

        // Aplicación de los filtros con comparaciones en minúsculas
        if (! empty($numero)) {
            $carrierGuides->whereRaw('LOWER(numero) LIKE ?', ['%' . strtolower($numero) . '%']);
        }

        if (! empty($document)) {
            $carrierGuides->whereRaw('LOWER(document) LIKE ?', ['%' . strtolower($document) . '%']);
        }

        if (! empty($nombreClientePaga)) {
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
        if (! empty($nombreRemitente)) {
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
        if (! empty($nombreDestinatario)) {
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

        if (! empty($drivers)) {
            $lowerDrivers = strtolower($drivers);
            $carrierGuides->where(function ($query) use ($lowerDrivers) {
                $query->whereHas('driver.person', function ($q) use ($lowerDrivers) {
                    $q->whereRaw("LOWER(CONCAT(names, ' ', fatherSurname, ' ', motherSurname)) LIKE ?", ["%{$lowerDrivers}%"]);
                })->orWhereHas('copilot.person', function ($q) use ($lowerDrivers) {
                    $q->whereRaw("LOWER(CONCAT(names, ' ', fatherSurname, ' ', motherSurname)) LIKE ?", ["%{$lowerDrivers}%"]);
                });
            });
        }

        if (! empty($route)) {
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

        if (! empty($vehicles)) {
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

        if (! empty($typeGuia)) {
            $carrierGuides->whereRaw('LOWER(type) = ?', [strtolower($typeGuia)]);
        }
        if (! empty($id_sucursal)) {
            $carrierGuides->where('branchOffice_id', $id_sucursal);
        }

        if (! empty($statusGuia)) {
            $carrierGuides->whereRaw('LOWER(status) = ?', [strtolower($statusGuia)]);
        }

        if (! empty($statusFacturado)) {
            $carrierGuides->whereRaw('LOWER(status_facturado) = ?', [strtolower($statusFacturado)]);
        }

        if (! empty($observation)) {
            $carrierGuides->whereRaw('LOWER(observation) LIKE ?', ['%' . strtolower($observation) . '%']);
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $carrierGuides->whereBetween(DB::raw('DATE(transferStartDate)'), [$startDate, $endDate]);
        } elseif (! empty($startDate)) {
            $carrierGuides->whereDate('transferStartDate', '>=', $startDate);
        } elseif (! empty($endDate)) {
            $carrierGuides->whereDate('transferStartDate', '<=', $endDate);
        }

        // Ordenar y paginar
        $carrierGuides = $carrierGuides->orderBy(DB::raw('CAST(SUBSTRING(numero, 6) AS UNSIGNED)'), 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'total'          => $carrierGuides->total(),
            'data'           => $carrierGuides->items(),
            'current_page'   => $carrierGuides->currentPage(),
            'last_page'      => $carrierGuides->lastPage(),
            'per_page'       => $carrierGuides->perPage(),
            'pagination'     => $perPage,
            'first_page_url' => $carrierGuides->url(1),
            'from'           => $carrierGuides->firstItem(),
            'next_page_url'  => $carrierGuides->nextPageUrl(),
            'path'           => $carrierGuides->path(),
            'prev_page_url'  => $carrierGuides->previousPageUrl(),
            'to'             => $carrierGuides->lastItem(),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/transportev2/public/api/carrierGuide",
     *     summary="Create a new carrierGuide",
     *     tags={"CarrierGuide"},
     *     description="Create a new carrierGuide",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Reception data",
     *     @OA\JsonContent(
     *
     *
     *         @OA\Property(property="subContract", type="integer", example=null, description="Subcontrato"),
     *         @OA\Property(property="transferStartDate", type="string", format="date-time", example=null, description="Fecha de inicio de transferencia"),
     *
     *             @OA\Property(property="transferDateEstimated", type="string", format="date-time", example=null, description="Fecha de inicio de transferencia"),
     *         @OA\Property(property="tract_id", type="integer", example=1, description="ID del tracto"),
     *         @OA\Property(property="platform_id", type="integer", example=null, description="ID de la plataforma"),
     *         @OA\Property(property="origin_id", type="integer", example=1, description="ID de origen"),
     *         @OA\Property(property="destination_id", type="integer", example=2, description="ID de destino"),
     *         @OA\Property(property="sender_id", type="integer", example=1, description="ID del remitente"),
     *         @OA\Property(property="recipient_id", type="integer", example=2, description="ID del destinatario"),
     *       @OA\Property(property="reception_id", type="integer", example=1, description="ID Reception"),
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     *          @OA\Property(property="payResponsible_id", type="integer", example=1, description="ID del responsable de pago"),
     *         @OA\Property(property="driver_id", type="integer", example=1, description="ID del conductor"),
     * @OA\Property(property="copilot_id", type="integer", example=1, description="ID del copilot"),
     * @OA\Property(property="subcontract_id", type="integer", example=1, description="ID del Subcontract"),
     *
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Reception created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Reception created successfully")
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

        $validator = validator()->make($request->all(), [
            'ubigeoStart'       => 'nullable',
            'ubigeoEnd'         => 'nullable',
            'addressStart'      => 'required',
            'addressEnd'        => 'required',
            'motive_id'         => 'required|exists:motives,id',
        
            'tract_id' => 'nullable|sometimes|exists:vehicles,id|required_without:subcontract_id',

            'platform_id'       => 'nullable|exists:vehicles,id',
        
            'origin_id'         => 'required|exists:places,id',
            'destination_id'    => 'required|exists:places,id',
        
            'sender_id'         => 'required|exists:people,id',
            'recipient_id'      => 'required|exists:people,id',
        
            'payResponsible_id' => 'required|exists:people,id',
            'reception_id'      => 'required|exists:receptions,id',
            'branch_office_id'  => 'nullable|exists:branch_offices,id',
        
            'driver_id'         => 'nullable|sometimes|required_without:subcontract_id|exists:workers,id',
            'copilot_id'        => 'nullable|exists:workers,id',
        
            'subcontract_id'    => 'nullable|exists:subcontracts,id',
        
            'districtStart_id'  => 'required|exists:districts,id',
            'districtEnd_id'    => 'required|exists:districts,id',
        ]);
        
        
        

        if ($validator->fails()) {
            Bitacora::create([
                'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
                'record_id'   => null,             // El ID del usuario afectado
                'action'      => 'POST',           // Acción realizada
                'table_name'  => 'carrier_guides', // Tabla afectada
                'data'        => null,
                'description' => 'Error al Guardar GRT: ' . $validator->errors()->first(), // Descripción de la acción
                'ip_address'  => $request->ip(),                                           // Dirección IP del usuario
                'user_agent'  => $request->userAgent(),                                    // Información sobre el navegador/dispositivo
            ]);
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $remitenteguia = Person::find($request->input('sender_id'));
        if ($remitenteguia) {
            if ($remitenteguia->documentNumber == "20605597484") {
                return response()->json(['error' => 'Transportista no debe ser igual al Remitente'], 422);
            }
        }

        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json(["message" => "Branch Office Not Found"], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice     = BranchOffice::find($branch_office_id);
        }

        if ($branchOffice) {
            $tipo = 'V' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
        } else {
            return response()->json(['error' => 'Branch Office not found'], 422);
        }

        $motive       = Motive::find($request->input('motive_id'));
        $motivoNombre = '';
        $motivoCode   = '';
        if ($motive->id == 13) {
            $motivoNombre = $request->input('motivo') ?? $motive->name;
            $motivoCode   = $motive->code;
        } else {
            $motivoNombre = $motive->name;
            $motivoCode   = $motive->code;
        }

        $branch_office_id = $request->input('branch_office_id');

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
        $tipo = 'V' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
        $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);

        $resultado    = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM carrier_guides WHERE SUBSTRING(numero, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $vehicle = Vehicle::find($request->input('tract_id'));

        //
        $document = $request->input('document') ?? null;
        if ($document != null) {
            $document = $this->algoritmoanexos($document);

            // Verifica que el 'sender_id' esté presente en la solicitud antes de buscar la persona.
            $senderId = $request->input('sender_id');
            if ($senderId) {
                $persona = Person::find($senderId);

                // Si la persona existe, verifica que el tipo de documento sea "ruc" (sin importar mayúsculas/minúsculas).
                if ($persona && strtolower($persona->typeofDocument) !== 'ruc') {
                    return response()->json(['error' => 'Si se agregó Documento Anexo, el Remitente debe ser una Empresa'], 422);
                }
            }
        }
        if ($request->input('type') != "Electronica") {
            $correlativo_manual = $request->input('serie') . "-" . $request->input('number');
            $correlativo_manual = $this->algoritmoanexos($correlativo_manual);

            $exists = Carrierguide::where('numero', $correlativo_manual)
                ->where('status_facturado', '!=', 'Anulada')
                ->where('type', 'manual')
                ->exists();
            if ($exists) {
                return response()->json(['error' => 'Este número ya se tiene registrado'], 422);
            }
        }

        $data = [
            // 'numero' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'status'                => $request->input('status') ?? 'Pendiente',
            'document'              => $document,
            'observation'           => $request->input('observation') ?? '-',
            'subContract'           => $request->input('subContract') ?? null,
            'transferStartDate'     => $request->input('transferStartDate') ?? null,
            'transferDateEstimated' => $request->input('transferDateEstimated') ?? null,

            'ubigeoStart'           => $request->input('ubigeoStart') ?? null,
            'ubigeoEnd'             => $request->input('ubigeoEnd') ?? null,
            'addressStart'          => $request->input('addressStart') ?? null,
            'addressEnd'            => $request->input('addressEnd') ?? null,
            'motive_id'             => $request->input('motive_id') ?? null,

            'districtStart_id'      => $request->input('districtStart_id') ?? null,
            'districtEnd_id'        => $request->input('districtEnd_id') ?? null,

            'modalidad'                => '01',//PUBLICO
            'motivo'                => $motivoNombre,
            'codemotivo'            => $motivoCode,
            'placa'                 => $vehicle->currentPlate ?? '-',

            'tract_id'              => $request->input('tract_id') ?? null,
            'platform_id'           => $request->input('platform_id') ?? null,
            'origin_id'             => $request->input('origin_id') ?? null,
            'destination_id'        => $request->input('destination_id') ?? null,
            'sender_id'             => $request->input('sender_id') ?? null,
            'recipient_id'          => $request->input('recipient_id') ?? null,
            'reception_id'          => $request->input('reception_id') ?? null,
            'reasonForTransfer'     => $request->input('reasonForTransfer') ?? null,

            'payResponsible_id'     => $request->input('payResponsible_id') ?? null,
            'driver_id'             => $request->input('driver_id') ?? null,
            'copilot_id'            => $request->input('copilot_id') ?? null,
            'subcontract_id'        => $request->input('subcontract_id') ?? null,
            'branchOffice_id'       => $branch_office_id,
            'user_created_id'       => Auth::user()->id,
        ];

        $object = CarrierGuide::create($data);

        $object->ubigeoStart = District::find($request->input('districtStart_id'))->ubigeo_code;
        $object->ubigeoEnd   = District::find($request->input('districtEnd_id'))->ubigeo_code;
        $object->save();

        $object->type = $request->input('type') ?? 'Electronica';

        $object->number = $request->input('number');
        $object->serie  = $request->input('serie');

        $object->save();

        // Verificar si el tipo del objeto no es "Electronica"
        if ($object->type != 'Electronica') {
            // Generar el correlativo manual usando el método algoritmoanexos
            // $correlativo_manual = $this->algoritmoanexos($correlativo_manual);
            $object->numero = $correlativo_manual;
        } else {
            $object->numero = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
        }
        $object->save();

        if ($object->subcontract_id != null) {
            $validator = Validator::make($request->all(), ['datasubcontrata' => 'required|array','costsubcontract' => 'required|numeric|min:0',]);
            if ($validator->fails()) 
            {return response()->json(['errors' => $validator->errors()->first()], 422);}
            $validator = Validator::make($request->datasubcontrata ?? [], (new SubcontractDataRequest())->rules(), (new SubcontractDataRequest())->messages());
            if ($validator->fails()) {return response()->json(['errors' => "DATA: " . $validator->errors()->first()], 422);}
            $object = $this->carrierGuideService->updatedatasubcontrata($object->id, $request->costsubcontract, $request->datasubcontrata);
        }
        $this->carrierGuideService->updatestockProduct($object->id);

        $object = CarrierGuide::with([
            'tract',
            'platform',
            'motive',
            'origin',
            'destination',
            'sender',
            'recipient',
            'payResponsible',
            'driver',
            'copilot',
            'districtStart.province.department',
            'districtEnd.province.department',
            'copilot.person',
            'subcontract',
            'driver.person',
            'reception' => function ($query) {
                $query->get()->transform(function ($reception) {
                    try {
                        // Verifica que la relación 'firstCarrierGuide' exista
                        if (! isset($reception->firstCarrierGuide)) {
                            $reception->status = 'Sin Guia';
                            // Verifica que 'Programming' exista en 'firstCarrierGuide'
                        } elseif (isset($reception->firstCarrierGuide) && ! isset($reception->firstCarrierGuide->Programming)) {
                            $reception->status = 'Sin Programar';
                        } else {
                            $reception->status = 'Programado';
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al procesar el estado de la recepción al crear una guia: ' . $e->getMessage());
                    }
                    return $reception;
                });
            },
        ])->find($object->id);

        Bitacora::create([
            'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
            'record_id'   => $object->id,      // El ID del usuario afectado
            'action'      => 'POST',           // Acción realizada
            'table_name'  => 'carrier_guides', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Guarda GRT',          // Descripción de la acción
            'ip_address'  => $request->ip(),        // Dirección IP del usuario
            'user_agent'  => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/carrierGuide/{id}",
     *     summary="Get a carrierGuide by ID",
     *     tags={"CarrierGuide"},
     *     description="Retrieve a carrierGuide by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the carrierGuide to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CarrierGuide found",

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
        $object = CarrierGuide::with('tract', 'platform', 'motive',

            'origin', 'destination',
            'sender', 'recipient',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department', 'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        )->find($id);
        if (! $object) {
            return response()->json(['message' => 'Carrier Guide not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/carrierGuide/{id}",
     *     summary="Update an existing carrierGuide",
     *     tags={"CarrierGuide"},
     *     description="Update an existing carrierGuide",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the carrierGuide to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reception data",
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="subContract", type="integer", example=null, description="Subcontrato"),
     *             @OA\Property(property="transferStartDate", type="string", format="date-time", example=null, description="Fecha de inicio de transferencia"),
     *             @OA\Property(property="transferDateEstimated", type="string", format="date-time", example=null, description="Fecha de inicio de transferencia"),
     *
     *             @OA\Property(property="tract_id", type="integer", example=null, description="ID del tracto"),
     *             @OA\Property(property="platform_id", type="integer", example=null, description="ID de la plataforma"),
     *             @OA\Property(property="origin_id", type="integer", example=1, description="ID de origen"),
     *             @OA\Property(property="destination_id", type="integer", example=2, description="ID de destino"),
     *             @OA\Property(property="sender_id", type="integer", example=1, description="ID del remitente"),
     *             @OA\Property(property="recipient_id", type="integer", example=2, description="ID del destinatario"),
     * @OA\Property(property="reception_id", type="integer", example=1, description="ID Reception"),
     *             @OA\Property(property="payResponsible_id", type="integer", example=1, description="ID del responsable de pago"),
     *             @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     *         @OA\Property(property="driver_id", type="integer", example=1, description="ID del conductor"),
     * @OA\Property(property="copilot_id", type="integer", example=1, description="ID del copilot"),
     * @OA\Property(property="subcontract_id", type="integer", example=1, description="ID del Subcontract"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Carrier Guide updated successfully"
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
        $object = CarrierGuide::find($id);
        if (! $object) {
            return response()->json(['message' => 'CarrierGuide not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [

            // 'document' => 'required',
            // 'subContract' => 'required',
            // 'transferStartDate' => 'required',
            // 'carrier' => 'required',

            'tract_id'          => 'nullable|exists:vehicles,id',
            'platform_id'       => 'nullable|exists:vehicles,id',

            'origin_id'         => 'nullable|exists:places,id',
            'destination_id'    => 'nullable|exists:places,id',

            'sender_id'         => 'nullable|exists:people,id',
            'recipient_id'      => 'nullable|exists:people,id',

            'payResponsible_id' => 'nullable|exists:people,id',
            'driver_id'         => 'nullable|exists:workers,id',
            'copilot_id'        => 'nullable|exists:workers,id',
            'subcontract_id'    => 'nullable|exists:subcontracts,id',
            'motive_id'         => 'required|exists:motives,id',

            'reception_id'      => 'nullable|exists:receptions,id',

            'districtStart_id'  => 'required|exists:districts,id',
            'districtEnd_id'    => 'required|exists:districts,id',

        ]);

        if ($validator->fails()) {
            Bitacora::create([
                'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
                'record_id'   => $object->id,      // El ID del usuario afectado
                'action'      => 'PUT',            // Acción realizada
                'table_name'  => 'carrier_guides', // Tabla afectada
                'data'        => json_encode($object),
                'description' => 'Error al Actualizar GRT: ' . $validator->errors()->first(), // Descripción de la acción
                'ip_address'  => $request->ip(),                                              // Dirección IP del usuario
                'user_agent'  => $request->userAgent(),                                       // Información sobre el navegador/dispositivo
            ]);

            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $motive       = Motive::find($request->input('motive_id'));
        $motivoNombre = '';
        $motivoCode   = '';
        if ($motive->id == 13) {
            $motivoNombre = $request->input('motivo') ?? $motive->name;
            $motivoCode   = $motive->code;
        } else {
            $motivoNombre = $motive->name;
            $motivoCode   = $motive->code;
        }

        $vehicle = Vehicle::find($request->input('tract_id'));

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };
        $document = $request->input('document') ?? null;
        if ($document != null) {
            $document = $this->algoritmoanexos($document);
            // Verifica que el 'sender_id' esté presente en la solicitud antes de buscar la persona.
            $senderId = $request->input('sender_id');
            if ($senderId) {
                $persona = Person::find($senderId);

                // Si la persona existe, verifica que el tipo de documento sea "ruc" (sin importar mayúsculas/minúsculas).
                if ($persona && strtolower($persona->typeofDocument) !== 'ruc') {
                    return response()->json(['error' => 'Si se agregó Documento Anexo, el Remitente debe ser una Empresa'], 422);
                }
            }
        }

        if ($request->input('type') != "Electronica") {
            $correlativo_manual = $request->input('serie') . "-" . $request->input('number');
            $correlativo_manual = $this->algoritmoanexos($correlativo_manual);

            $exists = Carrierguide::where('numero', $correlativo_manual)
                ->where('status_facturado', '!=', 'Anulada')
                ->where('type', 'manual')
                ->where('id', '!=', $id) // Excluir el registro actual
                ->exists();
            if ($exists) {
                return response()->json(['error' => 'Este número ya se tiene registrado'], 422);
            }
        }

        $Data = [

            // 'status' => $request->input('status') ?? null,
            'document'              => $document ?? null,
            'subContract'           => $request->input('subContract') ?? null,
            'transferStartDate'     => $request->input('transferStartDate') ?? null,
            'transferDateEstimated' => $request->input('transferDateEstimated') ?? null,
            'observation'           => $request->input('observation') ?? null,

            'carrier'               => $request->input('carrier') ?? null,
            'tract_id'              => $request->input('tract_id') ?? null,
            'platform_id'           => $request->input('platform_id') ?? null,
            'origin_id'             => $request->input('origin_id') ?? null,
            'destination_id'        => $request->input('destination_id') ?? null,
            'sender_id'             => $request->input('sender_id') ?? null,
            'recipient_id'          => $request->input('recipient_id') ?? null,
            'reasonForTransfer'     => $request->input('reasonForTransfer') ?? null,
            'payResponsible_id'     => $request->input('payResponsible_id') ?? null,
            'driver_id'             => $request->input('driver_id') ?? null,
            'copilot_id'            => $request->input('copilot_id') ?? null,
            'subcontract_id'        => $request->input('subcontract_id') ?? null,

            'districtStart_id'      => $request->input('districtStart_id') ?? null,
            'districtEnd_id'        => $request->input('districtEnd_id') ?? null,
            'modalidad'                => '01',//PUBLICO
            'motivo'                => $motivoNombre ?? null,
            'codemotivo'            => $motivoCode ?? null,
            'placa'                 => $vehicle->currentPlate ?? '-' ?? null,

            'branch_office'         => $request->input('branch_office') ?? null,
            'reception_id'          => $request->input('reception_id') ?? null,

            'ubigeoStart'           => $request->input('ubigeoStart') ?? null,
            'ubigeoEnd'             => $request->input('ubigeoEnd') ?? null,
            'addressStart'          => $request->input('addressStart') ?? null,
            'addressEnd'            => $request->input('addressEnd') ?? null,

            'motive_id'             => $request->input('motive_id') ?? null,
            'user_edited_id'        => Auth::user()->id,

        ];

        $object->update($Data);

        $object->ubigeoStart = District::find($request->input('districtStart_id'))->ubigeo_code;
        $object->ubigeoEnd   = District::find($request->input('districtEnd_id'))->ubigeo_code;
        $object->save();

        $object->type = $request->input('type') ?? 'Electronica';

        $object->number = $request->input('number');
        $object->serie  = $request->input('serie');

        $object->save();

        // Verificar si el tipo del objeto no es "Electronica"
        if ($object->type != 'Electronica') {
            // Generar el correlativo manual usando el método algoritmoanexos
            // $correlativo_manual = $this->algoritmoanexos($correlativo_manual);
            $object->numero = $correlativo_manual;
        }
        $object->save();

        if ($object->subcontract_id != null) {
            $validator = Validator::make($request->all(), ['datasubcontrata' => 'required|array','costsubcontract' => 'required|numeric|min:0',]);
            if ($validator->fails()) {return response()->json(['errors' => $validator->errors()->first()], 422);}
            $validator = Validator::make($request->datasubcontrata ?? [], (new SubcontractDataRequest())->rules(), (new SubcontractDataRequest())->messages());
            if ($validator->fails()) {return response()->json(['errors' => "DATA: " . $validator->errors()->first()], 422);}
            $object = $this->carrierGuideService->updatedatasubcontrata($object->id, $request->costsubcontract, $request->datasubcontrata);
        }
        $this->carrierGuideService->updatestockProduct($object->id);
        
        $object = CarrierGuide::with('tract', 'platform', 'motive',
            'origin', 'destination',
            'sender', 'recipient',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
            'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person',
            'reception', 'branchOffice'
        )->find($object->id);

        Bitacora::create([
            'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
            'record_id'   => $object->id,      // El ID del usuario afectado
            'action'      => 'PUT',            // Acción realizada
            'table_name'  => 'carrier_guides', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Actualizar GRT',      // Descripción de la acción
            'ip_address'  => $request->ip(),        // Dirección IP del usuario
            'user_agent'  => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/carrierGuide/{id}",
     *     summary="Delete a CarrierGuide",
     *     tags={"CarrierGuide"},
     *     description="Delete a CarrierGuide by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CarrierGuide to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CarrierGuide deleted successfully",
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

    public function destroy($id)
    {
        return response()->json(['message' => 'No Está Permitido Eliminar Guias'], 422);
        // $object = CarrierGuide::with('tract', 'platform', 'motive',

        //     'origin', 'destination',
        //     'sender', 'recipient',
        //     'payResponsible', 'driver', 'copilot', 'districtStart.province.department', 'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        // )->find($id);
        // if (!$object) {
        //     return response()->json(['message' => 'Carrier Guide not found'], 422);
        // }
        // $object->delete();
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/carrierGuide/{id}/status",
     *     summary="Update CarrierGuide status",
     *     tags={"CarrierGuide"},
     *     description="Update the status of a CarrierGuide to either 'Pendiente' or Entregado or 'En Transito'",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the CarrierGuide",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="New status for the CarrierGuide (Pendiente or En Transito)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"Pendiente", "En Transito"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CarrierGuide status updated successfully",
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
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pendiente,En Transito,Entregado',
        ]);

        $carrierGuide = CarrierGuide::find($id);

        if (! $carrierGuide) {
            return response()->json(['message' => 'Carrier Guide not found'], 422);
        }

        $carrierGuide->status         = $request->input('status');
        $carrierGuide->user_edited_id = Auth::user()->id;
        $carrierGuide->save();

        $carrierGuide = CarrierGuide::
            with('tract', 'platform', 'motive', 'origin', 'destination', 'sender', 'recipient', 'branchOffice',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
            'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        )

            ->find($id);

        Bitacora::create([
            'user_id'     => Auth::id(),        // ID del usuario que realiza la acción
            'record_id'   => $carrierGuide->id, // El ID del usuario afectado
            'action'      => 'POST',            // Acción realizada
            'table_name'  => 'carrier_guides',  // Tabla afectada
            'data'        => json_encode($carrierGuide),
            'description' => 'Actualizó Estado de GRT', // Descripción de la acción
            'ip_address'  => $request->ip(),             // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),      // Información sobre el navegador/dispositivo
        ]);

        return response()->json($carrierGuide, 200);
    }

    //COMENTADO DESDE DEV
    public function declararGuia(Request $request, $idventa)
    {
        $carrier = CarrierGuide::find($idventa);

        $authuser = Auth::user();
        if ($authuser->typeofUser_id == 2) {

        } else {
            if ($authuser->id != $carrier->user_created_id) {
                Bitacora::create([
                    'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
                    'record_id'   => $carrier->id,     // El ID del usuario afectado
                    'action'      => 'POST',           // Acción realizada
                    'table_name'  => 'carrier_guides', // Tabla afectada
                    'data'        => json_encode($carrier),
                    'description' => 'Declarar GRT Manual - Validación El usuario logueado no es el usuario que creó la GRT', // Descripción de la acción
                    'ip_address'  => $request->ip(),                                                                            // Dirección IP del usuario
                    'user_agent'  => $request->userAgent(),                                                                     // Información sobre el navegador/dispositivo
                ]);
                return response()->json(['message' => 'El usuario logueado no es el usuario que creó la GRT'], 422);
            }
        }

        $funcion = "enviarGuiaRemision";

        if (! $carrier) {
            return response()->json(['message' => 'GUIA NO ENCONTRADA'], 422);
        }
        if ($carrier->status_facturado != 'Pendiente') {
            return response()->json(['message' => 'GUIA NO SE ENCUENTRA EN PENDIENTE DE ENVÍO'], 422);
        }

        //Construir la URL con los parámetros
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
            Bitacora::create([
                'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
                'record_id'   => null,             // El ID del usuario afectado
                'action'      => 'POST',           // Acción realizada
                'table_name'  => 'carrier_guides', // Tabla afectada
                'data'        => null,
                'description' => 'DECLARAR GUIA: Error en cURL al enviar GUIA. ID guia: $idventa,$funcion Error: $error', // Descripción de la acción
                'ip_address'  => $request->ip(),                                                                          // Dirección IP del usuario
                'user_agent'  => $request->userAgent(),                                                                   // Información sobre el navegador/dispositivo
            ]);
        } else {
            // Registrar la respuesta en el log
            Log::error("Respuesta recibida de VENTA para ID venta: $idventa,$funcion Respuesta: $response");
            // Mostrar la respuesta
            // echo 'Respuesta: ' . $response;
            // Bitacora::create([
            //     'user_id' => Auth::id(), // ID del usuario que realiza la acción
            //     'record_id' => null, // El ID del usuario afectado
            //     'action' => 'POST', // Acción realizada
            //     'table_name' => 'carrier_guides', // Tabla afectada
            //     'data' => null,
            //     'description' => 'DECLARAR GUIA: Respuesta recibida de VENTA para ID venta: $idventa,$funcion Respuesta: $response', // Descripción de la acción
            //     'ip_address' => $request->ip(), // Dirección IP del usuario
            //     'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
            // ]);
        }

        // Cerrar cURL
        curl_close($ch);
        // // Log del cierre de la solicitud
        Log::error("Solicitud de GUIA finalizada para ID venta: $idventa. $funcion");

        // ----------------------------------------------
        $carrier->status_facturado = 'Enviado';
        $carrier->user_factured_id = Auth::user()->id;
        $carrier->save();
        $carrier = CarrierGuide::with('tract', 'platform', 'motive', 'origin', 'destination', 'sender', 'recipient', 'branchOffice',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
            'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception'
        )->find($carrier->id);

        Bitacora::create([
            'user_id'     => Auth::id(),       // ID del usuario que realiza la acción
            'record_id'   => $carrier->id,     // El ID del usuario afectado
            'action'      => 'POST',           // Acción realizada
            'table_name'  => 'carrier_guides', // Tabla afectada
            'data'        => json_encode($carrier),
            'description' => 'Declarar GRT Manual', // Descripción de la acción
            'ip_address'  => $request->ip(),        // Dirección IP del usuario
            'user_agent'  => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

        return response()->json($carrier, 200);
    }
//COMENTADO DESDE DEV
    public function declararGuiaBack($fecha = null)
    {
        $fecha = $fecha ?? Carbon::now()->toDateString();
        // Obtener todas las guías que cumplen con la fecha y el estado "Pendiente"
        $carriers = CarrierGuide::whereDate('transferStartDate', $fecha)
            ->where('status_facturado', 'Pendiente')
            ->get();

        //  Definir el nombre de la función para la solicitud
        $funcion = "enviarGuiaRemision";

        // Verificar si hay guías que cumplan con los criterios
        if ($carriers->isEmpty()) {
            return response()->json(['message' => 'NO HAY GUÍAS PENDIENTES PARA ENVIAR'], 422);
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
        }
        Log::error("FINALIZADO ENVIO MASIVO $fecha, GUIAS ENVIADAS: $contador");
        //Retornar respuesta exitosa
        return response()->json(['message' => 'Todas las guías pendientes fueron enviadas correctamente.'], 200);
    }

    public function algoritmoanexos($document)
    {
        $pattern            = '/([A-Za-z0-9]+)-([0-9]{1,8})/';
        $documentsArray     = explode(',', $document);
        $formattedDocuments = []; // Inicializar arreglo para documentos formateados

        if (strpos($document, '-') !== false && $document != '' && $document != null) {
            // Dividir la cadena en un array de documentos
            foreach ($documentsArray as $doc) {
                // Eliminar espacios adicionales al inicio y al final
                $input = trim($doc);

                // Verificar si el documento contiene un guion (-)
                if (strpos($input, '-') !== false) {
                    // Separar en base al guion
                    list($beforeDash, $afterDash) = explode('-', $input, 2);

                    // Tomar los últimos 4 caracteres antes del guion como serie (sin espacios)
                    // Si la serie tiene menos de 4 caracteres, agregar ceros a la izquierda
                    $serie = strtoupper(substr(str_replace(' ', '', $beforeDash), -4));
                    $serie = str_pad($serie, 4, '0', STR_PAD_LEFT); // Completar la serie con ceros si es necesario

                                                                                // Limpiar el correlativo y tomar solo los últimos 8 dígitos
                    $correlativo = preg_replace('/\D/', '', $afterDash);        // Eliminar caracteres no numéricos
                    $correlativo = substr($correlativo, -8);                    // Tomar solo los últimos 8 dígitos
                    $correlativo = str_pad($correlativo, 8, '0', STR_PAD_LEFT); // Completar con ceros si es necesario

                    // Combinar la serie y el correlativo en el formato requerido
                    $formattedDocuments[] = $serie . '-' . $correlativo;
                }

            }

            // Reconstruir la cadena de documentos formateados separados por comas
            $document = implode(',', $formattedDocuments);
        }

        return $document;
    }

    public function changeStatusFacturacion(Request $request, $id)
    {
        $object = CarrierGuide::find($id);

        $validator = validator()->make($request->all(), [
            'statusFacturado' => 'required|in:Anulada,Pendiente,Enviado',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        if (! $object) {
            return response()->json(['message' => 'GUIA NO ENCONTRADA'], 422);
        }

        $object->status_facturado = $request->input('statusFacturado') ?? $object->status_facturado;

        $object->save();

        $object = CarrierGuide::with('tract', 'platform', 'motive',
            'origin', 'destination',
            'sender', 'recipient',
            'payResponsible', 'driver', 'copilot', 'districtStart.province.department',
            'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person',
            'reception', 'branchOffice'
        )->find($object->id);

        return response()->json($object, 200);
    }
    public function getStatusFacturacion(Request $request)
    {
        $nombre = $request->input('nombre');
        if (empty($nombre)) {
            return response()->json(['error' => 'El campo Nombre es Requerido'], 422);
        }

        $funcion = "getstatusservidor";

        // Construir la URL con los parámetros
        $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion'         => $funcion,
            'nombresolicitud' => $nombre,
            'empresa_id'      => 437,
            'fecini'          => '',
            'fecfin'          => '',
        ];
        $url .= '?' . http_build_query($params);

        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($ch);
        $data     = [];

        // Verificar si ocurrió algún error
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            // Registrar el error en el log
            Log::error("Error en cURL al Consultar estado de facturación. Nombre: $nombre,$funcion Error: $error");

            // Definir la respuesta de error
            $data = [
                "mensaje"           => 'Error',
                "nombre-solicitado" => $nombre,
                "response"          => [],
                "status"            => null,
            ];
        } else {
            // Verificar si la respuesta está vacía o no es válida
            if (empty($response)) {
                Log::error("Respuesta vacía al Consultar estado de facturación. Nombre: $nombre, $funcion");

                $data = [
                    "mensaje"           => 'Error',
                    "nombre-solicitado" => $nombre,
                    "response"          => [],
                    "status"            => null,
                ];
            } else {
                Log::info("Respuesta recibida al Consultar estado de facturación. Nombre: $nombre,$funcion Respuesta: $response");
                $responseArray = json_decode($response, true); // Convertir JSON a array

                // Verificar si 'data' existe en la respuesta
                $status = isset($responseArray['data'][0]['descripcion']) ? $responseArray['data'][0]['descripcion'] : null;

                $data = [
                    "mensaje"           => 'Correcto',
                    "nombre-solicitado" => $nombre,
                    "response"          => $responseArray,
                    "status"            => $status,
                ];
            }
        }

        // Cerrar cURL
        curl_close($ch);

        return response()->json($data, 200);
    }

}
