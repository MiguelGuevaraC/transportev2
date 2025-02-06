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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
/**
 * @OA\Get(
 *     path="/transportev2/public/api/dataCollection",
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

        // Receptions pagination
        $receptions_page = $request->input('receptions_page', 1);
        $receptions_per_page = $request->input('receptions_per_page', 10); // Default 10
        $codeReception = $request->input('codeReception');
        $dateStart = $request->input('dateStart');
        $dateEnd = $request->input('dateEnd');
        $nombreClientePaga = $request->input('nombreClientePaga');
        $nombreRemitente = $request->input('nombreRemitente');
        $nombreDestinatario = $request->input('nombreDestinatario');

        $origenOrDestino = $request->input('origenOrDestino');
        $isCargos = $request->input('isCargos');
        $statusReception = $request->input('statusReception');
        $numberGuia = $request->input('numberGuia');
        $numberVenta = $request->input('numberVenta');

        // $branch_office_id = $request->input('branchOffice_id');
        // Validate receptions_per_page
        if (!is_numeric($receptions_per_page) || $receptions_per_page <= 0) {
            $receptions_per_page = 10; // Default value if NaN or non-positive
        }

        // $receptions = Reception::with('user', 'origin', 'sender',
        //     'destination', 'recipient', 'pickupResponsible', 'payResponsible',
        //     'seller', 'pointDestination', 'pointSender', 'details',
        //     'firstCarrierGuide', 'moviment','cargos')

        //     ->where('branchOffice_id', $branch_office_id)
        //     ->orderBy('id', 'desc')->paginate($receptions_per_page, ['*'], 'receptions_page', $receptions_page);

        $receptionsQuery = Reception::with([
            'user', 'origin', 'sender', 'destination',
            'recipient', 'pickupResponsible', 'payResponsible',
            'seller', 'pointDestination', 'pointSender', 'branchOffice',
            'details', 'firstCarrierGuide', 'moviment', 'cargos',
        ])
            ->where('branchOffice_id', $branch_office_id);

        // Filtro por código de recepción
        if (!empty($codeReception)) {
            $receptionsQuery->where('codeReception', 'LIKE', '%' . $codeReception . '%');
        }

        // Filtro por fecha inicio (>=) y fecha fin (<=)
        if (!empty($dateStart)) {
            $receptionsQuery->whereDate('created_at', '>=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $receptionsQuery->whereDate('created_at', '<=', $dateEnd);
        }

        $receptionsQuery = Reception::select('receptions.*', 
        DB::raw('(
            SELECT GROUP_CONCAT(description SEPARATOR ", ") 
            FROM detail_receptions 
            WHERE detail_receptions.reception_id = receptions.id
        ) as carga'),
         
        )
        
        ->with([
            'user', 'origin', 'sender', 'destination',
            'recipient', 'pickupResponsible', 'payResponsible',
            'seller', 'pointDestination', 'pointSender', 'branchOffice',
            'details', 'firstCarrierGuide', 'moviment', 'cargos',
        ]);

        if (!empty($branch_office_id)) {
            $receptionsQuery->where('branchOffice_id', $branch_office_id);
        }
        // Filtro por código de recepción
        if (!empty($codeReception)) {
            $receptionsQuery->where('codeReception', 'LIKE', '%' . $codeReception . '%');
        }

        // Filtro por fecha inicio (>=) y fecha fin (<=)
        if (!empty($dateStart)) {
            $receptionsQuery->whereDate('created_at', '>=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $receptionsQuery->whereDate('created_at', '<=', $dateEnd);
        }

        // Filtro por número de guía
        if (!empty($numberGuia)) {
            // Filtro por número de guía dentro de la relación firstCarrierGuide
            $receptionsQuery->whereHas('firstCarrierGuide', function ($query) use ($numberGuia) {
                $query->where('numero', 'LIKE', '%' . $numberGuia . '%');
            });
        }

        // Filtro por número de venta dentro de la relación moviment
        if (!empty($numberVenta)) {
            $receptionsQuery->where(function ($query) use ($numberVenta) {
                // Filtrar primero por el campo nro_sale
                $query->where('nro_sale', $numberVenta)
                      ->orWhere(function ($subQuery) use ($numberVenta) {
                          // En caso de que nro_sale sea NULL, filtrar por moviment
                          $subQuery->whereNull('nro_sale')
                                   ->whereHas('moviment', function ($movimentQuery) use ($numberVenta) {
                                       $movimentQuery->where('sequentialNumber', 'LIKE', '%' . $numberVenta . '%');
                                   });
                      });
            });
        }
        

        // Filtro por nombre del cliente que paga
        if (!empty($nombreRemitente)) {
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

        if (!empty($nombreDestinatario)) {
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

        if (!empty($nombreClientePaga)) {
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
        if (!empty($origenOrDestino)) {
            $routeParts = explode('-', $origenOrDestino);

            // Si sólo se ingresó una parte de la ruta (ej. "Chiclayo")
            if (count($routeParts) === 1) {
                $receptionsQuery->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->orWhereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                });
            } elseif (count($routeParts) === 2) {
                // Si se ingresó el formato "Origen-Destino" (ej. "Amazonas-Chiclayo")
                $receptionsQuery->whereHas('origin', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[0]) . '%']);
                })->whereHas('destination', function ($query) use ($routeParts) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($routeParts[1]) . '%']);
                });
            }
        }

        // Filtro por isCargos (recepciones que tienen cargos)
        if (!empty($isCargos)) {
            if ($isCargos === "true") {
                $receptionsQuery->whereHas('cargos');
            } else {
                $receptionsQuery->whereDoesntHave('cargos');
            }
        }

        // Filtrar por statusReception
        if (!empty($statusReception)) {
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

        // Transformación de la colección para definir el 'status'
        $receptions->getCollection()->transform(function ($reception) {
            if (!$reception->firstCarrierGuide) {
                $reception->status = 'Sin Guia';
            } elseif ($reception->firstCarrierGuide && !$reception->firstCarrierGuide->Programming) {
                $reception->status = 'Sin Programar';
            } else {
                $reception->status = 'Programado';
            }
            return $reception;
        });

        // Total de recepciones filtradas
        // $totalReceptions = $receptions->total();

        // Repeat the same for other paginated entities...

        // Places pagination
        $places_page = $request->input('places_page', 1);
        $places_per_page = $request->input('places_per_page', 500); // Default 10

        // Validate places_per_page
        if (!is_numeric($places_per_page) || $places_per_page <= 0) {
            $places_per_page = 10; // Default value if NaN or non-positive
        }

        // $places = Place::orderBy('id', 'desc')
        // ->paginate($places_per_page, ['*'], 'places_page', $places_page);

        $places = Place::orderBy('name', 'asc')
            ->paginate(500);

        // Workers pagination
        $workers_page = $request->input('workers_page', 1);
        $workers_per_page = $request->input('workers_per_page', 500); // Default 10

        // Validate workers_per_page
        if (!is_numeric($workers_per_page) || $workers_per_page <= 0) {
            $workers_per_page = 10; // Default value if NaN or non-positive
        }
        $user = Auth()->user();
        $typeofUser_id = $user->typeofUser_id ?? '';

        $workers = Worker::with('person', 'area', 'district.province.department')
        // ->when($typeofUser_id != 1 || $typeofUser_id != 2, function ($query) use ($branch_office_id) {
        //     return $query->where('branchOffice_id', $branch_office_id);
        // })
        // ->where('branchOffice_id', $branch_office_id)
            ->orderBy('id', 'desc')
            ->paginate($workers_per_page, ['*'], 'workers_page', $workers_page);

        // Vehicles pagination
        $vehicles_page = $request->input('vehicles_page', 1);
        $vehicles_per_page = $request->input('vehicles_per_page', 10); // Default 10

        // Validate vehicles_per_page
        if (!is_numeric($vehicles_per_page) || $vehicles_per_page <= 0) {
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
            "branchOffice"
        ])
        ->when($sinProgramaciones !== null, function ($query) use ($sinProgramaciones) {
            if ($sinProgramaciones === "1" || $sinProgramaciones === "true") {
                // Filtrar vehículos sin programaciones.
                $query->where(function ($query) {
                    $query->whereDoesntHave('tractProgrammings')
                          ->whereDoesntHave('platformProgrammings');
                });
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
        $address_page = $request->input('address_page', 1);
        $address_per_page = $request->input('address_per_page', 10); // Default 10

        // Validate address_per_page
        if (!is_numeric($address_per_page) || $address_per_page <= 0) {
            $address_per_page = 10; // Default value if NaN or non-positive
        }

        $address = Address::with('client')->orderBy('id', 'desc')->paginate($address_per_page, ['*'], 'address_page', $address_page);

        // Clients pagination
        $clients_page = $request->input('clients_page', 1);
        $clients_per_page = $request->input('clients_per_page', 10); // Default 10

        // Validate clients_per_page
        if (!is_numeric($clients_per_page) || $clients_per_page <= 0) {
            $clients_per_page = 10; // Default value if NaN or non-positive
        }

        $clients = Person::with(['tarifas.unity','products.unity'])
        ->where('branchOffice_id', $branch_office_id)
            ->where("type", 'Cliente')

            ->orderBy('id', 'desc')
            ->paginate($clients_per_page, ['*'], 'clients_page', $clients_page);

        // Subcontracts pagination
        $subcontracts_page = $request->input('subcontracts_page', 1);
        $subcontracts_per_page = $request->input('subcontracts_per_page', 10); // Default 10

        // Validate subcontracts_per_page
        if (!is_numeric($subcontracts_per_page) || $subcontracts_per_page <= 0) {
            $subcontracts_per_page = 10; // Default value if NaN or non-positive
        }

        $subcontracts = Subcontract::orderBy('id', 'desc')->paginate($subcontracts_per_page, ['*'], 'subcontracts_page', $subcontracts_page);

        // CarrierGuides pagination
        $carrierGuides_page = $request->input('carrierGuides_page', 1);
        $carrierGuides_per_page = $request->input('carrierGuides_per_page', 10); // Default 10

        // Validate carrierGuides_per_page
        if (!is_numeric($carrierGuides_per_page) || $carrierGuides_per_page <= 0) {
            $carrierGuides_per_page = 10; // Default value if NaN or non-positive
        }

        $carrierGuides = CarrierGuide::with('tract', 'platform', 'motive', 'branchOffice', 'origin', 'destination', 'sender', 'recipient', 'payResponsible', 'driver', 'copilot', 'districtStart.province.department', 'districtEnd.province.department', 'copilot.person', 'subcontract', 'driver.person', 'reception')
        // ->where('branchOffice_id', $branch_office_id)
            ->orderBy('id', 'desc')->paginate($carrierGuides_per_page, ['*'], 'carrierGuides_page', $carrierGuides_page);

        // Compose response
        $response = [
            'receptions' => [
                'total' => $receptions->total(),
                'data' => $receptions->items(),
                'current_page' => $receptions->currentPage(),
                'last_page' => $receptions->lastPage(),
                'per_page' => $receptions->perPage(),
                'pagination' => $receptions_per_page, // New field for pagination size
                'first_page_url' => $receptions->url(1),
                'from' => $receptions->firstItem(),
                'next_page_url' => $receptions->nextPageUrl(),
                'path' => $receptions->path(),
                'prev_page_url' => $receptions->previousPageUrl(),
                'to' => $receptions->lastItem(),
            ],
            'carrierGuides' => [
                'total' => $carrierGuides->total(),
                'data' => $carrierGuides->items(),
                'current_page' => $carrierGuides->currentPage(),
                'last_page' => $carrierGuides->lastPage(),
                'per_page' => $carrierGuides->perPage(),
                'pagination' => $carrierGuides_per_page, // New field for pagination size
                'first_page_url' => $carrierGuides->url(1),
                'from' => $carrierGuides->firstItem(),
                'next_page_url' => $carrierGuides->nextPageUrl(),
                'path' => $carrierGuides->path(),
                'prev_page_url' => $carrierGuides->previousPageUrl(),
                'to' => $carrierGuides->lastItem(),
            ],
            'places' => [
                'total' => $places->total(),
                'data' => $places->items(),
                'current_page' => $places->currentPage(),
                'last_page' => $places->lastPage(),
                'per_page' => $places->perPage(),
                'pagination' => $places_per_page, // New field for pagination size
                'first_page_url' => $places->url(1),
                'from' => $places->firstItem(),
                'next_page_url' => $places->nextPageUrl(),
                'path' => $places->path(),
                'prev_page_url' => $places->previousPageUrl(),
                'to' => $places->lastItem(),
            ],
            'workers' => [
                'total' => $workers->total(),
                'data' => $workers->items(),
                'current_page' => $workers->currentPage(),
                'last_page' => $workers->lastPage(),
                'per_page' => $workers->perPage(),
                'pagination' => $workers_per_page, // New field for pagination size
                'first_page_url' => $workers->url(1),
                'from' => $workers->firstItem(),
                'next_page_url' => $workers->nextPageUrl(),
                'path' => $workers->path(),
                'prev_page_url' => $workers->previousPageUrl(),
                'to' => $workers->lastItem(),
            ],
            'vehicles' => [
                'total' => $vehicles->total(),
                'data' => $vehicles->items(),
                'current_page' => $vehicles->currentPage(),
                'last_page' => $vehicles->lastPage(),
                'per_page' => $vehicles->perPage(),
                'pagination' => $vehicles_per_page, // New field for pagination size
                'first_page_url' => $vehicles->url(1),
                'from' => $vehicles->firstItem(),
                'next_page_url' => $vehicles->nextPageUrl(),
                'path' => $vehicles->path(),
                'prev_page_url' => $vehicles->previousPageUrl(),
                'to' => $vehicles->lastItem(),
            ],
            'address' => [
                'total' => $address->total(),
                'data' => $address->items(),
                'current_page' => $address->currentPage(),
                'last_page' => $address->lastPage(),
                'per_page' => $address->perPage(),
                'pagination' => $address_per_page, // New field for pagination size
                'first_page_url' => $address->url(1),
                'from' => $address->firstItem(),
                'next_page_url' => $address->nextPageUrl(),
                'path' => $address->path(),
                'prev_page_url' => $address->previousPageUrl(),
                'to' => $address->lastItem(),
            ],
            'clients' => [
                'total' => $clients->total(),
                'data' => $clients->items(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'pagination' => $clients_per_page, // New field for pagination size
                'first_page_url' => $clients->url(1),
                'from' => $clients->firstItem(),
                'next_page_url' => $clients->nextPageUrl(),
                'path' => $clients->path(),
                'prev_page_url' => $clients->previousPageUrl(),
                'to' => $clients->lastItem(),
            ],
            'subcontracts' => [
                'total' => $subcontracts->total(),
                'data' => $subcontracts->items(),
                'current_page' => $subcontracts->currentPage(),
                'last_page' => $subcontracts->lastPage(),
                'per_page' => $subcontracts->perPage(),
                'pagination' => $subcontracts_per_page, // New field for pagination size
                'first_page_url' => $subcontracts->url(1),
                'from' => $subcontracts->firstItem(),
                'next_page_url' => $subcontracts->nextPageUrl(),
                'path' => $subcontracts->path(),
                'prev_page_url' => $subcontracts->previousPageUrl(),
                'to' => $subcontracts->lastItem(),
            ],
        ];

        return response()->json($response);
    }

}
