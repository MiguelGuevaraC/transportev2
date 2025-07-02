<?php

namespace App\Http\Controllers\Compra;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompraMovimentRequest\IndexCompraMovimentRequest;
use App\Http\Requests\CompraMovimentRequest\StoreCompraMovimentRequest;
use App\Http\Requests\CompraMovimentRequest\UpdateCompraMovimentRequest;
use App\Http\Resources\CompraMovimentResource;
use App\Models\CompraMoviment;
use App\Services\CompraMovimentService;
use Illuminate\Http\Request;

class CompraMovimentController extends Controller
{
     protected $compraOrderService;

    public function __construct(CompraMovimentService $OrderService)
    {
        $this->compraOrderService = $OrderService;
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/compraorder",
 *     summary="Obtener información de Orders con filtros y ordenamiento",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Orders", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexCompraMovimentRequest $request)
    {
        return $this->getFilteredResults(
            CompraMoviment::class,
            $request,
            CompraMoviment::filters,
            CompraMoviment::sorts,
            CompraMovimentResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/compraorder/{id}",
 *     summary="Obtener detalles de un Movimineto Compra a por ID",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Order", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Movimineto Compra a encontrado", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=404, description="Movimineto Compra a no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Movimineto Compra a no encontrada")))
 * )
 */

    public function show($id)
    {
        $compraOrder = $this->compraOrderService->getCompraMovimentById($id);
        if (! $compraOrder) {
            return response()->json([
                'error' => 'Movimineto Compra No Encontrada',
            ], 404);
        }
        return new CompraMovimentResource($compraOrder);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/compraorder",
 *     summary="Crear Order",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/OrderRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movimineto Compra a creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Order")
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

    public function store(StoreCompraMovimentRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'ACTIVO';
        $compraOrder      = $this->compraOrderService->createCompraMoviment($data);
        return new CompraMovimentResource($compraOrder);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/compraorder/{id}",
 *     summary="Actualizar un Order",
 *     tags={"Order"},
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
 *             @OA\Schema(ref="#/components/schemas/OrderRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movimineto Compra a actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Order")
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
 *         description="Movimineto Compra a no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Movimineto Compra a no encontrada")
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

    public function update(UpdateCompraMovimentRequest $request, $id)
    {
        $validatedData = $request->validated();
        $compraOrder     = $this->compraOrderService->getCompraMovimentById($id);
        if (! $compraOrder) {
            return response()->json([
                'error' => 'Movimineto Compra No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->compraOrderService->updateCompraMoviment($compraOrder, $validatedData);
        return new CompraMovimentResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/compraorder/{id}",
 *     summary="Eliminar un Orderpor ID",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Movimineto Compra a eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Movimineto Compra a eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Movimineto Compra a no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $compraOrder = $this->compraOrderService->getCompraMovimentById($id);
        if (! $compraOrder) {
            return response()->json([
                'error' => 'Movimineto Compra No Encontrada.',
            ], 404);
        }



        $compraOrder = $this->compraOrderService->destroyById($id);
        return response()->json([
            'message' => 'Movimineto Compra eliminado exitosamente',
        ], 200);
    }

}
