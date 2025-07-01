<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\TireRequest\IndexTireRequest;
use App\Http\Requests\TireRequest\StoreTireRequest;
use App\Http\Requests\TireRequest\UpdateTireRequest;
use App\Http\Resources\TireResource;
use App\Models\Tire;
use App\Services\TireService;
use Illuminate\Http\Request;

class TireController extends Controller
{
     protected $tireService;

    public function __construct(TireService $TireService)
    {
        $this->tireService = $TireService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/tire",
 *     summary="Obtener información de Tires con filtros y ordenamiento",
 *     tags={"Tire"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Tires", @OA\JsonContent(ref="#/components/schemas/Tire")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexTireRequest $request)
    {
        return $this->getFilteredResults(
            Tire::class,
            $request,
            Tire::filters,
            Tire::sorts,
            TireResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/tire/{id}",
 *     summary="Obtener detalles de un Tire por ID",
 *     tags={"Tire"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Tire", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Tire encontrado", @OA\JsonContent(ref="#/components/schemas/Tire")),
 *     @OA\Response(response=404, description="Tire no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Tire no encontrada")))
 * )
 */

    public function show($id)
    {
        $tire = $this->tireService->getTireById($id);
        if (! $tire) {
            return response()->json([
                'error' => 'Neumático No Encontrada',
            ], 404);
        }
        return new TireResource($tire);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/tire",
 *     summary="Crear Tire",
 *     tags={"Tire"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/TireRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tire creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Tire")
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

    public function store(StoreTireRequest $request)
    {
        $data= $request->validated();
        $tire = $this->tireService->createTire($data);
        return new TireResource($tire);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/tire/{id}",
 *     summary="Actualizar un Tire",
 *     tags={"Tire"},
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
 *             @OA\Schema(ref="#/components/schemas/TireRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tire actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Tire")
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
 *         description="Tire no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Tire no encontrada")
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

    public function update(UpdateTireRequest $request, $id)
    {
        $validatedData = $request->validated();
        $tire = $this->tireService->getTireById($id);
        if (! $tire) {
            return response()->json([
                'error' => 'Neumático No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->tireService->updateTire($tire, $validatedData);
        return new TireResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/tire/{id}",
 *     summary="Eliminar un Tirepor ID",
 *     tags={"Tire"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Neumático eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Neumático eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Tire no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $tire = $this->tireService->getTireById($id);
        if (! $tire) {
            return response()->json([
                'error' => 'Neumático No Encontrada.',
            ], 404);
        }
        $tire = $this->tireService->destroyById($id);
        return response()->json([
            'message' => 'Neumático eliminado exitosamente',
        ], 200);
    }

}
