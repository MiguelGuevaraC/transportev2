<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/transportedev/public/api/document",
     *     summary="Retrieve all Documents",
     *     tags={"Document"},
     *     description="Fetches a list of all available Documents in the system.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of the list of Documents.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Document")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. The user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function actualizarEstadoDocumentos()
    {
        $hoy = Carbon::today('America/Lima');

        Document::where(function ($query) use ($hoy) {
            $query->where('status', 'Vigente')->whereDate('dueDate', '<', $hoy)
                ->orWhere(function ($subquery) use ($hoy) {
                    $subquery->where('status', 'Vencido')->whereDate('dueDate', $hoy);
                });
        })->update(['status' => DB::raw("CASE
            WHEN dueDate < '$hoy' THEN 'Vencido'
            WHEN dueDate = '$hoy' THEN 'Vigente'
        END")]);
    }

    public function index(Request $request)
    {
        $this->actualizarEstadoDocumentos();

                                                     // Parámetros de paginación
        $page     = $request->input('page', 1);      // Página por defecto es 1
        $per_page = $request->input('per_page', 10); // 10 elementos por defecto

                                                     // Parámetros de filtro
        $number     = $request->input('number');     // Número de documento
        $vehicle_id = $request->input('vehicle_id'); // ID de vehículo
        $startDate  = $request->input('startDate');  // Fecha de inicio (created_at >=)
        $endDate    = $request->input('endDate');    // Fecha de fin (created_at <=)
        $status     = $request->input('status');
        // Construir la consulta con filtros condicionales
        $query = Document::with(['vehicle'])->where('state', 1);

        // Filtrar por número de documento si está presente
        if (! empty($number)) {
            $query->where('number', 'LIKE', "%$number%");
        }
        if (! empty($status)) {
            $query->where('status', 'LIKE', "%$status%");
        }
        // Filtrar por ID de vehículo si está presente
        if (! empty($vehicle_id)) {
            $query->where('vehicle_id', $vehicle_id);
        }

        // Filtrar por fechas de creación (rangos)
        if (! empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $query->where('created_at', '<=', $endDate);
        }

        // Obtener la lista paginada con los filtros aplicados
        $documents = $query->orderBy('id', 'desc')
            ->paginate($per_page, ['*'], 'page', $page);

        // Estructura de la respuesta con información de paginación
        return response()->json([
            'total'          => $documents->total(),
            'data'           => $documents->items(),
            'current_page'   => $documents->currentPage(),
            'last_page'      => $documents->lastPage(),
            'per_page'       => $documents->perPage(),
            'pagination'     => $per_page,
            'first_page_url' => $documents->url(1),
            'from'           => $documents->firstItem(),
            'next_page_url'  => $documents->nextPageUrl(),
            'path'           => $documents->path(),
            'prev_page_url'  => $documents->previousPageUrl(),
            'to'             => $documents->lastItem(),
        ], 200);
    }

    public function indexReport()
    {

        $this->actualizarEstadoDocumentos();

        // Obtener documentos cuya fecha de vencimiento esté entre hoy y los próximos 3 días
        $documentsExpiringSoon = Document::whereBetween
        ('dueDate', [now(), now()->addDays(3)])
            ->get();

        foreach ($documentsExpiringSoon as $document) {
            // Calcular la diferencia en días entre hoy y la fecha de vencimiento
            $daysUntilDue = now()->diffInDays($document->dueDate, false);

                               // Determinar la prioridad según los días restantes
            $priority = 'low'; // Por defecto, bajo
            if ($daysUntilDue == 1) {
                $priority = 'medium'; // Prioridad media si queda 1 día
            } elseif ($daysUntilDue <= 0) {
                $priority = 'high'; // Prioridad alta si es hoy o ya vencido
            }

            // Verificar si ya existe una notificación para este documento y fecha
            $notificationExists = Notification::where('record_id', $document->id)
                ->where('table', 'documents')
                ->whereDate('created_at', now()) // Verificar si se creó hoy
                ->exists();

            if (! $notificationExists) {

                // Crear una nueva notificación si no existe
                Notification::create([
                    'record_id'  => $document->id,
                    'title'      => "Vencimiento próximo del documento",
                    'message'    => "El documento con número {$document->number} vencerá en {$daysUntilDue} días.",
                    'type'       => 'warning',
                    'table'      => 'documents',
                    'priority'   => $priority,
                    'vehicle_id' => $document->vehicle_id,
                    'dueDate'    => $document->dueDate,
                    'created_at' => now(),
                ]);
            }
        }

        return response()->json([
            'mensaje' => 'listo',

        ], 200);
    }

    // public function index(Request $request)
    // {
    //     // Actualiza los documentos basados en la fecha de vencimiento
    //     Document::query()->update([
    //         'status' => DB::raw("CASE
    //         WHEN dueDate < NOW() THEN 'Vencido'
    //         ELSE 'Vigente'
    //     END"),
    //     ]);

    //     // Obtener los parámetros 'page' y 'per_page' desde la solicitud, con valores por defecto
    //     $page = $request->input('page', 1); // Página por defecto es 1 si no se pasa
    //     $per_page = $request->input('per_page', 10); // Por defecto 10 elementos por página

    //     // Obtiene la lista de documentos con la relación vehicle y paginación
    //     $documents = Document::with(['vehicle'])
    //         ->where('state', 1)
    //         ->orderBy('id', 'desc')
    //         ->paginate($per_page, ['*'], 'page', $page);

    //     // Estructura la respuesta
    //     return response()->json([

    //         'total' => $documents->total(),
    //         'data' => $documents->items(),
    //         'current_page' => $documents->currentPage(),
    //         'last_page' => $documents->lastPage(),
    //         'per_page' => $documents->perPage(),
    //         'pagination' => $per_page, // Campo adicional de tamaño de paginación
    //         'first_page_url' => $documents->url(1),
    //         'from' => $documents->firstItem(),
    //         'next_page_url' => $documents->nextPageUrl(),
    //         'path' => $documents->path(),
    //         'prev_page_url' => $documents->previousPageUrl(),
    //         'to' => $documents->lastItem(),

    //     ], 200);
    // }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/document",
 *     summary="Create a new Document",
 *     tags={"Document"},
 *     description="Stores a new Document in the database.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for creating a Document",
 *         @OA\JsonContent(
 *             required={"description", "vehicle_id"},
 *             @OA\Property(property="pathFile", type="string", example="/uploads/documents/doc1.pdf", description="File path of the document"),
 *             @OA\Property(property="description", type="string", example="Box Truck", description="Description of the Document"),
 *             @OA\Property(property="number", type="string", example="12345", description="Number of the Document"),
 *             @OA\Property(property="dueDate", type="string", format="date", example="2024-12-31", description="Due date of the Document"),
 *             @OA\Property(property="status", type="string", example="Vigente", description="Status of the Document"),
 *             @OA\Property(property="vehicle_id", type="integer", example=1, description="ID of the associated Vehicle")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document created successfully",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/Document"
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Some fields are required.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized. The user is not authenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [

            'description' => 'required|string|max:255',
            'number'      => 'nullable|string',
            'type'        => 'nullable|string',
            'dueDate'     => 'nullable|date',
            'vehicle_id'  => 'required|integer|exists:vehicles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = $request->only([
            'type',
            'description',
            'number',
            'dueDate',
            'vehicle_id',
        ]);

        $object = Document::create($data);

        $expiredDocument = Document::where('type', $object->type)
            ->where('vehicle_id', $object->vehicle_id)
                                           // ->where('status', 'Vencido') // Asegurarse de que esté marcado como vencido
            ->whereDate('dueDate', '<', now()) // Asegurarse de que la fecha de vencimiento sea anterior a la fecha actual
            ->first();

        if ($expiredDocument) {
            // Eliminar la notificación del documento vencido
            $not = Notification::where('vehicle_id', $expiredDocument->vehicle_id)
                ->where('table', 'documents')
                ->where('record_id', $expiredDocument->id) // Coincidir con el document_id del documento vencido
                ->delete();
            return response()->json($not, 200);
        }

        $object = Document::with(['vehicle'])->find($object->id);

        return response()->json($object, 200);
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/document/{id}",
 *     summary="Get a Document by ID",
 *     tags={"Document"},
 *     description="Retrieve a Document by its ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Document to retrieve",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document found",
 *         @OA\JsonContent(
 *             type="object",
 *             ref="#/components/schemas/Document"
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Document not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Document not found")
 *         )
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
    public function show($id)
    {
        $document = Document::with(['vehicle'])->find($id);

        if (! $document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        $this->actualizarEstadoDocumentos();
        return response()->json($document, 200);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/document/{id}",
 *     summary="Update an existing Document",
 *     tags={"Document"},
 *     description="Update an existing Document",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Document to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Updated Document data",
 *         @OA\JsonContent(
 *             @OA\Property(property="description", type="string", example="Updated Description", description="Description of the Document"),
 *             @OA\Property(property="vehicle_id", type="integer", example=1, description="ID of the associated Vehicle"),
 *             @OA\Property(property="state", type="boolean", example=true, description="State of the Document"),
 *             @OA\Property(property="pathFile", type="string", example="/documents/file.pdf", description="Path of the related file"),
 *             @OA\Property(property="number", type="string", example="123456", description="Document number"),
 *             @OA\Property(property="dueDate", type="string", format="date", example="2024-08-01", description="Due date of the Document"),
 *             @OA\Property(property="status", type="string", example="Vencido", description="Status of the Document")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document updated successfully",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/Document"
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
 *     @OA\Response(
 *         response=404,
 *         description="Document not found.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Document not found.")
 *         )
 *     )
 * )
 */
    public function update(Request $request, $id)
    {
        $document = Document::with(['vehicle'])->find($id);
        if (! $document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [
            'description' => 'required|string|max:255',
            'vehicle_id'  => 'required|integer|exists:vehicles,id',
            'type'        => 'required',
            'pathFile'    => 'nullable|string|max:255',
            'number'      => 'nullable|string',
            'dueDate'     => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = $request->only([
            'description',
            'vehicle_id',
            'type',
            'pathFile',
            'number',
            'dueDate',
        ]);

        $document->update($data);

        $this->actualizarEstadoDocumentos();
        $document = Document::with(['vehicle'])->find($document->id);
        Bitacora::create([
            'user_id'     => Auth::id(),    // ID del usuario que realiza la acción
            'record_id'   => $document->id, // El ID del usuario afectado
            'action'      => 'PUT',         // Acción realizada
            'table_name'  => 'documents',   // Tabla afectada
            'data'        => json_encode($document),
            'description' => 'Actualizar Documentos', // Descripción de la acción
            'ip_address'  => $request->ip(),          // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),   // Información sobre el navegador/dispositivo
        ]);

        return response()->json($document, 200);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/document/{id}",
 *     summary="Create or Update a Document",
 *     tags={"Document"},
 *     description="Stores a new Document in the database or updates an existing one if the ID is provided.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=false,
 *         description="ID of the Document to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for creating or updating a Document, including a file upload",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"description", "vehicle_id"},
 *                 @OA\Property(property="pathFile", type="string", format="binary", description="File to be uploaded"),
 *                 @OA\Property(property="description", type="string", example="Box Truck", description="Description of the Document"),
 *                 @OA\Property(property="number", type="string", example="12345", description="Number of the Document"),
 *                 @OA\Property(property="dueDate", type="string", format="date", example="2024-12-31", description="Due date of the Document"),
 *                 @OA\Property(property="status", type="string", example="Vigente", description="Status of the Document"),
 *                 @OA\Property(property="vehicle_id", type="integer", example=1, description="ID of the associated Vehicle")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document created or updated successfully",
 *         @OA\JsonContent(
 *             ref="#/components/schemas/Document"
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Some fields are required.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized. The user is not authenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

public function createOrUpdate(Request $request, $id = null)
{
    try {
        $validator = validator()->make($request->all(), [
            'description' => 'required|string|max:255',
            'number'      => 'required|string',
            'dueDate'     => 'required|date',
            'vehicle_id'  => 'required|integer|exists:vehicles,id',
            'pathFile'    => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $document = $id != 'null' ? Document::find($id) : new Document();

        if (! $document) {
            return response()->json(['error' => 'Document not found.'], 404);
        }

        if ($request->hasFile('pathFile')) {
            if ($document->pathFile) {
                $filePath = str_replace('/storage', 'public', $document->pathFile);
                Storage::delete($filePath);
            }


            $photoFile = $request->file('pathFile');

            $vehicleDirectory = 'public/document/' . $request->input('vehicle_id');
            $filePath         = $photoFile->store($vehicleDirectory);

            $photoUrl = Storage::url($filePath);

            $document->pathFile = $photoUrl;
        }

        $document->description = $request->input('description');
        $document->number      = $request->input('number');
        $document->dueDate     = $request->input('dueDate');
        $document->type        = $request->input('type');
        $document->vehicle_id  = $request->input('vehicle_id');

        $currentDate = now();
        $dueDate     = $request->input('dueDate');

        if ($dueDate < $currentDate) {
            $document->status = 'Vencido';
        } else {
            $document->status = 'Vigente';

            $expiredDocument = Document::where('type', $document->type)
                ->where('vehicle_id', $document->vehicle_id)
                ->whereDate('dueDate', '<', now())
                ->first();

            if ($expiredDocument) {
                Notification::where('vehicle_id', $expiredDocument->vehicle_id)
                    ->where('table', 'documents')
                    ->where('record_id', $expiredDocument->id)
                    ->delete();
            }
        }

        $document->save();
        $this->actualizarEstadoDocumentos();

        $document = Document::with(['vehicle'])->find($document->id);

        Bitacora::create([
            'user_id'     => Auth::id(),
            'record_id'   => $document->id,
            'action'      => 'POST/PUT',
            'table_name'  => 'documents',
            'data'        => json_encode($document),
            'description' => 'Guardo Documentos',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return response()->json($document, 200);

    } catch (\Exception $e) {
        Log::error('Error en createOrUpdate DocumentController: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        return response()->json(['error' => 'Unexpected error, please try again later.'], 500);
    }
}


/**
 * @OA\Delete(
 *     path="/transportedev/public/api/document/{id}",
 *     summary="Delete a Document",
 *     tags={"Document"},
 *     description="Mark a Document as deleted by setting its state to inactive",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the Document to delete",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document marked as deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Document deleted successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Document not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Document not found.")
 *         )
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

    public function destroy($id)
    {
        $document = Document::with(['vehicle'])->find($id);
        if (! $document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $document->state = 0; // Marcar el documento como eliminado al establecer el estado a 0 (inactivo)
        $document->save();
        $this->actualizarEstadoDocumentos();

        return response()->json(['message' => 'Document deleted successfully'], 200);
    }

}
