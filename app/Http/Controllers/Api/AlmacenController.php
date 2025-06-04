<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlmacenRequest\IndexAlmacenRequest;
use App\Http\Requests\AlmacenRequest\StoreAlmacenRequest;
use App\Http\Requests\AlmacenRequest\UpdateAlmacenRequest;
use App\Http\Resources\AlmacenResource;
use App\Models\Almacen;
use App\Models\Seccion;
use App\Services\AlmacenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    protected $almacenService;

    public function __construct(AlmacenService $AlmacenService)
    {
        $this->almacenService = $AlmacenService;
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/almacen",
 *     summary="Obtener información de Almacens con filtros y ordenamiento",
 *     tags={"Almacen"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="address", in="query", description="Filtrar por address", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="status", in="query", description="Filtrar por status", required=false, @OA\Schema(type="string")),
 *
 *     @OA\Response(response=200, description="Lista de Almacens", @OA\JsonContent(ref="#/components/schemas/Almacen")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexAlmacenRequest $request)
    {

        return $this->getFilteredResults(
            Almacen::class,
            $request,
            Almacen::filters,
            Almacen::sorts,
            AlmacenResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/almacen/{id}",
 *     summary="Obtener detalles de un Almacen por ID",
 *     tags={"Almacen"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Almacen", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Almacen encontrado", @OA\JsonContent(ref="#/components/schemas/Almacen")),
 *     @OA\Response(response=404, description="Almacen no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Almacen no encontrada")))
 * )
 */

    public function show($id)
    {

        $almacen = $this->almacenService->getAlmacenById($id);

        if (! $almacen) {
            return response()->json([
                'error' => 'Almacen No Encontrada',
            ], 404);
        }

        return new AlmacenResource($almacen);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/almacen",
 *     summary="Crear Almacen",
 *     tags={"Almacen"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/AlmacenRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Almacen creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Almacen")
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

    public function store(StoreAlmacenRequest $request)
    {
        $almacen = $this->almacenService->createAlmacen($request->validated());
        return new AlmacenResource($almacen);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/almacen/{id}",
 *     summary="Actualizar un Almacen",
 *     tags={"Almacen"},
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
 *             @OA\Schema(ref="#/components/schemas/AlmacenRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Almacen actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Almacen")
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
 *         description="Almacen no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Almacen no encontrada")
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

    public function update(UpdateAlmacenRequest $request, $id)
    {

        $validatedData = $request->validated();

        $almacen = $this->almacenService->getAlmacenById($id);
        if (! $almacen) {
            return response()->json([
                'error' => 'Almacen No Encontrado',
            ], 404);
        }

        $updatedcarga = $this->almacenService->updateAlmacen($almacen, $validatedData);
        return new AlmacenResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/almacen/{id}",
 *     summary="Eliminar un Almacenpor ID",
 *     tags={"Almacen"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Almacen eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Almacen eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Almacen no encontrada"))),

 * )
 */

    public function destroy($id)
    {

        $almacen = $this->almacenService->getAlmacenById($id);

        if (! $almacen) {
            return response()->json([
                'error' => 'Almacen No Encontrada.',
            ], 404);
        }
        $almacen = $this->almacenService->destroyById($id);

        if ($almacen->seccion()->exists()) {
            return response()->json([
                'error' => 'El Almacén tiene asociado secciones.',
            ], 422);
        }

        return response()->json([
            'message' => 'Almacen eliminado exitosamente',
        ], 200);
    }



    public function report(Request $request, $id = 0)
    {
        $almacen = Almacen::with('products')->find($id);
        if (! $almacen) {
            abort(404);
        }

        // Agrupar productos por sección
        $productosAgrupados = $almacen->products->groupBy(function ($product) {
            return $product->pivot->seccion_id;
        });

        // Traer nombres de secciones
        $secciones = Seccion::whereIn('id', $productosAgrupados->keys())->pluck('name', 'id');

        $pdf = Pdf::loadView('almacen-report', [
            'almacen' => $almacen,
            'productosPorSeccion' => $productosAgrupados,
            'secciones' => $secciones,
        ]);

        // Cambié stream() por download() para permitir la descarga del archivo PDF
        return $pdf->download($almacen->numero . '-' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }



}
