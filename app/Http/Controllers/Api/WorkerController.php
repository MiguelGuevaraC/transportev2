<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\BranchOffice;
use App\Models\DetailWorker;
use App\Models\Person;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{

/**
 * @OA\Get(
 *     path="/transportev2/public/api/worker",
 *     summary="Get all Worker",
 *     tags={"Worker"},
 *     description="Show all Worker",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="branch_office_id",
 *         in="query",
 *         description="ID of the branch office",
 *         required=false,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="occupation",
 *         in="query",
 *         description="Occupation of the worker",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Status of the worker",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of Worker"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Branch Office Not Found.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Branch Office Not Found")
 *         )
 *     )
 * )
 */

    public function index(Request $request)
    {
        Worker::query()->update([
            'statusLicencia' => DB::raw("CASE
        WHEN licencia_date IS NULL THEN NULL
        WHEN licencia_date IS NOT NULL AND licencia_date < NOW() THEN 'Vencido'
        WHEN licencia_date IS NOT NULL THEN 'Vigente'
        ELSE statusLicencia  -- No cambia si licencia_date es NULL
    END"),
        ]);

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

        $occupation = $request->input('occupation') ?? '';

        $status = $request->input('status') ?? '';
        $namesCadena = $request->input('namesCadena') ?? '';
        $namesCadena = str_replace('%20', '', $namesCadena); // Elimina %20
        $namesCadena = strtolower($namesCadena); // Convierte todo a minúsculas
        
        $isConductor = $request->input('isConductor') ?? '';

        $workers_per_page = $request->input('per_page', 10); // Default items per page
        $workers_page = $request->input('page', 1); // Current page

        $user = Auth()->user();
        $typeofUser_id = $user->typeofUser_id ?? '';

        $workers = Worker::with('person', 'area', 'district.province.department', 'branchOffice')

            ->when(true, function ($query) use ($occupation, $isConductor) {
                // Si isConductor es false, excluye a los conductores
                if ($isConductor == "false") {
                    $query->where('occupation', '!=', 'Conductor');
                }

                // Siempre filtra por la ocupación, si se ha especificado
                if (!empty($occupation)) {
                    // Cambiar a where para aplicar ambos filtros como un AND
                    $query->where('occupation', $occupation);
                }
            })

            ->when($status != '', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($namesCadena != '', function ($query) use ($namesCadena) {
                // Filtrar por nombres o apellidos en el modelo 'person'
                return $query->whereHas('person', function ($innerQuery) use ($namesCadena) {
                    $innerQuery->where('documentNumber', 'LIKE', '%' . $namesCadena . '%')
                        ->orWhere('names', 'LIKE', '%' . $namesCadena . '%')
                        ->orWhere('fatherSurname', 'LIKE', '%' . $namesCadena . '%')
                        ->orWhere('motherSurname', 'LIKE', '%' . $namesCadena . '%')
                        ->orWhere('businessName', 'LIKE', '%' . $namesCadena . '%')
                        ->orWhere(DB::raw("CONCAT(names, ' ', fatherSurname, ' ', motherSurname)"), 'LIKE', '%' . $namesCadena . '%');
                });
                
            })
            ->orderBy(
                Person::select('names')
                    ->whereColumn('people.id', 'workers.person_id') // Relaciona la tabla person con workers
                    ->limit(1),
                'asc'
            )
            ->paginate($workers_per_page, ['*'], 'workers_page', $workers_page);

        // Calcular el total de páginas
        // $totalPages = ceil($list->total() / $workers_per_page);

        return response()->json([
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
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/transportev2/public/api/worker",
     *     summary="Store a new worker",
     *     tags={"Worker"},
     *     description="Create a new worker",
     *     security={{"bearerAuth":{}}},

     * @OA\RequestBody(
     *     required=true,
     *     description="Worker data",
     *     @OA\JsonContent(
     *
     *         @OA\Property(property="code", type="string", example="example_code", description="Code property"),
     *         @OA\Property(property="department", type="string", example="example_department", description="Department property"),
     *         @OA\Property(property="province", type="string", example="example_province", description="Province property"),
     *         @OA\Property(property="district", type="string", example="example_district", description="District property"),
     *         @OA\Property(property="maritalStatus", type="string", example="example_marital_status", description="Marital Status property"),
     *         @OA\Property(property="levelInstitution", type="string", example="example_level_institution", description="Level Institution property"),
     *         @OA\Property(property="occupation", type="string", example="example_occupation", description="Occupation property"),
     * @OA\Property(property="startDate", type="string", example="2024-05-08", description="Start Date property"),
     *
     *
     *         @OA\Property(property="person_id", type="integer", example=1, description="Person ID property"),
     *         @OA\Property(property="area_id", type="integer", example=1, description="Area ID property"),
     *         @OA\Property(property="branchOffice_id", type="integer", example=1, description="Branch Office ID property"),
     *     )
     * ),

     *     @OA\Response(
     *         response=200,
     *         description="Worker created successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Worker")
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
  
            'person_id' => [
                'required',
                Rule::unique('workers', 'person_id')->whereNull('deleted_at'),
            ],
            'area_id' => 'required|exists:areas,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'district_id' => 'required|exists:districts,id',

        ], [
            'person_id.required' => 'El campo persona es obligatorio.',
            'person_id.unique' => 'Esta persona ya está asignada a otro trabajador.',
            'area_id.required' => 'El campo área es obligatorio.',
            'area_id.exists' => 'El área seleccionada no es válida.',
            'branchOffice_id.required' => 'El campo sucursal es obligatorio.',
            'branchOffice_id.exists' => 'La sucursal seleccionada no es válida.',
            'district_id.required' => 'El campo distrito es obligatorio.',
            'district_id.exists' => 'El distrito seleccionado no es válido.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'code' => $request->input('code') ?? null,

            'maritalStatus' => $request->input('maritalStatus') ?? null,
            'levelInstitution' => $request->input('levelInstitution') ?? null,
            'occupation' => $request->input('occupation') ?? null,

            'startDate' => $request->input('startDate') ?? null,
            'licencia' => $request->input('licencia') ?? null,
            'licencia_date' => $request->input('licencia_date') ?? null,
            'district_id' => $request->input('district_id') ?? null,
            'person_id' => $request->input('person_id') ?? null,
            'area_id' => $request->input('area_id') ?? null,
            'branchOffice_id' => $request->input('branchOffice_id') ?? null,
        ];
        $object = Worker::create($data);

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('public/photosWorker');
            $object->pathPhoto = Storage::url($filePath);
            $object->save();
        }

        // Guarda el documento
        $currentDate = now();
        $licencia_date = $request->input('licencia_date');

        if ($licencia_date < $currentDate) {
            $object->statusLicencia = 'Vencido';
        } else {
            $object->statusLicencia = 'Vigente';
        }

        // Guarda el documento
        $object->save();

        $object = Worker::with('person', 'area', 'district.province.department', 'branchOffice')->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(), // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'POST', // Acción realizada
            'table_name' => 'workers', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Crea Trabajador', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

        return response()->json($object, 200);

    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/worker/{id}",
     *     summary="Update an existing worker",
     *     tags={"Worker"},
     *     description="Update an existing worker",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the worker to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * @OA\RequestBody(
     *     required=true,
     *     description="Worker data",
     *     @OA\JsonContent(
     *
     *         @OA\Property(property="code", type="string", example="example_code", description="Code property"),
     *         @OA\Property(property="department", type="string", example="example_department", description="Department property"),
     *         @OA\Property(property="province", type="string", example="example_province", description="Province property"),
     *         @OA\Property(property="district", type="string", example="example_district", description="District property"),
     *         @OA\Property(property="maritalStatus", type="string", example="example_marital_status", description="Marital Status property"),
     *         @OA\Property(property="levelInstitution", type="string", example="example_level_institution", description="Level Institution property"),
     *         @OA\Property(property="occupation", type="string", example="example_occupation", description="Occupation property"),
     * @OA\Property(property="startDate", type="string", example="2024-05-08", description="Start Date property"),
     *
     *
     *         @OA\Property(property="person_id", type="integer", example=1, description="Person ID property"),
     *         @OA\Property(property="area_id", type="integer", example=1, description="Area ID property"),
     *         @OA\Property(property="branchOffice_id", type="integer", example=1, description="Branch Office ID property"),
     *     )
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="Worker updated successfully",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Worker")
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
        $object = Worker::find($id);
        if (!$object) {
            return response()->json(['message' => 'Worker not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            // 'department' => 'required',
            // 'province' => 'required',
            // 'district' => 'required',
            // 'maritalStatus' => 'required',
            // 'levelInstitution' => 'required',
            // 'occupation' => 'required',
            // 'position' => 'required',
            // 'center' => 'required',
            // 'typeRelationship' => 'required',
            // 'startDate' => 'required',
            // 'endDate' => 'required',

            'district_id' => 'required|exists:districts,id',
            'person_id' => [
                'required',
                'string',
                Rule::unique('people', 'id')->whereNull('deleted_at')
                ->ignore($object->person_id),
            ],
            'area_id' => 'required|exists:areas,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };

        $Data = array_filter([
            'code' => $request->input('code') ?? null,
            // 'department' => $request->input('department') ?? null,
            // 'province' => $request->input('province') ?? null,
            // 'district' => $request->input('district') ?? null,
            'district_id' => $request->input('district_id') ?? null,
            'maritalStatus' => $request->input('maritalStatus') ?? null,
            'levelInstitution' => $request->input('levelInstitution') ?? null,
            'occupation' => $request->input('occupation') ?? null,
            'center' => $request->input('center') ?? null,
            'typeRelationship' => $request->input('typeRelationship') ?? null,
            'startDate' => $request->input('startDate') ?? null,
            'licencia' => $request->input('licencia') ?? null,
            'licencia_date' => $request->input('licencia_date') ?? null,
            'person_id' => $request->input('person_id') ?? null,
            'area_id' => $request->input('area_id') ?? null,
            'branchOffice_id' => $request->input('branchOffice_id') ?? null,

        ], $filterNullValues);

        $object->update($Data);

        if ($request->hasFile('file')) {

            if ($object->pathPhoto) {
                Storage::delete(str_replace('/storage/', 'public/', $object->pathPhoto));
            }

            // Almacenar la nueva imagen y obtener la ruta
            $filePath = $request->file('file')->store('public/photosWorker');
            $object->pathPhoto = Storage::url($filePath);
            $object->save();
        }

        $object = Worker::with('person', 'area', 'district.province.department', 'branchOffice')->find($object->id);

        return response()->json($object, 200);
    }

/**
 * @OA\Post(
 *     path="/transportev2/public/api/worker/{id}",
 *     summary="Create or update a worker",
 *     tags={"Worker"},
 *     description="Create a new worker or update an existing one",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the worker to update",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Worker data",
 *         @OA\JsonContent(
 *             @OA\Property(property="person_id", type="integer", example=1, description="Person ID property"),
 *             @OA\Property(property="district_id", type="string", example=1, description="District property"),
 *             @OA\Property(property="area_id", type="integer", example=1, description="Area ID property"),
 *             @OA\Property(property="branchOffice_id", type="integer", example=1, description="Branch Office ID property"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Worker created or updated successfully",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Worker")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Some fields are required.")
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
 *         response=404,
 *         description="Worker not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Worker not found.")
 *         )
 *     ),
 * )
 */
    public function createOrUpdate(Request $request, $id = null)
    {
        $worker = $id != 'null' ? Worker::find($id) : new Worker();
        // Validación de los datos
        $validator = validator()->make($request->all(), [
            'person_id' => [
                'required',
                'string',
                Rule::unique('workers', 'person_id')
                    ->whereNull('deleted_at')
                    ->ignore($worker->id),
            ],
            'area_id' => 'required|exists:areas,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'district_id' => 'required|exists:districts,id',
        ], [
            'person_id.required' => 'El campo persona es obligatorio.',
            'person_id.string' => 'El campo persona debe ser una cadena de texto.',
            'person_id.unique' => 'Esta persona ya está asignada a otro trabajador.',
            'area_id.required' => 'El campo área es obligatorio.',
            'area_id.exists' => 'El área seleccionada no es válida.',
            'branchOffice_id.required' => 'El campo sucursal es obligatorio.',
            'branchOffice_id.exists' => 'La sucursal seleccionada no es válida.',
            'district_id.required' => 'El campo distrito es obligatorio.',
            'district_id.exists' => 'El distrito seleccionado no es válido.',
        ]);
    

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Buscar o crear un nuevo objeto Worker
        $worker = $id != 'null' ? Worker::find($id) : new Worker();

        if ($id && !$worker) {
            return response()->json(['error' => 'Worker not found'], 404);
        }

        // Asignar datos al trabajador
        $worker->code = $request->input('code');
        $worker->levelInstitution = $request->input('levelInstitution');
        $worker->maritalStatus = $request->input('maritalStatus');
        $worker->district_id = $request->input('district_id');
        $worker->occupation = $request->input('occupation');
        $worker->person_id = $request->input('person_id');
        $worker->area_id = $request->input('area_id');
        $worker->branchOffice_id = $request->input('branchOffice_id');
        $worker->licencia = $request->input('licencia');
        $worker->licencia_date = $request->input('licencia_date');
        $worker->startDate = $request->input('startDate');

        // Guardar los datos
        $worker->save();

        // Manejar la subida del archivo (opcional)
        if ($request->hasFile('file')) {

            if ($worker->pathPhoto != null) {

                Storage::delete(str_replace('/storage/', 'public/', $worker->pathPhoto));
            }

            $filePath = $request->file('file')->store('public/photosWorker');
            $worker->pathPhoto = Storage::url($filePath);
            $worker->save();
        }

        // Retornar la información del trabajador con las relaciones cargadas
        $worker = Worker::with('person', 'area', 'district.province.department', 'branchOffice')->find($worker->id);

        Bitacora::create([
            'user_id' => Auth::id(), // ID del usuario que realiza la acción
            'record_id' => $worker->id, // El ID del usuario afectado
            'action' => 'POST', // Acción realizada
            'table_name' => 'workers', // Tabla afectada
            'data' => json_encode($worker),
            'description' => 'Actualiza o crea Trabajador', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

        return response()->json($worker, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/worker/{id}",
     *     summary="Get a worker by ID",
     *     tags={"Worker"},
     *     description="Retrieve a worker by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the worker to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Worker encontrado",
     * @OA\JsonContent(
     *   type="object",
     *   ref="#/components/schemas/Worker"
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
        $worker = Worker::with('person', 'area', 'district.province.department', 'branchOffice')->find($id);
        if (!$worker) {
            return response()->json(['message' => 'Worker not found'], 422);
        }
        return response()->json($worker, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/worker/{id}/historyProgramming",
     *     summary="Get a worker's history by ID",
     *     tags={"Worker"},
     *     description="Retrieve the programming history of a worker by their ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the worker to retrieve history for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Worker history retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/DetailWorker")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Worker not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Worker not found")
     *         )
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
    public function getWorkerHistory($workerId)
    {
        // Obtener el trabajador por su ID
        $worker = Worker::find($workerId);

        if (!$worker) {
            return response()->json(['error' => 'Worker not found'], 404);
        }

        // Definir el número de elementos por página
        $perPage = request()->input('per_page', 10); // Puedes ajustar el valor predeterminado

        // Obtener el historial de programaciones del trabajador con paginación y ordenado de forma descendente
        $history = DetailWorker::where('worker_id', $workerId)
            ->join('programmings', 'programmings.id', '=', 'detail_workers.programming_id') // Unir con la tabla 'programmings'
            ->with(
                'programming',
                'programming.tract',
                'programming.platform',
                'programming.origin',
                'programming.destination',
                'programming.carrierGuides.reception.origin',
                'programming.carrierGuides.reception.sender',
                'programming.carrierGuides.reception.destination',
                'programming.carrierGuides.reception.recipient',
                'programming.carrierGuides.reception.pickupResponsible',
                'programming.carrierGuides.reception.payResponsible',
                'programming.carrierGuides.reception.seller',
                'programming.carrierGuides.reception.pointDestination',
                'programming.carrierGuides.reception.pointSender'
            )
            ->orderBy('programmings.id', 'desc') // Ordenar por el ID de la tabla 'programmings'
            ->paginate($perPage);

        // Obtener solo las programaciones con pluck
        $programmingCollection = $history->pluck('programming');

        // Preparar la respuesta con los datos paginados
        return response()->json([
            'total' => $history->total(),
            'data' => $programmingCollection, // Aquí están las programaciones pluckeadas y ordenadas
            'current_page' => $history->currentPage(),
            'last_page' => $history->lastPage(),
            'per_page' => $history->perPage(),
            'pagination' => $perPage, // Nuevo campo para el tamaño de la paginación
            'first_page_url' => $history->url(1),
            'from' => $history->firstItem(),
            'next_page_url' => $history->nextPageUrl(),
            'path' => $history->path(),
            'prev_page_url' => $history->previousPageUrl(),
            'to' => $history->lastItem(),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/worker/{id}",
     *     summary="Delete a worker",
     *     tags={"Worker"},
     *     description="Delete a worker by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the worker to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Worker deleted successfully",
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
        $worker = Worker::find($id);
        if (!$worker) {
            return response()->json(['message' => 'Worker not found'], 422);
        }

        if ($worker->detailWorkers()->exists()) {
            return response()->json(['message' => 'Este trabajador pertenece a una programación'], 422);
        }

        if ($worker->carrierGuidesPilotos()->exists()) {
            return response()->json(['message' => 'Este trabajador esta asignado a una guia como Piloto'], 422);
        }
    
        if ($worker->carrierGuidesCoPilotos()->exists()) {
            return response()->json(['message' => 'Este trabajador esta asignado a una guia como Copiloto'], 422);
        }

        $object = Worker::with('person', 'area', 'district.province.department', 'branchOffice')
            ->find($id);

        Bitacora::create([
            'user_id' => Auth::id(), // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'DELETE', // Acción realizada
            'table_name' => 'workers', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Elimina Trabajador', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

        $worker->delete();
    }
}
