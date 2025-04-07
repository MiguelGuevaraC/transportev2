<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\BranchOffice;
use App\Models\Person;
use App\Models\Photos;
use App\Models\Vehicle;
use App\Models\Worker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{

    /**
     *
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/transportedev/public/api/vehicleAll",
     *     summary="Get all Vehicle",
     *     tags={"Vehicle"},
     *     description="Show all Vehicle",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of Vehicle",
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

    public function showAll(Request $request)
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

        $persons = Vehicle::with(["modelFunctional", "photos", "documents", "typeCarroceria.typeCompany", "responsable.worker", "companyGps", 'branchOffice'])
            ->where('branchOffice_id', $branch_office_id)->orderBy('currentPlate', 'asc')->get();
        return response()->json($persons, 200);
    }

/**
 * Retrieve a list of vehicles.
 *
 * @return \Illuminate\Http\Response
 *
 * @OA\Get(
 *     path="/transportedev/public/api/vehicle",
 *     summary="Get all Vehicles",
 *     tags={"Vehicle"},
 *     description="Retrieve a paginated list of vehicles, optionally filtered by branch office or type of vehicle (e.g., Tracto, Carreta).",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="branch_office_id",
 *         in="query",
 *         description="ID of the branch office to filter vehicles by",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="typeCar",
 *         in="query",
 *         description="Type of vehicle to filter by (e.g., Tracto, Carreta)",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"Tracto", "Carreta"},
 *             example="Tracto"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of Vehicles",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Vehicle")
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
 *         description="Branch Office Not Found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Branch Office Not Found")
 *         )
 *     ),
 * )
 */
    public function index(Request $request)
    {
        // Parámetros de entrada
        $branch_office_id = $request->input('branch_office_id');
        $sinProgramaciones = $request->input('sinProgramaciones'); //

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
        ])->orderBy('currentPlate', 'asc');

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
        $lista = $vehiclesQuery->paginate($pagination);

        // Respuesta en formato JSON
        return response()->json([
            'total' => $lista->total(),
            'data' => $lista->items(),
            'current_page' => $lista->currentPage(),
            'last_page' => $lista->lastPage(),
            'per_page' => $lista->perPage(),
            'pagination' => $pagination,
            'first_page_url' => $lista->url(1),
            'from' => $lista->firstItem(),
            'next_page_url' => $lista->nextPageUrl(),
            'path' => $lista->path(),
            'prev_page_url' => $lista->previousPageUrl(),
            'to' => $lista->lastItem(),
        ], 200);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/vehicle",
 *     summary="Create a new vehicle",
 *     tags={"Vehicle"},
 *     description="Create a new vehicle and upload associated photos.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Vehicle data",
 *         @OA\JsonContent(

 *             @OA\Property(property="oldPlate", type="string", example="ABC123"),
 *             @OA\Property(property="currentPlate", type="string", example="XYZ789"),
 *             @OA\Property(property="numberMtc", type="string", example="123456789"),
 *             @OA\Property(property="brand", type="string", example="Toyota"),
 *             @OA\Property(property="numberModel", type="string", example="Corolla"),
 *             @OA\Property(property="tara", type="number", example=1500.5),
 *             @OA\Property(property="netWeight", type="number", example=2000.75),
 *             @OA\Property(property="usefulLoad", type="number", example=500.25),
 *             @OA\Property(property="ownerCompany", type="string", example="Transportes S.A."),
 *             @OA\Property(property="length", type="number", example=5.2),
 *             @OA\Property(property="width", type="number", example=2.0),
 *             @OA\Property(property="height", type="number", example=1.8),
 *             @OA\Property(property="ejes", type="integer", example=2),
 *             @OA\Property(property="wheels", type="integer", example=4),
 *             @OA\Property(property="color", type="string", example="Red"),
 *             @OA\Property(property="typeCar", type="string", example="Tracto"),
 *             @OA\Property(property="year", type="integer", example=2020),
 *             @OA\Property(property="tireType", type="string", example="Type A"),
 *             @OA\Property(property="tiresuspension", type="string", example="Suspension B"),
 *             @OA\Property(property="modelVehicle_id", type="integer", example=1),
 *             @OA\Property(property="branchOffice_id", type="integer", example=1),
 *             @OA\Property(property="bonus", type="number", example=1000.0),
 *             @OA\Property(property="isConection", type="boolean", example=true),
 *             @OA\Property(property="companyGps_id", type="integer", example=1),
 *             @OA\Property(property="mode", type="string", example="Standard"),
 *             @OA\Property(property="responsable_id", type="integer", example=1),
 *             @OA\Property(
 *                 property="photos",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="file", type="string", format="binary", description="Photo file"),
 *                     @OA\Property(property="type", type="string", description="Type of the photo", example="Documento/Imagen")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Vehicle created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Vehicle created successfully"),
 *             @OA\Property(property="vehicle", ref="#/components/schemas/Vehicle")
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

    public function createOrUpdate(Request $request, $id = null)
    {
        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'modelVehicle_id' => 'required|exists:model_functionals,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'typeCar' => 'nullable|in:Tracto,Carreta',
            'type' => 'nullable|in:Documento,Imagen',
            'bonus' => 'nullable',
            'isConection' => 'nullable|boolean',
            'companyGps_id' => 'nullable|exists:people,id',
            'mode' => 'nullable|string',
            // 'responsable_id' => 'nullable|exists:woid',
            'typeCarroceria_id' => 'nullable|exists:type_carrocerias,id',
            'photos.*.file' => 'nullable',
            'currentPlate' => [
                'required',
                'string',
                Rule::unique('vehicles')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            Log::warning('Validación fallida', ['errors' => $validator->errors()->toArray(), 'request_data' => $request->all()]);
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // $person= Person::find($worker->person_id);

        try {
            // Crear o actualizar el vehículo
            $vehicle = Vehicle::updateOrCreate(
                ['id' => $id], // Si se proporciona un ID, se actualizará el vehículo existente, si no, se creará uno nuevo
                [
                    // 'status' => $request->input('status') ?? 'Disponible',
                    'oldPlate' => $request->input('oldPlate'),
                    'currentPlate' => $request->input('currentPlate'),
                    'numberMtc' => $request->input('numberMtc'),
                    'brand' => $request->input('brand'),
                    'numberModel' => $request->input('numberModel'),
                    'tara' => $request->input('tara'),
                    'netWeight' => $request->input('netWeight'),
                    'usefulLoad' => $request->input('usefulLoad'),
                    'ownerCompany' => $request->input('ownerCompany'),
                    'length' => $request->input('length'),
                    'width' => $request->input('width'),
                    'height' => $request->input('height'),
                    'ejes' => $request->input('ejes'),
                    'wheels' => $request->input('wheels'),
                    'color' => $request->input('color'),
                    'typeCar' => $request->input('typeCar'),
                    'year' => $request->input('year'),
                    'tireType' => $request->input('tireType'),
                    'tireSuspension' => $request->input('tiresuspension'),
                    'modelVehicle_id' => $request->input('modelVehicle_id'),
                    'typeCarroceria_id' => $request->input('typeCarroceria_id'),
                    'branchOffice_id' => $request->input('branchOffice_id'),
                    'bonus' => $request->input('bonus'),
                    'isConection' => $request->input('isConection') ? 1 : 0,
                    'companyGps_id' => $request->input('companyGps_id'),
                    'mode' => $request->input('mode'),
                    // 'responsable_id' => $worker->person->id,
                ]
            );
            $worker = Worker::find($request->input('responsable_id'));

            if ($worker) {
                $vehicle->responsable_id = $worker->person->id;
            }
            // Procesar las fotos
            if ($request->has('photos')) {
                $existingPhotos = Photos::where('vehicle_id', $vehicle->id)->get();
                foreach ($existingPhotos as $photo) {
                    $filePath = str_replace('/storage', 'public', $photo->description);
                    Storage::delete($filePath); // Eliminar el archivo de almacenamiento
                    $photo->delete(); // Eliminar la entrada de la base de datos
                }

                foreach ($request->photos as $photoData) {
                    // Verificar si la imagen viene en binario
                    if (isset($photoData['file']) && $photoData['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $photoFile = $photoData['file'];
                    } else {
                        continue; // Saltar a la siguiente iteración si no hay archivo válido
                    }

                    $fileType = $photoData['type'];

                    // Crear un directorio para las fotos del vehículo
                    $vehicleDirectory = 'public/photosVehicle/' . $vehicle->id;
                    $filePath = $photoFile->store($vehicleDirectory); // Guardar la foto en el directorio

                    $photoUrl = Storage::url($filePath); // Obtener la URL de la foto

                    // Crear o actualizar la entrada en la base de datos para la foto
                    Photos::updateOrCreate(
                        [
                            'vehicle_id' => $vehicle->id,
                            'name' => $photoFile->getClientOriginalName(),
                            'type' => $fileType,
                        ],
                        [
                            'description' => $photoUrl, // URL de la foto
                        ]
                    );
                }
            }

            // Cargar las relaciones
            $vehicle = Vehicle::with(["modelFunctional", "photos",
                "documents", "typeCarroceria.typeCompany", "responsable.worker",
                "companyGps", 'branchOffice'])->find($vehicle->id);

            Bitacora::create([
                'user_id' => Auth::id(), // ID del usuario que realiza la acción
                'record_id' => $vehicle->id, // El ID del usuario afectado
                'action' => 'PUT/POST', // Acción realizada
                'table_name' => 'vehicles', // Tabla afectada
                'data' => json_encode($vehicle),
                'description' => 'Actualizar o crear Vehículo', // Descripción de la acción
                'ip_address' => $request->ip(), // Dirección IP del usuario
                'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
            ]);
            return response()->json($vehicle, 200);
        } catch (Exception $e) {
            // Registrar el error en el log
            Log::error('Error al crear o actualizar el vehículo', [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);
            return response()->json(['error' => 'Hubo un problema al procesar la solicitud.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/vehicle/{id}",
     *     summary="Get a vehicle by ID",
     *     tags={"Vehicle"},
     *     description="Retrieve a vehicle by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vehicle to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Vehicle encontrado",

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

        $vehicle = Vehicle::with(["modelFunctional", "photos", "documents", "typeCarroceria.typeCompany", "responsable.worker", "companyGps", 'branchOffice'])->find($id);
        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 422);
        }
        return response()->json($vehicle, 200);
    }
/**
 * @OA\Post(
 *     path="/transportedev/public/api/vehicle/{id}",
 *     summary="Create or update a vehicle",
 *     tags={"Vehicle"},
 *     description="Create a new vehicle or update an existing one and optionally upload associated photos.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the vehicle. Leave empty to create a new vehicle.",
 *         @OA\Schema(type="integer", nullable=true)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Vehicle data",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="oldPlate", type="string", example="ABC123"),
 *                 @OA\Property(property="currentPlate", type="string", example="XYZ789"),
 *                 @OA\Property(property="numberMtc", type="string", example="123456789"),
 *                 @OA\Property(property="brand", type="string", example="Toyota"),
 *                 @OA\Property(property="numberModel", type="string", example="Corolla"),
 *                 @OA\Property(property="tara", type="number", example=1500.5),
 *                 @OA\Property(property="netWeight", type="number", example=2000.75),
 *                 @OA\Property(property="usefulLoad", type="number", example=500.25),
 *                 @OA\Property(property="ownerCompany", type="string", example="Transportes S.A."),
 *                 @OA\Property(property="length", type="number", example=5.2),
 *                 @OA\Property(property="width", type="number", example=2.0),
 *                 @OA\Property(property="height", type="number", example=1.8),
 *                 @OA\Property(property="ejes", type="integer", example=2),
 *                 @OA\Property(property="wheels", type="integer", example=4),
 *                 @OA\Property(property="color", type="string", example="Red"),
 *                 @OA\Property(property="typeCar", type="string", example="Tracto"),
 *                 @OA\Property(property="year", type="integer", example=2020),
 *                 @OA\Property(property="tireType", type="string", example="Type A"),
 *                 @OA\Property(property="tiresuspension", type="string", example="Suspension B"),
 *                 @OA\Property(property="modelVehicle_id", type="integer", example=1),
 *                 @OA\Property(property="branchOffice_id", type="integer", example=1),
 *                 @OA\Property(property="bonus", type="number", example=1000.0),
 *                 @OA\Property(property="isConection", type="boolean", example=true),
 *                 @OA\Property(property="companyGps_id", type="integer", example=1),
 *                 @OA\Property(property="mode", type="string", example="Standard"),
 *                 @OA\Property(property="responsable_id", type="integer", example=1),
 *                 @OA\Property(
 *                     property="photos",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="file", type="string", format="binary", description="Photo file"),
 *                         @OA\Property(property="type", type="string", description="Type of the photo", example="Documento/Imagen")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Vehicle created or updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Vehicle created or updated successfully"),
 *             @OA\Property(property="vehicle", ref="#/components/schemas/Vehicle")
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
    public function update(Request $request, $id = null)
    {
        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'modelVehicle_id' => 'required|exists:model_functionals,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'typeCar' => 'nullable|in:Tracto,Carreta',
            'type' => 'nullable|in:Documento,Imagen',
            'bonus' => 'nullable|numeric',
            'isConection' => 'nullable|boolean',
            'companyGps_id' => 'nullable|exists:people,id',
            'mode' => 'nullable|string',
            'responsable_id' => 'nullable|exists:people,id',
            'typeCarroceria_id' => 'nullable|exists:type_carrocerias,id',
            'photos.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Encontrar o crear el vehículo
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            $vehicle = new Vehicle();
        }

        // Actualizar o establecer los datos del vehículo
        $data = [
            // 'status' => $request->input('status'),
            'oldPlate' => $request->input('oldPlate'),
            'currentPlate' => $request->input('currentPlate'),
            'numberMtc' => $request->input('numberMtc'),
            'brand' => $request->input('brand'),
            'numberModel' => $request->input('numberModel'),
            'tara' => $request->input('tara'),
            'netWeight' => $request->input('netWeight'),
            'usefulLoad' => $request->input('usefulLoad'),
            'ownerCompany' => $request->input('ownerCompany'),
            'length' => $request->input('length'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
            'ejes' => $request->input('ejes'),
            'wheels' => $request->input('wheels'),
            'color' => $request->input('color'),
            'typeCar' => $request->input('typeCar'),
            'year' => $request->input('year'),
            'tireType' => $request->input('tireType'),
            'tiresuspension' => $request->input('tiresuspension'),
            'modelVehicle_id' => $request->input('modelVehicle_id'),
            'typeCarroceria_id' => $request->input('typeCarroceria_id'),
            'branchOffice_id' => $request->input('branchOffice_id'),
            'bonus' => $request->input('bonus'),
            'isConection' => $request->input('isConection'),
            'companyGps_id' => $request->input('companyGps_id'),
            'mode' => $request->input('mode'),
            'responsable_id' => $request->input('responsable_id'),
        ];

        $vehicle->fill($data);
        $vehicle->save();

        // Procesar las fotos
        if ($request->has('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $fileType = $request->input('photos')[array_search($photoFile, $request->file('photos'))]['type'];

                // Crear un nombre único para el archivo
                $filename = time() . '_' . $photoFile->getClientOriginalName();
                $photoFile->storeAs('public/photos', $filename);

                // Guardar la foto en la base de datos o hacer lo que sea necesario
                $photo = new Photos();
                $photo->file = $filename;
                $photo->type = $fileType;
                $photo->vehicle_id = $vehicle->id;
                $photo->save();
            }
        }

        return response()->json([
            'message' => 'Vehicle created or updated successfully',
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/transportedev/public/api/vehicle/{id}",
     *     summary="Delete a vehicle",
     *     tags={"Vehicle"},
     *     description="Delete a vehicle by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vehicle to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comision Agent deleted successfully",
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
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 422);
        }

        $vehicle1 = Vehicle::with(["modelFunctional", "photos",
            "documents", "typeCarroceria.typeCompany", "responsable.worker",
            "companyGps", 'branchOffice'])->find($id);

        Bitacora::create([
            'user_id' => Auth::id(), // ID del usuario que realiza la acción
            'record_id' => $vehicle1->id, // El ID del usuario afectado
            'action' => 'DELETE', // Acción realizada
            'table_name' => 'vehicles', // Tabla afectada
            'data' => json_encode($vehicle1),
            'description' => 'Eliminar Vehículo', // Descripción de la acción
            'ip_address' => $request->ip(), // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        $vehicle->delete();

    }
    public function getVehicleHistory($vehicleId)
    {
        // Obtener el vehículo por su ID
        $vehicle = Vehicle::find($vehicleId);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        // Definir el número de elementos por página
        $perPage = request()->input('per_page', 10); // Valor predeterminado de la paginación

        // Obtener el historial de programaciones del vehículo con paginación
        $history = Vehicle::with(['tractProgrammings.carrierGuides.reception', 'platformProgrammings.carrierGuides.reception'])
            ->where('vehicles.id', $vehicleId)
            ->paginate($perPage);

        // Mapear todas las programaciones, incluyendo tract y platform
        $programmingCollection = collect($history->items())->flatMap(function ($item) {
            // Obtener y ordenar las programaciones de tract y platform por id en orden descendente
            $tractProgramings = collect($item->tractProgrammings ?? [])->sortByDesc('id'); // Convertir a colección y ordenar
            $platformProgramings = collect($item->platformProgrammings ?? [])->sortByDesc('id'); // Convertir a colección y ordenar

            // Combinar ambas colecciones de programaciones
            return $tractProgramings->map(function ($programming) {
                return [
                    'programming' => [
                        'id' => $programming->id, // ID
                        'departureDate' => $programming->departureDate, // Agrega el valor de departureDate
                        'estimatedArrivalDate' => $programming->estimatedArrivalDate, // Agrega el valor de estimatedArrivalDate
                        'actualArrivalDate' => $programming->actualArrivalDate,

                        'tract' => [
                            'id' => $programming->tract?->id, // ID de tract opcional
                        ],
                        'platform' => [
                            'id' => $programming->platform?->id, // ID de platform opcional
                        ],
                        'origin' => $programming->origin, // Origen
                        'destination' => $programming->destination, // Destino
                        'carrierGuides' => $programming->carrierGuides->map(function ($guide) {
                            return [
                                'reception' => [
                                    'origin' => optional($guide->reception)->origin,
                                    'sender' => optional($guide->reception)->sender,
                                    'destination' => optional($guide->reception)->destination,
                                    'recipient' => optional($guide->reception)->recipient,
                                    'pickupResponsible' => optional($guide->reception)->pickupResponsible,
                                    'payResponsible' => optional($guide->reception)->payResponsible,
                                    'seller' => optional($guide->reception)->seller,
                                    'pointDestination' => optional($guide->reception)->pointDestination,
                                    'pointSender' => optional($guide->reception)->pointSender,
                                ],
                            ];
                        })->whenEmpty(function () {
                            return collect([null]); // Devolver null si no hay guías
                        }),
                    ],
                ];
            })->merge($platformProgramings->map(function ($programming) {
                return [
                    'programming' => [
                        'id' => $programming->id, // ID
                        'departureDate' => $programming->departureDate, // Agrega el valor de departureDate
                        'estimatedArrivalDate' => $programming->estimatedArrivalDate, // Agrega el valor de estimatedArrivalDate
                        'actualArrivalDate' => $programming->actualArrivalDate,

                        'tract' => [
                            'id' => $programming->tract?->id, // ID de tract opcional
                        ],
                        'platform' => [
                            'id' => $programming->platform?->id, // ID de platform opcional
                        ],
                        'origin' => $programming->origin, // Origen
                        'destination' => $programming->destination, // Destino
                        'carrierGuides' => $programming->carrierGuides->map(function ($guide) {
                            return [
                                'reception' => [
                                    'origin' => optional($guide->reception)->origin,
                                    'sender' => optional($guide->reception)->sender,
                                    'destination' => optional($guide->reception)->destination,
                                    'recipient' => optional($guide->reception)->recipient,
                                    'pickupResponsible' => optional($guide->reception)->pickupResponsible,
                                    'payResponsible' => optional($guide->reception)->payResponsible,
                                    'seller' => optional($guide->reception)->seller,
                                    'pointDestination' => optional($guide->reception)->pointDestination,
                                    'pointSender' => optional($guide->reception)->pointSender,
                                ],
                            ];
                        })->whenEmpty(function () {
                            return collect([null]); // Devolver null si no hay guías
                        }),
                    ],
                ];
            }));
        });

        // Calcular el total de programaciones
        $totalProgramations = $programmingCollection->count();

        // Preparar la respuesta con los datos paginados
        return response()->json([
            'total' => $totalProgramations, // Total de programaciones
            'data' => $programmingCollection->values(), // Devolver las programaciones
            'current_page' => $history->currentPage(),
            'last_page' => $history->lastPage(),
            'per_page' => $history->perPage(),
            'first_page_url' => $history->url(1),
            'from' => $history->firstItem(),
            'next_page_url' => $history->nextPageUrl(),
            'path' => $history->path(),
            'prev_page_url' => $history->previousPageUrl(),
            'to' => $history->lastItem(),
        ]);
    }

}
