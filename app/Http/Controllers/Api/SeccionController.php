<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeccionRequest\IndexSeccionRequest;
use App\Http\Requests\SeccionRequest\StoreSeccionRequest;
use App\Http\Requests\SeccionRequest\UpdateSeccionRequest;
use App\Http\Resources\SeccionResource;
use App\Models\Seccion;
use App\Services\SeccionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    protected $seccionService;

    public function __construct(SeccionService $SeccionService)
    {
        $this->seccionService = $SeccionService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/seccion",
 *     summary="Obtener información de Seccions con filtros y ordenamiento",
 *     tags={"Seccion"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="address", in="query", description="Filtrar por address", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="status", in="query", description="Filtrar por status", required=false, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="Lista de Seccions", @OA\JsonContent(ref="#/components/schemas/Seccion")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexSeccionRequest $request)
    {

        return $this->getFilteredResults(
            Seccion::class,
            $request,
            Seccion::filters,
            Seccion::sorts,
            SeccionResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/seccion/{id}",
 *     summary="Obtener detalles de un Seccion por ID",
 *     tags={"Seccion"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Seccion", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Seccion encontrado", @OA\JsonContent(ref="#/components/schemas/Seccion")),
 *     @OA\Response(response=404, description="Seccion no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Seccion no encontrada")))
 * )
 */

    public function show($id)
    {

        $seccion = $this->seccionService->getSeccionById($id);

        if (! $seccion) {
            return response()->json([
                'error' => 'Seccion No Encontrada',
            ], 404);
        }

        return new SeccionResource($seccion);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/seccion",
 *     summary="Crear Seccion",
 *     tags={"Seccion"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/SeccionRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Seccion creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Seccion")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error de validación")
 *         )
 *     )
 * )
 */

    public function store(StoreSeccionRequest $request)
    {
        $seccion = $this->seccionService->createSeccion($request->validated());
        return new SeccionResource($seccion);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/seccion/{id}",
 *     summary="Actualizar un Seccion",
 *     tags={"Seccion"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/SeccionRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Seccion actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Seccion")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error de validación")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Seccion no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Seccion no encontrada")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error interno del servidor")
 *         )
 *     )
 * )
 */

    public function update(UpdateSeccionRequest $request, $id)
    {

        $validatedData = $request->validated();

        $seccion = $this->seccionService->getSeccionById($id);
        if (! $seccion) {
            return response()->json([
                'error' => 'Seccion No Encontrado',
            ], 404);
        }

        $updatedcarga = $this->seccionService->updateSeccion($seccion, $validatedData);
        return new SeccionResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/seccion/{id}",
 *     summary="Eliminar un Seccionpor ID",
 *     tags={"Seccion"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Seccion eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Seccion eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Seccion no encontrada"))),

 * )
 */

    public function destroy($id)
    {

        $seccion = $this->seccionService->getSeccionById($id);

        if (! $seccion) {
            return response()->json([
                'error' => 'Seccion No Encontrada.',
            ], 404);
        }
        $seccion = $this->seccionService->destroyById($id);

        return response()->json([
            'message' => 'Seccion eliminado exitosamente',
        ], 200);
    }

    public function report(Request $request, $seccion_id)
    {
        $seccion = Seccion::with(['products' => function ($query) {
            $query->wherePivot('stock', '>', 0); // Solo productos con stock
        }])->find($seccion_id);
    
        if (! $seccion) {
            abort(404);
        }
    
        $pdf = Pdf::loadView('seccion-report', [
            'seccion'   => $seccion,
            'productos' => $seccion->products,
        ]);
    
        // Modificar stream() por download() para permitir la descarga del archivo PDF
        return $pdf->download('seccion-' . $seccion->name . '-' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
    

}
