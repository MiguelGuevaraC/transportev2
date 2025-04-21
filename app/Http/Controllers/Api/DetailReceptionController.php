<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchOffice;
use App\Models\CarrierGuide;
use App\Models\DetailReception;
use App\Models\Programming;
use App\Models\Reception;
use App\Models\Route;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DetailReceptionController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailReception",
     *     summary="Get all detailReception",
     *     tags={"Detail Reception"},
     *     description="Show all detailReception",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of detailReception"
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

    public function index()
    {
        $list = DetailReception::with('reception.user', 'reception.origin', 'reception.sender', 'reception.destination'
            , 'reception.recipient', 'reception.pickupResponsible'
            , 'reception.payResponsible', 'reception.seller'
            , 'reception.pointDestination', 'reception.pointSender', 'comissionAgent')->orderBy('id', 'desc')->get();
        return response()->json($list, 200);
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/detailReception",
     *     summary="Create a new detailReception",
     *     tags={"Detail Reception"},
     *     description="Create a new detailReception",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detail Reception data",
     *         @OA\JsonContent(
     *                     @OA\Property(property="description", type="string", example="Descripción"),
     *                     @OA\Property(property="weight", type="number", format="float", example=0.00),
     *                     @OA\Property(property="paymentAmount", type="number", format="float", example=0.00),
     *                     @OA\Property(property="comissionAmount", type="number", format="float", example=0.00),
     *                      @OA\Property(property="comissionAgent_id", type="integer", example=null),
     *                      @OA\Property(property="reception_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail Reception created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Detail Reception created successfully")
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

            'description' => 'required',
            'weight' => 'required',
            'paymentAmount' => 'required',

            // 'comissionAmount' => 'required',
            'comissionAgent_id' => 'nullable|exists:people,id',
            'reception_id' => 'required|exists:receptions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $user = Auth()->user();
        $worker = Worker::find($user->worker_id);

        if ($worker) {
            $branchOffice = BranchOffice::find($worker->branchOffice_id);

            if ($branchOffice) {
                $tipo = 'D' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
            } else {
                return response()->json(['error' => 'Branch Office not found'], 422);
            }
        } else {
            return response()->json(['error' => 'Worker not found'], 422);
        }

        $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);

        $resultado = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM detail_receptions WHERE SUBSTRING(numero, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $data = [
            'numero' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'description' => $request->input('description') ?? '-',
            'weight' => $request->input('weight') ?? 0.00,
            'paymentAmount' => $request->input('paymentAmount') ?? 0.00,
            'debtAmount' => $request->input('debtAmount') ?? 0.00,
            'costFreight' => $request->input('costFreight') ?? 0.00,
            'comissionAmount' => $request->input('comissionAmount') ?? 0.00,
            'costLoad' => $request->input('costLoad') ?? 0.00,
            'costDownload' => $request->input('costDownload') ?? 0.00,
            'comment' => $request->input('comment') ?? '-',
            'status' => 'Generada',
            'comissionAgent_id' => $request->input('comissionAgent_id') ?? null,
            'reception_id' => $request->input('reception_id'),
        ];

        $object = DetailReception::create($data);

        $object = DetailReception::with('reception.user', 'reception.origin', 'reception.sender', 'reception.destination'
            , 'reception.recipient', 'reception.pickupResponsible'
            , 'reception.payResponsible', 'reception.seller'
            , 'reception.pointDestination', 'reception.pointSender', 'comissionAgent')->find($object->id);
        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailReception/{id}",
     *     summary="Get a detailReception by ID",
     *     tags={"Detail Reception"},
     *     description="Retrieve a detailReception by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailReception to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="DetailReception encontrado",

     *),

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
        $detailReception = DetailReception::with('reception.user', 'reception.origin', 'reception.sender', 'reception.destination'
            , 'reception.recipient', 'reception.pickupResponsible'
            , 'reception.payResponsible', 'reception.seller'
            , 'reception.pointDestination', 'reception.pointSender', 'comissionAgent')->find($id);
        if (!$detailReception) {
            return response()->json(['message' => 'DetailReception not found'], 422);
        }
        return response()->json($detailReception, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailReceptionForReception/{id}",
     *     summary="Get a detailReception by ID",
     *     tags={"Detail Reception"},
     *     description="Retrieve a detailReception by its ID Reception",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Reception to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="DetailReception encontrado",

     *),

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

    public function showForReception($id)
    {
        $reception = Reception::find($id);

        $detailReception = Reception::with('user', 'origin',
            'sender', 'destination', 'recipient', 'pickupResponsible',
            'payResponsible', 'seller', 'pointDestination',
            'pointSender', 'details', 'details.comissionAgent')->find($id);

        if (!$reception) {
            return response()->json(['message' => 'Reception not found'], 422);
        }
        return response()->json($detailReception, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportedev/public/api/detailReception/{id}",
     *     summary="Update an existing detailReception",
     *     tags={"Detail Reception"},
     *     description="Update an existing detailReception",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailReception to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detail Reception data",
     *         @OA\JsonContent(
     *                     @OA\Property(property="description", type="string", example="Descripción"),
     *                     @OA\Property(property="weight", type="number", format="float", example=0.00),
     *                     @OA\Property(property="paymentAmount", type="number", format="float", example=0.00),
     *                     @OA\Property(property="comissionAmount", type="number", format="float", example=0.00),
     * @OA\Property(property="comissionAgent_id", type="integer", example=null),
     *     @OA\Property(property="reception_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail Reception updated successfully"
     *
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *       @OA\Response(
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
        $object = DetailReception::find($id);
        if (!$object) {
            return response()->json(['message' => 'DetailReception not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [

            // 'description' => 'required',
            // 'weight' => 'required',
            // 'paymentAmount' => 'required',

            // 'comissionAmount' => 'required',
            'comissionAgent_id' => 'nullable|exists:people,id',
            'reception_id' => 'required|exists:receptions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'description' => $request->input('description'),
            'weight' => $request->input('weight'),
            'paymentAmount' => $request->input('paymentAmount'),
            'debtAmount' => $request->input('debtAmount'),
            'costFreight' => $request->input('costFreight'),
            'comissionAmount' => $request->input('comissionAmount'),
            'costLoad' => $request->input('costLoad'),
            'costDownload' => $request->input('costDownload'),
            'comment' => $request->input('comment'),
            'status' => 'Generada',
            'comissionAgent_id' => $request->input('comissionAgent_id'),
            'reception_id' => $request->input('reception_id'),
        ], $filterNullValues);

        $object->update($Data);

        $object = DetailReception::with('reception.user', 'reception.origin', 'reception.sender', 'reception.destination'
            , 'reception.recipient', 'reception.pickupResponsible'
            , 'reception.payResponsible', 'reception.seller'
            , 'reception.pointDestination', 'reception.pointSender', 'comissionAgent')->find($object->id);
        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/detailReception/{id}",
     *     summary="Delete a detailReception",
     *     tags={"Detail Reception"},
     *     description="Delete a detailReception by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the detailReception to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DetailReception deleted successfully",
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
        $detailReception = DetailReception::find($id);
        if (!$detailReception) {
            return response()->json(['message' => 'DetailReception not found'], 422);
        }
        $detailReception->delete();
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/detailReceptionWithoutProgramming",
     *     summary="Get all detailReception Without Programming",
     *     tags={"Detail Reception"},
     *     description="Show all detailReception Without Programming",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="programming_id",
     *         in="query",
     *         required=true,
     *         description="ID of the programming",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of detailReception Without Programming"
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

    public function indexWithoutProgramming(Request $request)
    {

        $programming_id = $request->input('programming_id');

        if ($programming_id != 'null') {
            $programming = Programming::find($programming_id);
            if (!$programming) {
                return response()->json(['error' => 'Programming Not Found'], 422);
            }
        }

        $branch_office_id = $request->input('branch_office_id');
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

        $list = $this->filterReceptions($request, $branch_office_id);

        return response()->json($list->original, 200);
    }

    public function filterReceptions(Request $request, $branch_office_id)
    {
        $startPlace = $request->input('startPlace_id');
        $endPlace = $request->input('endPlace_id');
        $programming_id = $request->input('programming_id') ?? 'null';
        //PARAMETROS
        $placa = $request->input('placa');
        $numero = $request->input('numero');
        $origenOrDestino = $request->input('origenOrDestino');
        $nombreClientePaga = $request->input('nombrePersona');
        $branch_office_id = $request->input('branch_office_id');
        // Buscar la ruta principal
        $route = Route::where('placeStart_id', $startPlace)
            ->where('placeEnd_id', $endPlace)
            ->first();

        // Si no existe la ruta principal, retorna una colección vacía
        if (!$route) {
            return response()->json([
                'total' => 0,
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $request->get('per_page', 15),
                'pagination' => 0,
                'message' => 'No routes found',
            ], 200);
        }

        // Obtener las subrutas y agregar la ruta principal al inicio del array
        $subRoutes = $route->routes->toArray() ?? [];
        array_unshift($subRoutes, $route);

        // Convertimos el array a una colección
        $subRoutes = collect($subRoutes);

        $query = CarrierGuide::with([
            'reception.user', 'reception.origin', 'reception.sender', 'reception.destination',
            'reception.recipient', 'reception.pickupResponsible', 'reception.payResponsible',
            'reception.seller', 'reception.pointDestination', 'reception.pointSender',
            'tract', 'platform', 'origin', 'destination', 'programming', 'branchOffice',
            'sender', 'recipient', 'programming', 'programmings', 'payResponsible', 'driver', 'copilot',
            'districtStart.province.department', 'districtEnd.province.department',
            'copilot.person', 'subcontract', 'driver.person',
        ])

            ->orderByRaw('CASE WHEN programming_id = ? THEN 0 ELSE 1 END', [$programming_id])
            ->orderBy('id', 'desc');

        // Filtro por las rutas (startPlace y endPlace)
        if ($subRoutes->isNotEmpty()) {
            $query->where(function ($query) use ($subRoutes) {
                foreach ($subRoutes as $subRoute) {
                    $query->orWhere(function ($q) use ($subRoute) {
                        $q->whereHas('reception.origin', function ($q) use ($subRoute) {
                            $q->where('name', $subRoute['placeStart']);
                        })
                            ->whereHas('reception.destination', function ($q) use ($subRoute) {
                                $q->where('name', $subRoute['placeEnd']);
                            });
                    });
                }
            });
        }

        if (!empty($origenOrDestino)) {
            $routeParts = explode('-', $origenOrDestino);

            // Si sólo se ingresó una parte de la ruta (ej. "Chiclayo")
            if (count($routeParts) === 1) {
                $query->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->orWhereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                });
            } elseif (count($routeParts) === 2) {
                // Si se ingresó el formato "Origen-Destino" (ej. "Amazonas-Chiclayo")
                $query->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->whereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[1]) . '%']);
                });
            }
        }
        if (!empty($numero)) {
            $query->where('numero', 'LIKE', '%' . $numero . '%');
        }
        if (!empty($branch_office_id)) {

            $query->where('branchOffice_id', $branch_office_id);

        }
        if (!empty($placa)) {
            $query->whereHas('tract', function ($query) use ($placa) {
                $query->where('currentPlate', 'LIKE', '%' . $placa . '%');
            });
        }
        
        if (!empty($nombreClientePaga)) {
            $nombreClientePagaUpper = strtoupper($nombreClientePaga);

            $query->where(function ($query) use ($nombreClientePagaUpper) {
                // Búsqueda en payResponsible
                $query->orWhereHas('payResponsible', function ($subQuery) use ($nombreClientePagaUpper) {
                    $subQuery->where(function ($q) use ($nombreClientePagaUpper) {
                        $this->applyNameFilter($q, $nombreClientePagaUpper);
                    });
                });

                // Búsqueda en recipient
                $query->orWhereHas('recipient', function ($subQuery) use ($nombreClientePagaUpper) {
                    $subQuery->where(function ($q) use ($nombreClientePagaUpper) {
                        $this->applyNameFilter($q, $nombreClientePagaUpper);
                    });
                });

                // Búsqueda en sender
                $query->orWhereHas('sender', function ($subQuery) use ($nombreClientePagaUpper) {
                    $subQuery->where(function ($q) use ($nombreClientePagaUpper) {
                        $this->applyNameFilter($q, $nombreClientePagaUpper);
                    });
                });
            });
        }

        // Filtro por programming_id
        // if ($programming_id != 'null') {
        //     $query->whereNull('programming_id')
        //         ->orWhere('programming_id', $programming_id);
        // } else {
        //     $query->whereNull('programming_id');
        // }
        if ($programming_id !== 'null') {
            $programming = Programming::with('carrierGuidess')->find($programming_id);

            // Si la programación existe, obtener las guías asociadas
            if ($programming) {
                // Agregar guías de la programación a la consulta
              //  (dd($programming->carrierGuidess->pluck('id')));
              //if()
                $query->orWhereIn('id', $programming->carrierGuidess->pluck('id'));
            }
        }
        // Obtener los resultados con paginación
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $rawSql = vsprintf(str_replace(['?'], ['\'%s\''], $query->toSql()), $query->getBindings());
        //error_log($rawSql);
        
        $receptions = $query->paginate($perPage, ['*'], 'page', $page);

        // Estructura de la respuesta
        return response()->json([
            'total' => $receptions->total(),
            'data' => $receptions->items(),
            'current_page' => $receptions->currentPage(),
            'last_page' => $receptions->lastPage(),
            'per_page' => $receptions->perPage(),
            'pagination' => $perPage,
            'first_page_url' => $receptions->url(1),
            'from' => $receptions->firstItem(),
            'next_page_url' => $receptions->nextPageUrl(),
            'path' => $receptions->path(),
            'prev_page_url' => $receptions->previousPageUrl(),
            'to' => $receptions->lastItem(),
        ], 200);
    }
    // public function filterReceptions(Request $request, $branch_office_id)
    // {
    //     $startPlace = $request->input('startPlace_id');
    //     $endPlace = $request->input('endPlace_id');
    //     $programming_id = $request->input('programming_id') ?? 'null';

    //     // Buscar la ruta principal
    //     $route = Route::where('placeStart_id', $startPlace)
    //         ->where('placeEnd_id', $endPlace)
    //         ->first(); // Se agrega first() para que retorne un único registro o null

    //     // Si no existe la ruta principal, retorna una colección vacía
    //     if (!$route) {
    //         return collect();
    //     }

    //     // Obtener las subrutas y agregar la ruta principal al inicio del array
    //     $subRoutes = $route->routes->toArray() ?? [];
    //     array_unshift($subRoutes, $route);

    //     // Convertimos el array a una colección
    //     $subRoutes = collect($subRoutes);

    //     $query = CarrierGuide::with([
    //         'reception.user', 'reception.origin', 'reception.sender', 'reception.destination',
    //         'reception.recipient', 'reception.pickupResponsible', 'reception.payResponsible',
    //         'reception.seller', 'reception.pointDestination', 'reception.pointSender',
    //         'tract', 'platform', 'origin', 'destination', 'programming','branchOffice',
    //         'sender', 'recipient', 'programming', 'payResponsible', 'driver', 'copilot',
    //         'districtStart.province.department', 'districtEnd.province.department',
    //         'copilot.person', 'subcontract', 'driver.person',
    //     ])
    //         ->whereHas('reception.branchOffice', function ($query) use ($branch_office_id) {
    //             // $query->where('id', $branch_office_id);
    //         })
    //         ->orderBy('id', 'desc');

    //     // Filtro por las rutas (startPlace y endPlace) usando relaciones reception.origin y reception.destination
    //     if ($subRoutes->isNotEmpty()) {
    //         $query->where(function ($query) use ($subRoutes) {
    //             foreach ($subRoutes as $subRoute) {
    //                 $query->orWhere(function ($q) use ($subRoute) {
    //                     $q->whereHas('reception.origin', function ($q) use ($subRoute) {
    //                         $q->where('name', $subRoute['placeStart']);
    //                     })
    //                         ->whereHas('reception.destination', function ($q) use ($subRoute) {
    //                             $q->where('name', $subRoute['placeEnd']);
    //                         });
    //                 });
    //             }
    //         });
    //     }

    //     // Filtro por programming_id
    //     if (($programming_id != 'null')) {
    //         // dd($programming_id);
    //         $query->whereNull('programming_id')
    //             ->orWhere('programming_id', $programming_id);
    //     } else {

    //         $query->whereNull('programming_id');
    //     }

    //     $receptions = $query->get();

    //     return $receptions;
    // }
    public function applyNameFilter($query, $searchTermUpper)
    {
        $query->where(DB::raw('UPPER(names)'), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(documentNumber)'), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(fatherSurname)'), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(businessName)'), 'LIKE', '%' . $searchTermUpper . '%')
            ->orWhere(DB::raw('UPPER(motherSurname)'), 'LIKE', '%' . $searchTermUpper . '%');
    }


}
