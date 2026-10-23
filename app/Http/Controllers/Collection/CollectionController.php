<?php
namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\BranchOffice;
use App\Models\CarrierGuide;
use App\Models\Person;
use App\Models\Place;
use App\Models\Reception;
use App\Models\Subcontract;
use App\Models\Vehicle;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
/**
 * @OA\Get(
 *     path="/transporte/public/api/dataCollection",
 *     summary="Get all data",
 *     tags={"CollectionData"},
 *     description="Retrieve all data collections",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of all data collections"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
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

        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }

        }
        // else {
        //     $branch_office_id = auth()->user()->worker->branchOffice_id;
        //     $branchOffice = BranchOffice::find($branch_office_id);
        // }

        // Receptions pagination
        $receptions_page     = $request->input('receptions_page', 1);
        $receptions_per_page = $request->input('receptions_per_page', 10); // Default 10
        $codeReception       = $request->input('codeReception');
        $dateStart           = $request->input('dateStart');
        $dateEnd             = $request->input('dateEnd');
        $nombreClientePaga   = $request->input('nombreClientePaga');
        $nombreRemitente     = $request->input('nombreRemitente');
        $nombreDestinatario  = $request->input('nombreDestinatario');

        $origenOrDestino = $request->input('origenOrDestino');
        $isCargos        = $request->input('isCargos');
        $statusReception = $request->input('statusReception');
        $numberGuia      = $request->input('numberGuia');
        $documentGuia      = $request->input('documentGuia');
        $numberVenta     = $request->input('numberVenta');
        $glosa           = $request->input('glosa', $request->input('observacion', $request->input('observation')));

        // $branch_office_id = $request->input('branchOffice_id');
        // Validate receptions_per_page
        if (! is_numeric($receptions_per_page) || $receptions_per_page <= 0) {
            $receptions_per_page = 10; // Default value if NaN or non-positive
        }

        // $receptions = Reception::with('user', 'origin', 'sender',
        //     'destination', 'recipient', 'pickupResponsible', 'payResponsible',
        //     'seller', 'pointDestination', 'pointSender', 'details',
        //     'firstCarrierGuide', 'moviment','cargos')

        //     ->where('branchOffice_id', $branch_office_id)
        //     ->orderBy('id', 'desc')->paginate($receptions_per_page, ['*'], 'receptions_page', $receptions_page);

        $receptionsQuery = Reception::select(
            'receptions.*',
            DB::raw('(
                SELECT GROUP_CONCAT(description SEPARATOR ", ")
                FROM detail_receptions
                WHERE detail_receptions.reception_id = receptions.id
            ) as carga'),
            'carrier_guides.numero as guide_number',
            'carrier_guides.id as guide_id' // Aquí el error podría estar en la coma antes del cierre
        )
            ->leftJoin('carrier_guides', function ($join) {
                $join->on('receptions.id', '=', 'carrier_guides.reception_id')
                    ->where('carrier_guides.status_facturado', '!=', 'Anulada'); // Filtra solo guías activas
            })
            ->with([
                'user', 'origin', 'sender', 'destination',
                'recipient', 'pickupResponsible', 'payResponsible',
                'seller', 'pointDestination', 'pointSender', 'branchOffice',
                'details',
                // 'firstCarrierGuide',
                'moviment', 'cargos',
            ]);

        if (! empty($branch_office_id)) {
            $receptionsQuery->where('receptions.branchOffice_id', $branch_office_id);
        }
        // Filtro por código de recepción
        if (! empty($codeReception)) {
            $receptionsQuery->where('receptions.codeReception', 'LIKE', '%' . $codeReception . '%');
        }

        // Filtro por fecha inicio (>=) y fecha fin (<=)
        if (! empty($dateStart)) {
            $receptionsQuery->where('receptions.receptionDate', '>=', $dateStart);
        }

        if (! empty($dateEnd)) {
            // receptionDate incluye hora; incluir todo el día final sin
            // aplicar funciones sobre la columna y conservar los índices.
            $endExclusive = Carbon::parse($dateEnd)->addDay()->startOfDay();
            $receptionsQuery->where('receptions.receptionDate', '<', $endExclusive);
        }

        // Filtro por número de guía
        // if (! empty($numberGuia)) {
        //     // Filtro por número de guía dentro de la relación firstCarrierGuide
        //     $receptionsQuery->whereHas('firstCarrierGuide', function ($query) use ($numberGuia) {
        //         $query->where('numero', 'LIKE', '%' . $numberGuia . '%');
        //     });

        // }
        if (! empty($numberGuia)) {
            $receptionsQuery->where('carrier_guides.numero', 'LIKE', '%' . $numberGuia . '%');
        }

        if (! empty($documentGuia)) {
            $receptionsQuery->where('carrier_guides.document', 'LIKE', '%' . $documentGuia . '%');
        }

        // Busca el texto en cualquier posición de la glosa/observación,
        // observación de la guía o descripción de la carga.
        if (! empty($glosa)) {
            $receptionsQuery->where(function ($query) use ($glosa) {
                $term = '%' . mb_strtolower($glosa) . '%';

                $query->whereRaw('LOWER(receptions.comment) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(carrier_guides.observation) LIKE ?', [$term])
                    ->orWhereHas('details', function ($detailsQuery) use ($term) {
                        $detailsQuery->whereRaw('LOWER(description) LIKE ?', [$term]);
                    });
            });
        }

        // Filtro por número de venta dentro de la relación moviment
        if (! empty($numberVenta)) {
            $receptionsQuery->where(function ($query) use ($numberVenta) {
                // Filtrar primero por el campo nro_sale
                $query->where('receptions.nro_sale', $numberVenta)
                    ->orWhere(function ($subQuery) use ($numberVenta) {
                        // En caso de que nro_sale sea NULL, filtrar por moviment
                        $subQuery->whereNull('receptions.nro_sale')
                            ->whereHas('moviment', function ($movimentQuery) use ($numberVenta) {
                                $movimentQuery->where('sequentialNumber', 'LIKE', '%' . $numberVenta . '%');
                            });
                    });
            });
        }

        // Filtro por nombre del cliente que paga
        if (! empty($nombreRemitente)) {
            $nombreRemitenteUpper = strtoupper($nombreRemitente);

            $receptionsQuery->where(function ($query) use ($nombreRemitenteUpper) {
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

            $receptionsQuery->where(function ($query) use ($nombreDestinatarioUpper) {
                // Búsqueda en recipient
                $query->orWhereHas('recipient', function ($subQuery) use ($nombreDestinatarioUpper) {
                    $subQuery->where(function ($q) use ($nombreDestinatarioUpper) {
                        $this->applyNameFilter($q, $nombreDestinatarioUpper);
                    });
                });
            });
        }

        if (! empty($nombreClientePaga)) {
            $nombreClientePagaUpper = strtoupper($nombreClientePaga);

            $receptionsQuery->where(function ($query) use ($nombreClientePagaUpper) {
                // Búsqueda en payResponsible
                $query->orWhereHas('payResponsible', function ($subQuery) use ($nombreClientePagaUpper) {
                    $subQuery->where(function ($q) use ($nombreClientePagaUpper) {
                        $this->applyNameFilter($q, $nombreClientePagaUpper);
                    });
                });
            });
        }

        // Filtro por origen o destino
        if (! empty($origenOrDestino)) {
            $routeParts = explode('-', $origenOrDestino);

            // Si sólo se ingresó una parte de la ruta (ej. "Chiclayo")
            if (count($routeParts) === 1) {
                $term = '%' . strtolower(trim($routeParts[0])) . '%';
                $receptionsQuery->where(function ($query) use ($term) {
                    $query->whereHas('origin', function ($placeQuery) use ($term) {
                        $placeQuery->whereRaw('LOWER(name) LIKE ?', [$term]);
                    })->orWhereHas('destination', function ($placeQuery) use ($term) {
                        $placeQuery->whereRaw('LOWER(name) LIKE ?', [$term]);
                    });
                });
            } elseif (count($routeParts) === 2) {
                // Si se ingresó el formato "Origen-Destino" (ej. "Amazonas-Chiclayo")
                $receptionsQuery->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($routeParts[0])) . '%']);
                })->whereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower(trim($routeParts[1])) . '%']);
                });
            }
        }

        // Filtro por isCargos (recepciones que tienen cargos)
        if (! empty($isCargos)) {
            if ($isCargos === "true") {
                $receptionsQuery->whereHas('cargos');
            } else {
                $receptionsQuery->whereDoesntHave('cargos');
            }
        }

        // Filtrar por statusReception
        if (! empty($statusReception)) {
            $receptionsQuery->where(function ($query) use ($statusReception) {
                if ($statusReception === 'Sin Guia') {
                    $query->whereDoesntHave('firstCarrierGuide');
                } elseif ($statusReception === 'Sin Programar') {
                    $query->whereHas('firstCarrierGuide', function ($subQuery) {
                        $subQuery->doesntHave('Programming');
                    });
                } else {
                    // Para "Programado" se considera que debe existir una guía y tener programación
                    $query->whereHas('firstCarrierGuide', function ($subQuery) {
                        $subQuery->whereHas('Programming');
                    });
                }
            });
        }

        // Obtener y procesar las recepciones con paginación
        $receptions = $receptionsQuery->orderBy('id', 'desc')->paginate($receptions_per_page, ['*'], 'receptions_page', $receptions_page);

        // Cargar las guías relacionadas en una sola consulta para evitar N+1.
        $guideIds = collect($receptions->items())
            ->pluck('guide_id')
            ->filter()
            ->unique()
            ->values();

        $guidesById = CarrierGuide::with([
            'districtStart.province.department',
            'districtEnd.province.department',
            'motive', 'sender', 'recipient', 'payResponsible',
            'programming', 'programming.tract', 'programming.platform',
            'programming.origin', 'programming.destination',
        ])->whereIn('id', $guideIds)->get()->keyBy('id');

        // Transformación de la colección para definir el 'status'
        $receptions->getCollection()->transform(function ($reception) use ($guidesById) {
            $reception->first_carrier_guide = $guidesById[$reception->guide_id] ?? null;

            // Evitar error si no existe la guía
            if (! $reception->first_carrier_guide) {
                $reception->status = 'Sin Guia';
            } elseif (! $reception->first_carrier_guide->relationLoaded('programming') || is_null($reception->first_carrier_guide->programming)) {
                // Verificar correctamente si la programación existe
                $reception->status = 'Sin Programar';
            } else {
                $reception->status = 'Programado';
            }

            return $reception;
        });
        // El selector de origen/destino necesita buscar en todo el catálogo.
        // No paginar lugares: con 100/500 registros se ocultaban destinos
        // existentes como Pacasmayo, Trujillo o Piura.
        $places = Place::select(['id', 'ubigeo', 'name', 'address', 'state', 'district_id'])
            ->orderBy('name', 'asc')
            ->get();
        $placesLimit = max($places->count(), 1);
        $places = new LengthAwarePaginator(
            $places,
            $places->count(),
            $placesLimit,
            1,
            ['path' => $request->url()]
        );

        // Workers pagination
        $workers_page     = $request->input('workers_page', 1);
        $workers_per_page = $request->input('workers_per_page', 500); // Default 10

        // Validate workers_per_page
        if (! is_numeric($workers_per_page) || $workers_per_page <= 0) {
            $workers_per_page = 10; // Default value if NaN or non-positive
        }
        $user          = Auth()->user();
        $typeofUser_id = $user->typeofUser_id ?? '';

        $workers = Worker::with('person', 'area', 'district.province.department')
        // ->when($typeofUser_id != 1 || $typeofUser_id != 2, function ($query) use ($branch_office_id) {
        //     return $query->where('branchOffice_id', $branch_office_id);
        // })
        // ->where('branchOffice_id', $branch_office_id)
            ->orderBy('id', 'desc')
            ->paginate($workers_per_page, ['*'], 'workers_page', $workers_page);

        // Vehicles pagination
        $vehicles_page     = $request->input('vehicles_page', 1);
        $vehicles_per_page = $request->input('vehicles_per_page', 10); // Default 10

        // Validate vehicles_per_page
        if (! is_numeric($vehicles_per_page) || $vehicles_per_page <= 0) {
            $vehicles_per_page = 10; // Default value if NaN or non-positive
        }

        $sinProgramaciones = $request->input('sinProgramaciones', 'false'); // Permitir null por defecto.

        $vehicles = Vehicle::with([
            "modelFunctional",
            "photos",
            "documents",
            "typeCarroceria.typeCompany",
            "responsable",
            "companyGps",
            "branchOffice",
        ])
            ->when($sinProgramaciones !== null, function ($query) use ($sinProgramaciones) {
                if ($sinProgramaciones === "1" || $sinProgramaciones === "true") {
                    // Filtrar vehículos sin programaciones.
                    $query->where(function ($query) {
                        $query->whereDoesntHave('tractProgrammings')
                            ->whereDoesntHave('platformProgrammings');
                    })->orWhereIn('id',[7,110,6]);
                }
                // if ($sinProgramaciones === "0" || $sinProgramaciones === "false") {
                //     // Filtrar vehículos con programaciones.
                //     $query->whereHas('tractProgrammings')
                //           ->orWhereHas('platformProgrammings')->orWhere('id', 75);
                // }
            })
            ->orderBy('currentPlate', 'asc')
            ->paginate($vehicles_per_page, ['*'], 'vehicles_page', $vehicles_page);

        // Addresses pagination
        $address_page     = $request->input('address_page', 1);
        $address_per_page = $request->input('address_per_page', 10); // Default 10

        // Validate address_per_page
        if (! is_numeric($address_per_page) || $address_per_page <= 0) {
            $address_per_page = 10; // Default value if NaN or non-positive
        }

        $address = Address::with('client')->orderBy('id', 'desc')->paginate($address_per_page, ['*'], 'address_page', $address_page);

        // Clients pagination
        $clients_page     = $request->input('clients_page', 1);
        $clients_per_page = $request->input('clients_per_page', 10); // Default 10

        // Validate clients_per_page
        if (! is_numeric($clients_per_page) || $clients_per_page <= 0) {
            $clients_per_page = 10; // Default value if NaN or non-positive
        }

        $clients = Person::where('branchOffice_id', $branch_office_id)
            ->where("type", 'Cliente')

            ->orderBy('id', 'desc')->paginate($clients_per_page, ['*'], 'clients_page', $clients_page);

        // Subcontracts pagination
        $subcontracts_page     = $request->input('subcontracts_page', 1);
        $subcontracts_per_page = $request->input('subcontracts_per_page', 10); // Default 10

        // Validate subcontracts_per_page
        if (! is_numeric($subcontracts_per_page) || $subcontracts_per_page <= 0) {
            $subcontracts_per_page = 10; // Default value if NaN or non-positive
        }

        $subcontracts = Subcontract::orderBy('id', 'desc')->paginate($subcontracts_per_page, ['*'], 'subcontracts_page', $subcontracts_page);

        // CarrierGuides pagination
        $carrierGuides_page     = $request->input('carrierGuides_page', 1);
        $carrierGuides_per_page = $request->input('carrierGuides_per_page', 10); // Default 10

        // Validate carrierGuides_per_page
        if (! is_numeric($carrierGuides_per_page) || $carrierGuides_per_page <= 0) {
            $carrierGuides_per_page = 10; // Default value if NaN or non-positive
        }

        $carrierGuides = CarrierGuide::with('tract', 'platform', 'motive', 'branchOffice', 'origin', 'destination', 'sender', 'recipient', 'payResponsible', 'driver', 'copilot', 'districtStart.province.department', 'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception')
        // ->where('branchOffice_id', $branch_office_id)
            ->orderBy('id', 'desc')->paginate($carrierGuides_per_page, ['*'], 'carrierGuides_page', $carrierGuides_page);

        // Compose response
        $response = [
            'receptions'    => [
                'total'          => $receptions->total(),
                // 'data'           => ReceptionResource::collection($receptions->items()),
                'data'           => $receptions->items(),
                'current_page'   => $receptions->currentPage(),
                'last_page'      => $receptions->lastPage(),
                'per_page'       => $receptions->perPage(),
                'pagination'     => $receptions_per_page, // New field for pagination size
                'first_page_url' => $receptions->url(1),
                'from'           => $receptions->firstItem(),
                'next_page_url'  => $receptions->nextPageUrl(),
                'path'           => $receptions->path(),
                'prev_page_url'  => $receptions->previousPageUrl(),
                'to'             => $receptions->lastItem(),
            ],
            'carrierGuides' => [
                'total'          => $carrierGuides->total(),
                'data'           => $carrierGuides->items(),
                'current_page'   => $carrierGuides->currentPage(),
                'last_page'      => $carrierGuides->lastPage(),
                'per_page'       => $carrierGuides->perPage(),
                'pagination'     => $carrierGuides_per_page, // New field for pagination size
                'first_page_url' => $carrierGuides->url(1),
                'from'           => $carrierGuides->firstItem(),
                'next_page_url'  => $carrierGuides->nextPageUrl(),
                'path'           => $carrierGuides->path(),
                'prev_page_url'  => $carrierGuides->previousPageUrl(),
                'to'             => $carrierGuides->lastItem(),
            ],
            'places'        => [
                'total'          => $places->total(),
                'data'           => $places->items(),
                'current_page'   => $places->currentPage(),
                'last_page'      => $places->lastPage(),
                'per_page'       => $places->perPage(),
                'pagination'     => $placesLimit,
                'first_page_url' => $places->url(1),
                'from'           => $places->firstItem(),
                'next_page_url'  => $places->nextPageUrl(),
                'path'           => $places->path(),
                'prev_page_url'  => $places->previousPageUrl(),
                'to'             => $places->lastItem(),
            ],
            'workers'       => [
                'total'          => $workers->total(),
                'data'           => $workers->items(),
                'current_page'   => $workers->currentPage(),
                'last_page'      => $workers->lastPage(),
                'per_page'       => $workers->perPage(),
                'pagination'     => $workers_per_page, // New field for pagination size
                'first_page_url' => $workers->url(1),
                'from'           => $workers->firstItem(),
                'next_page_url'  => $workers->nextPageUrl(),
                'path'           => $workers->path(),
                'prev_page_url'  => $workers->previousPageUrl(),
                'to'             => $workers->lastItem(),
            ],
            'vehicles'      => [
                'total'          => $vehicles->total(),
                'data'           => $vehicles->items(),
                'current_page'   => $vehicles->currentPage(),
                'last_page'      => $vehicles->lastPage(),
                'per_page'       => $vehicles->perPage(),
                'pagination'     => $vehicles_per_page, // New field for pagination size
                'first_page_url' => $vehicles->url(1),
                'from'           => $vehicles->firstItem(),
                'next_page_url'  => $vehicles->nextPageUrl(),
                'path'           => $vehicles->path(),
                'prev_page_url'  => $vehicles->previousPageUrl(),
                'to'             => $vehicles->lastItem(),
            ],
            'address'       => [
                'total'          => $address->total(),
                'data'           => $address->items(),
                'current_page'   => $address->currentPage(),
                'last_page'      => $address->lastPage(),
                'per_page'       => $address->perPage(),
                'pagination'     => $address_per_page, // New field for pagination size
                'first_page_url' => $address->url(1),
                'from'           => $address->firstItem(),
                'next_page_url'  => $address->nextPageUrl(),
                'path'           => $address->path(),
                'prev_page_url'  => $address->previousPageUrl(),
                'to'             => $address->lastItem(),
            ],
            'clients'       => [
                'total'          => $clients->total(),
                'data'           => $clients->items(),
                'current_page'   => $clients->currentPage(),
                'last_page'      => $clients->lastPage(),
                'per_page'       => $clients->perPage(),
                'pagination'     => $clients_per_page, // New field for pagination size
                'first_page_url' => $clients->url(1),
                'from'           => $clients->firstItem(),
                'next_page_url'  => $clients->nextPageUrl(),
                'path'           => $clients->path(),
                'prev_page_url'  => $clients->previousPageUrl(),
                'to'             => $clients->lastItem(),
            ],
            'subcontracts'  => [
                'total'          => $subcontracts->total(),
                'data'           => $subcontracts->items(),
                'current_page'   => $subcontracts->currentPage(),
                'last_page'      => $subcontracts->lastPage(),
                'per_page'       => $subcontracts->perPage(),
                'pagination'     => $subcontracts_per_page, // New field for pagination size
                'first_page_url' => $subcontracts->url(1),
                'from'           => $subcontracts->firstItem(),
                'next_page_url'  => $subcontracts->nextPageUrl(),
                'path'           => $subcontracts->path(),
                'prev_page_url'  => $subcontracts->previousPageUrl(),
                'to'             => $subcontracts->lastItem(),
            ],
        ];

        return response()->json($response);
    }

}
