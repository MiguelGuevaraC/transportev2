<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\RepuestoRequest\IndexRepuestoRequest;
use App\Http\Requests\RepuestoRequest\StoreRepuestoRequest;
use App\Http\Requests\RepuestoRequest\UpdateRepuestoRequest;
use App\Http\Resources\RepuestoResource;
use App\Models\Repuesto;
use App\Services\RepuestoService;
use Illuminate\Http\Request;

class RepuestoController extends Controller
{
    protected $repuestoService;

    public function __construct(RepuestoService $RepuestoService)
    {
        $this->repuestoService = $RepuestoService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/repuesto",
 *     summary="Obtener información de Repuestos con filtros y ordenamiento",
 *     tags={"Repuesto"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Repuestos", @OA\JsonContent(ref="#/components/schemas/Repuesto")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexRepuestoRequest $request)
    {
        return $this->getFilteredResults(
            Repuesto::class,
            $request,
            Repuesto::filters,
            Repuesto::sorts,
            RepuestoResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/repuesto/{id}",
 *     summary="Obtener detalles de un Repuesto por ID",
 *     tags={"Repuesto"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Repuesto", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Repuesto encontrado", @OA\JsonContent(ref="#/components/schemas/Repuesto")),
 *     @OA\Response(response=404, description="Repuesto no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Repuesto no encontrada")))
 * )
 */

    public function show($id)
    {
        $repuesto = $this->repuestoService->getRepuestoById($id);
        if (! $repuesto) {
            return response()->json([
                'error' => 'Repuesto No Encontrada',
            ], 404);
        }
        return new RepuestoResource($repuesto);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/repuesto",
 *     summary="Crear Repuesto",
 *     tags={"Repuesto"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/RepuestoRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Repuesto creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Repuesto")
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

    public function store(StoreRepuestoRequest $request)
    {
        $data= $request->validated();
        $data['status']='ACTIVO';
        $repuesto = $this->repuestoService->createRepuesto($data);
        return new RepuestoResource($repuesto);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/repuesto/{id}",
 *     summary="Actualizar un Repuesto",
 *     tags={"Repuesto"},
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
 *             @OA\Schema(ref="#/components/schemas/RepuestoRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Repuesto actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Repuesto")
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
 *         description="Repuesto no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Repuesto no encontrada")
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

    public function update(UpdateRepuestoRequest $request, $id)
    {
        $validatedData = $request->validated();
        $repuesto = $this->repuestoService->getRepuestoById($id);
        if (! $repuesto) {
            return response()->json([
                'error' => 'Repuesto No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->repuestoService->updateRepuesto($repuesto, $validatedData);
        return new RepuestoResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/repuesto/{id}",
 *     summary="Eliminar un Repuestopor ID",
 *     tags={"Repuesto"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Repuesto eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Repuesto eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Repuesto no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $repuesto = $this->repuestoService->getRepuestoById($id);
        if (! $repuesto) {
            return response()->json([
                'error' => 'Repuesto No Encontrado.',
            ], 404);
        }
        $repuesto = $this->repuestoService->destroyById($id);
        return response()->json([
            'message' => 'Repuesto eliminado exitosamente',
        ], 200);
    }
}
