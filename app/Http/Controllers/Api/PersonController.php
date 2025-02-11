<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchOffice;
use App\Models\ContactInfo;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{

/**
 * @OA\Info(
 *      title="API's Hernandez Transportation System",
 *      version="1.0.0",
 *      description="API's for transportation management",
 * ),
 *   @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     in="header",
 *     name="Authorization",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * ),

 * Muestra todos los edificios activos.
 *
 * @return \Illuminate\Http\Response
 *
 * @OA\Get(
 *     path="/transportev2/public/api/clients",
 *     summary="Get all clients",
 *     tags={"Clients"},
 *     description="Show all clients",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of clients",
 *        @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Person")
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

    public function index(Request $request)
    {
        //ACTUALIZA LAS PERSONAS CON TIPO TRABAJADOR SI HAY RELACIÓN CON EL TRABAJADOR
        // DB::table('people')
        //     ->join('workers', 'workers.person_id', '=', 'people.id')
        //     ->where('people.type', '!=', 'Trabajador') // Solo actualiza si el type es diferente
        //     ->update(['people.type' => 'Trabajador']);

        DB::table('people')
            ->join('workers', 'workers.person_id', '=', 'people.id')
            ->where('people.type', '!=', 'Trabajador') // Solo actualiza si el tipo es diferente
            ->whereNull('people.deleted_at')           // Solo personas que no han sido eliminadas
            ->whereNull('workers.deleted_at')          // Solo trabajadores que no han sido eliminados
            ->update(['people.type' => 'Trabajador']);

        // Obtener la sucursal del usuario o la proporcionada
        $branch_office_id = $request->input('branch_office_id', auth()->user()->worker->branchOffice_id);
        $perPage          = $request->input('per_page', 15); // Número de resultados por página, por defecto 15

        $type = $request->input('type', 'Cliente'); // Número de resultados por página, por defecto 15

        // Validar la sucursal
        if ($branch_office_id && ! is_numeric($branch_office_id)) {
            return response()->json([
                "message" => "Invalid Branch Office ID",
            ], 400);
        }

        $branchOffice = BranchOffice::find($branch_office_id);
        if (! $branchOffice) {
            return response()->json([
                "message" => "Branch Office Not Found",
            ], 404);
        }

        // Obtener filtros
        $typePerson   = strtoupper($request->input('typePerson', ''));
        $nameFilter   = $request->input('namesCadena', '');
        $nro_document = $request->input('nro_document', '');
        $telephone    = $request->input('telephone', '');

        // Construir la consulta
        $query = Person::query()
        //  ->where('branchOffice_id', $branch_office_id)
        ;
        // Aplicar filtros por tipo de persona
        if ($typePerson === 'JURIDICA') {
            $query->where('typeofDocument', 'RUC');
        } elseif ($typePerson === 'NATURAL') {
            $query->where('typeofDocument', 'DNI');
        }
        if (! empty($nro_document)) {
            $query->where('documentNumber', 'LIKE', "%$nro_document%");
        }

        // Agregar filtro por `telephone` si no está vacío
        if (! empty($telephone)) {
            $query->where('telephone', 'LIKE', "%$telephone%"); // Usa LIKE para coincidencias parciales
        }

        if ($type) {
            $query->where('type', $type);
        }

        // Aplicar filtro por nombres
        if ($nameFilter) {
            $query->where(function ($subQuery) use ($nameFilter, $typePerson) {
                $subQuery->where('names', 'LIKE', '%' . $nameFilter . '%')
                    ->orWhere('fatherSurname', 'LIKE', '%' . $nameFilter . '%')
                    ->orWhere('motherSurname', 'LIKE', '%' . $nameFilter . '%')
                    ->orWhere('businessName', 'LIKE', '%' . $nameFilter . '%');

                // Si el tipo es JURIDICA, busca también en businessName
                if ($typePerson === 'JURIDICA') {
                    $subQuery->orWhere('businessName', 'LIKE', '%' . $nameFilter . '%');
                }
            });
        }

        // Paginación
        $persons = $query->orderByRaw("
        COALESCE(names, '') ASC,
        COALESCE(fatherSurname, '') ASC,
        COALESCE(motherSurname, '') ASC,
        COALESCE(businessName, '') ASC
    ")
            ->paginate($perPage);
        // Estructura de respuesta
        return response()->json([
            'persons' => [
                'total'          => $persons->total(),
                'data'           => $persons->items(),
                'current_page'   => $persons->currentPage(),
                'last_page'      => $persons->lastPage(),
                'per_page'       => $persons->perPage(),
                'pagination'     => $perPage, // Nuevo campo para el tamaño de la paginación
                'first_page_url' => $persons->url(1),
                'from'           => $persons->firstItem(),
                'next_page_url'  => $persons->nextPageUrl(),
                'path'           => $persons->path(),
                'prev_page_url'  => $persons->previousPageUrl(),
                'to'             => $persons->lastItem(),
            ],
        ], 200);
    }

    public function indexFiltro(Request $request)
    {
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

        $idPerson      = $request->input('idPerson');
        $personInclude = Person::find($idPerson);

        $type  = $request->input('type', ''); // Tipo por defecto Cliente
        $names = urldecode($request->input('names'));

        $names = str_replace('%20%', ' ', $names);

        // Consulta de personas según los filtros aplicados
        $persons = Person::with(['tarifas.unity', 'products.unity'])->when(! empty($names), function ($query) use ($names) {
            $query->where(function ($query) use ($names) {
                $query->whereRaw('lower(concat_ws(" ", names, fatherSurname, motherSurname)) LIKE ?', ['%' . strtolower($names) . '%'])
                    ->orWhereRaw('lower(businessName) LIKE ?', ['%' . strtolower($names) . '%'])
                    ->orWhereRaw('lower(documentNumber) LIKE ?', ['%' . strtolower($names) . '%']);
            });
        })
            ->when(! empty($type), function ($query) use ($type) {

                $query->where(function ($query) use ($type) {
                    $query->where('type', 'LIKE', '%' . $type . '%')
                        ->orWhere('type', 'Trabajador');

                });
            })
            ->when(! empty($names) || ! empty($type), function ($query) {
                $query->orWhere('id', '2');
            })
            ->orderByRaw('CASE
            WHEN businessName IS NOT NULL THEN businessName
            ELSE names
        END ASC')
            ->limit(20)
            ->get();

        // Si encuentra a la persona por ID, agregarla a los resultados
        if ($personInclude) {
            $persons->prepend($personInclude); // Agregar la persona al inicio del conjunto de resultados
        }

        return response()->json($persons, 200);
    }

/**
 * @OA\Get(
 *     path="/transportev2/public/api/naturalPerson",
 *     summary="Get all Natural People",
 *     tags={"Clients"},
 *     description="Show all Natural People",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of Natural People",
 *        @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Person")
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

    public function naturalPerson()
    {
        $persons = Person::whereRaw("LOWER(typeofDocument) = 'dni'")->orWhere('id', 2) // VARIOS
            ->orderBy('id', 'desc')->get();
        return response()->json($persons, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/legalEntity",
     *     summary="Get all Legal Entities",
     *     tags={"Clients"},
     *     description="Show all Legal Entities",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Legal Entities",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Person")
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

    public function legalEntity()
    {
        $persons = Person::whereRaw("LOWER(typeofDocument) = 'ruc'")->orderBy('id', 'desc')->get();
        return response()->json($persons, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/transportev2/public/api/clients",
     *     summary="Store a new client",
     *     tags={"Clients"},
     *     description="Create a new client",
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *     required=true,
     *     description="Client data",
     *     @OA\JsonContent(
     *         @OA\Property(property="typeofDocument", type="string", example="dni", description="Type of Document"),
     *         @OA\Property(property="documentNumber", type="string", example="75247586", description="Document Number"),
     *         @OA\Property(property="names", type="string", example="John", description="Names"),
     *         @OA\Property(property="fatherSurname", type="string", example="Doe", description="Father's Surname"),
     *         @OA\Property(property="motherSurname", type="string", example="Smith", description="Mother's Surname"),
     *         @OA\Property(property="birthDate", type="string", format="date", example="1990-01-01", description="Birth Date"),
     *         @OA\Property(property="address", type="string", example="123 Main Street", description="Address"),
     *         @OA\Property(property="telephone", type="string", example="123-456-7890", description="Telephone"),
     *         @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email"),
     *         @OA\Property(property="businessName", type="string", example=null, description="Business Name"),
     *         @OA\Property(property="comercialName", type="string", example=null, description="Commercial Name"),
     *         @OA\Property(property="fiscalAddress", type="string", example=null, description="Fiscal Address"),
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     * @OA\Property(property="representativePersonDni", type="string", example=null, description="Representative Person DNI"),
     *         @OA\Property(property="representativePersonName", type="string", example=null, description="Representative Person Name")
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="Client created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Person")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some Fields are required.")
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
    public function store(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'typeofDocument'   => 'required',
            'documentNumber'   => [
                'required',
                Rule::unique('people')->whereNull('deleted_at'),
            ],
            'branch_office_id' => 'nullable|exists:branch_offices,id',
            'file'             => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
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

        $clientData = [
            'typeofDocument'           => $request->input('typeofDocument'),
            'documentNumber'           => $request->input('documentNumber'),
            'names'                    => $request->input('names') ?? null,
            'fatherSurname'            => $request->input('fatherSurname') ?? null,
            'motherSurname'            => $request->input('motherSurname') ?? null,
            'birthDate'                => $request->input('birthDate') ?? null,
            'address'                  => $request->input('address') ?? null,
            'telephone'                => $request->input('telephone') ?? null,
            'email'                    => $request->input('email') ?? null,
            'businessName'             => $request->input('businessName') ?? null,
            'comercialName'            => $request->input('comercialName') ?? null,
            'fiscalAddress'            => $request->input('fiscalAddress') ?? null,
            'places'                   => $request->input('places') ?? null,

            'daysCredit'               => $request->input('daysCredit') ?? null,
            'type'                     => $request->input('type') ?? null,

            'representativePersonDni'  => $request->input('representativePersonDni') ?? null,
            'representativePersonName' => $request->input('representativePersonName') ?? null,
            'branchOffice_id'          => $branch_office_id,
        ];

        $client = Person::create($clientData);

        if ($request->input('isContact') == 'true') {
            $data = [

                'typeofDocument' => $request->input('typeofDocument'),
                'documentNumber' => $request->input('documentNumber'),
                'names'          => $request->input('names') ?? $client->businessName,
                'fatherSurname'  => $request->input('fatherSurname'),
                'motherSurname'  => $request->input('motherSurname'),

                'address'        => $request->input('address') ?? null,
                'telephone'      => $request->input('telephone') ?? null,
                'email'          => $request->input('email') ?? null,

                'person_id'      => $client->id,

            ];

            $object = ContactInfo::create($data);

            // $object = ContactInfo::with(["person"])->find($object->id);
        }

        $client = Person::with('contacts')->find($client->id);
        return response()->json($client, 200);

    }
    /**
     * @OA\Get(
     *     path="/transportev2/public/api/clients/{id}",
     *     summary="Get a client by ID",
     *     tags={"Clients"},
     *     description="Retrieve a client by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the client to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Cliente encontrado",
     * @OA\JsonContent(
     *   type="object",
     *   ref="#/components/schemas/Person"
     *)
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
        $client = Person::find($id);
        if (! $client) {
            return response()->json(['message' => 'Client not found'], 422);
        }
        return response()->json($client, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/clients/{id}",
     *     summary="Update an existing client",
     *     tags={"Clients"},
     *     description="Update an existing client",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the client to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Client data",
     *     @OA\JsonContent(
     *         @OA\Property(property="typeofDocument", type="string", example="dni", description="Type of Document"),
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     * @OA\Property(property="documentNumber", type="string", example="75247586", description="Document Number"),
     *         @OA\Property(property="names", type="string", example="John", description="Names"),
     *         @OA\Property(property="fatherSurname", type="string", example="Doe 2", description="Father's Surname"),
     *         @OA\Property(property="motherSurname", type="string", example="Smith 2", description="Mother's Surname"),
     *         @OA\Property(property="birthDate", type="string", format="date", example="1990-01-01", description="Birth Date"),
     *         @OA\Property(property="address", type="string", example="123 Main Street", description="Address"),
     *         @OA\Property(property="telephone", type="string", example="123-456-7890", description="Telephone"),
     *         @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email"),
     *         @OA\Property(property="businessName", type="string", example=null, description="Business Name"),
     *         @OA\Property(property="comercialName", type="string", example=null, description="Commercial Name"),
     *         @OA\Property(property="fiscalAddress", type="string", example=null, description="Fiscal Address"),
     *         @OA\Property(property="representativePersonDni", type="string", example=null, description="Representative Person DNI"),
     *         @OA\Property(property="representativePersonName", type="string", example=null, description="Representative Person Name")
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Person")
     *         )
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
        $client = Person::find($id);
        if (! $client) {
            return response()->json(['message' => 'Client not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'typeofDocument'   => 'required',
            'documentNumber'   => [
                'required',
                Rule::unique('people')->ignore($id)->whereNull('deleted_at'),
            ],
            'branch_office_id' => 'nullable|exists:branch_offices,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $routeData = array_filter([
            'typeofDocument' => $request->input('typeofDocument'),
            'documentNumber' => $request->input('documentNumber'),
            'birthDate'      => $request->input('birthDate'),
            'address'        => $request->input('address'),
            'telephone'      => $request->input('telephone'),
            'email'          => $request->input('email'),
            'branch_office'  => $request->input('branch_office'),
            'daysCredit'     => $request->input('daysCredit'),
            'places'         => $request->input('places'),

            'type'           => $request->input('type'),
        ], $filterNullValues);

        $client->update($routeData);

        $client->names         = $request->input('names') ?? null;
        $client->fatherSurname = $request->input('fatherSurname') ?? null;
        $client->motherSurname = $request->input('motherSurname') ?? null;

        $client->businessName             = $request->input('businessName') ?? null;
        $client->comercialName            = $request->input('comercialName') ?? null;
        $client->fiscalAddress            = $request->input('fiscalAddress') ?? null;
        $client->representativePersonDni  = $request->input('representativePersonDni') ?? null;
        $client->representativePersonName = $request->input('representativePersonName') ?? null;

        // if ($request->hasFile('file')) {

        //     if ($client->pathPhoto) {
        //         Storage::delete(str_replace('/storage/', 'public/', $client->pathPhoto));
        //     }

        //     // Almacenar la nueva imagen y obtener la ruta
        //     $filePath = $request->file('file')->store('public/photosWorker');
        //     $client->pathPhoto = Storage::url($filePath);
        //     $client->save();
        // }

        $client->save();
        $client = Person::find($client->id);

        return response()->json($client, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/clients/{id}",
     *     summary="Delete a client",
     *     tags={"Clients"},
     *     description="Delete a client by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the client to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
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
        $client = Person::find($id);
        if (! $client) {
            return response()->json(['message' => 'Client not found'], 422);
        }
        $client->delete();
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/clients/{id}/changeState",
     *     summary="Change the state of a client",
     *     tags={"Clients"},
     *     description="Change the state (active/inactive) of a client by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the client to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State of the client changed successfully",
     *         @OA\JsonContent(
     *             type="boolean",
     *             example=true,
     *             description="New state of the client (1 for active, 0 for inactive)"
     *         )
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
    public function changeState($id)
    {
        $client = Person::find($id);
        if (! $client) {
            return response()->json(['message' => 'Client not found'], 422);
        }

        // Cambiar el estado del cliente
        $client->state = ! $client->state;

        // Guardar los cambios en la base de datos
        $client->save();

        return response()->json($client->state, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/personsWithDebt",
     *     summary="Get Persons with Debt",
     *     tags={"Clients"},
     *     description="Retrieve all Persons who have receptions with debt amounts greater than 0, optionally filtered by 'conditionPay'. Includes the related receptions.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="conditionPay",
     *         in="query",
     *         required=false,
     *         description="Filter persons by the condition of payment. If not provided, all persons with debt will be returned.",
     *         @OA\Schema(type="string", example="Pendiente")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of persons with their debt receptions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(
     *                     property="receptionsWithDebt",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Reception")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="An error occurred while retrieving data.")
     *         )
     *     )
     * )
     */
    public function personsWithDebt(Request $request)
    {
        $branch_office_id = $request->input('branch_office_id');
        $person_id        = $request->input('person_id');
        $moviment_id      = $request->input('moviment_id'); // Parámetro de Moviment
        $person           = Person::find($person_id);

        $conditionPay = $request->input('conditionPay') ?? '';
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

        $names = $request->input('names');

        $user    = Auth()->user();
        $user_id = $user->id ?? '';

        // Obteniendo personas con recepciones que tienen deudas y aplicando la lógica de filtrado por movimiento
        $persons = Person::
            with('receptionsWithDebt', 'receptionsWithDebt.firstCarrierGuide')->
            whereHas('receptionsWithDebt', function ($query) use ($moviment_id) {
            // Si se envía moviment_id, incluir recepciones con el movimiento específico y las que no tienen venta
            if ($moviment_id) {
                //dd(cf);
                $query->where(function ($query) use ($moviment_id) {
                                                        // Filtrar las recepciones que NO tienen moviment o que tienen el moviment_id específico
                    $query->whereDoesntHave('moviment') // Todas las recepciones que NO tienen moviment
                        ->orWhereHas('moviment', function ($subQuery) use ($moviment_id) {

                            // Si se envía moviment_id, incluir las que tienen ese movimiento
                            $subQuery->where('id', $moviment_id);
                        });
                });

            } else {

                // Si no se envía moviment_id, incluir solo las recepciones que no tienen venta
                $query->where(function ($query) use ($moviment_id) {
                                                        // Filtrar las recepciones que NO tienen moviment o que tienen el moviment_id específico
                    $query->whereDoesntHave('moviment') // Todas las recepciones que NO tienen moviment
                        ->orWhereHas('moviment', function ($subQuery) use ($moviment_id) {

                            // Si se envía moviment_id, incluir las que tienen ese movimiento
                            $subQuery->where('status_facturado', 'Anulada')
                                ->orWhere('status', 'Anulada')->orWhere('status', 'Anulado');
                        });
                });
            }
        })
            ->where(function ($query) use ($names) {
                // Filtro por nombres
                $query->whereRaw('lower(motherSurname) LIKE ?', ['%' . strtolower($names) . '%'])
                    ->orWhereRaw('lower(fatherSurname) LIKE ?', ['%' . strtolower($names) . '%'])
                    ->orWhereRaw('lower(names) LIKE ?', ['%' . strtolower($names) . '%'])
                    ->orWhereRaw('lower(businessName) LIKE ?', ['%' . strtolower($names) . '%']);
            })
            ->with([
                'receptionsWithDebt' => function ($query) use ($moviment_id) {
                    // Filtrar las recepciones que no tienen venta o que tienen el movimiento específico
                    if ($moviment_id) {
                        $query->where(function ($query) use ($moviment_id) {
                            $query->whereDoesntHave('moviment') // Sin ventas
                                ->orWhereHas('moviment', function ($subQuery) use ($moviment_id) {
                                    $subQuery->where('id', $moviment_id); // Con el movimiento específico
                                });
                        });
                    } else {

                        // Si no se envía moviment_id, incluir solo las recepciones que no tienen venta
                        $query->where(function ($query) use ($moviment_id) {
                            $query->whereDoesntHave('moviment') // Sin ventas
                                ->orWhereHas('moviment', function ($subQuery) use ($moviment_id) {
                                    $subQuery->where('status', 'Anulada');             // Con el movimiento específico
                                    $subQuery->orWhere('status_facturado', 'Anulada'); // Con el movimiento específico
                                });
                        });
                    }

                    // Cargar las relaciones necesarias
                    $query->with(['origin', 'destination', 'details' => function ($query) {
                        $query->select('description', 'reception_id');
                    }]);
                },
            ])->limit(50);

        // Filtrar por condición de pago si es necesario
        if ($conditionPay !== '') {
            $persons->where('conditionPay', $conditionPay);
        }

        if ($person) {
            $persons->where('id', $person->id);
        }

        // Obtener las personas con sus recepciones y detalles ya cargados
        $persons = $persons->orderBy('id', 'desc')->get();

        // Agregar las descripciones de detalles como una cadena formateada
        $persons->each(function ($person) {
            $person->receptionsWithDebt->each(function ($reception) {
                // Obtener el nombre del origen y destino
                $originName      = strtoupper($reception->origin->name ?? 'ORIGEN DESCONOCIDO');
                $destinationName = strtoupper($reception->destination->name ?? 'DESTINO DESCONOCIDO');

                // Unir las descripciones de los detalles en una cadena separada por comas
                $detailsDescriptions = implode(', ', $reception->details->pluck('description')->toArray());

                // Construir la descripción en el formato solicitado
                $reception->description = "SERVICIO DE TRANSPORTE DE $originName - $destinationName, " .
                    "CON TRASLADO DE $detailsDescriptions, {$reception->comment}";
            });
        });

        return response()->json($persons, 200);
    }

}
