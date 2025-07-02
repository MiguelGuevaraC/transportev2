<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\TireOperationRequest\IndexTireOperationRequest;
use App\Http\Requests\TireOperationRequest\StoreTireOperationRequest;
use App\Http\Requests\TireOperationRequest\UpdateTireOperationRequest;
use App\Http\Resources\TireOperationResource;
use App\Models\TireOperation;
use App\Services\TireoperationService;
use Illuminate\Http\Request;

class TireOperationController extends Controller
{
     protected $operationService;

    public function __construct(TireoperationService $TireoperationService)
    {
        $this->operationService = $TireoperationService;
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/tire_operation",
 *     summary="Obtener información de TireOperations con filtros y ordenamiento",
 *     tags={"TireOperation"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de TireOperations", @OA\JsonContent(ref="#/components/schemas/TireOperation")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexTireOperationRequest $request)
    {
        return $this->getFilteredResults(
            TireOperation::class,
            $request,
            TireOperation::filters,
            TireOperation::sorts,
            TireOperationResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/tire_operation/{id}",
 *     summary="Obtener detalles de un TireOperation por ID",
 *     tags={"TireOperation"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del TireOperation", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="TireOperation encontrado", @OA\JsonContent(ref="#/components/schemas/TireOperation")),
 *     @OA\Response(response=404, description="TireOperation no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="TireOperation no encontrada")))
 * )
 */

    public function show($id)
    {
        $operation_tire = $this->operationService->getTireOperationById($id);
        if (! $operation_tire) {
            return response()->json([
                'error' => 'Operación del Neumático No Encontrada',
            ], 404);
        }
        return new TireOperationResource($operation_tire);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/tire_operation",
 *     summary="Crear TireOperation",
 *     tags={"TireOperation"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/TireOperationRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="TireOperation creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/TireOperation")
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

    public function store(StoreTireOperationRequest $request)
    {
        $data= $request->validated();
        $operation_tire = $this->operationService->createTireOperation($data);
        return new TireOperationResource($operation_tire);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/tire_operation/{id}",
 *     summary="Actualizar un TireOperation",
 *     tags={"TireOperation"},
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
 *             @OA\Schema(ref="#/components/schemas/TireOperationRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="TireOperation actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/TireOperation")
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
 *         description="TireOperation no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="TireOperation no encontrada")
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

    public function update(UpdateTireOperationRequest $request, $id)
    {
        $validatedData = $request->validated();
        $operation_tire = $this->operationService->getTireOperationById($id);
        if (! $operation_tire) {
            return response()->json([
                'error' => 'Operación del Neumático No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->operationService->updateTireOperation($operation_tire, $validatedData);
        return new TireOperationResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/tire_operation/{id}",
 *     summary="Eliminar un TireOperationpor ID",
 *     tags={"TireOperation"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Operación del Neumático eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Operación del Neumático eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="TireOperation no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $operation_tire = $this->operationService->getTireOperationById($id);
        if (! $operation_tire) {
            return response()->json([
                'error' => 'Operación del Neumático No Encontrada.',
            ], 404);
        }
        $operation_tire = $this->operationService->destroyById($id);
        return response()->json([
            'message' => 'Operación del Neumático eliminado exitosamente',
        ], 200);
    }

}
