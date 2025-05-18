<?php

namespace App\Http\Controllers\Compra;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompraOrderDetailRequest\StoreCompraOrderDetailRequest;
use App\Http\Requests\CompraOrderDetailRequest\UpdateCompraOrderDetailRequest;
use App\Http\Requests\CompraOrderRequest\IndexCompraOrderRequest;
use App\Http\Resources\CompraOrderDetailResource;
use App\Models\CompraOrderDetail;
use App\Services\CompraOrderDetailService;
use Illuminate\Http\Request;

class CompraOrderDetailController extends Controller
{
     protected $compraOrderDetailService;

    public function __construct(CompraOrderDetailService $OrderService)
    {
        $this->compraOrderDetailService = $OrderService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/compraorderdetail",
 *     summary="Obtener información de Orders con filtros y ordenamiento",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Orders", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexCompraOrderRequest $request)
    {
        return $this->getFilteredResults(
            CompraOrderDetail::class,
            $request,
            CompraOrderDetail::filters,
            CompraOrderDetail::sorts,
            CompraOrderDetailResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/compraorderdetail/{id}",
 *     summary="Obtener detalles de un Detalle Orden Compra a por ID",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Order", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Detalle Orden Compra a encontrado", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=404, description="Detalle Orden Compra a no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Detalle Orden Compra a no encontrada")))
 * )
 */

    public function show($id)
    {
        $compraOrderDetail = $this->compraOrderDetailService->getCompraOrderDetailById($id);
        if (! $compraOrderDetail) {
            return response()->json([
                'error' => 'Detalle Orden Compra No Encontrada',
            ], 404);
        }
        return new CompraOrderDetailResource($compraOrderDetail);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/compraorderdetail",
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
 *         description="Detalle Orden Compra a creada exitosamente",
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

    public function store(StoreCompraOrderDetailRequest $request)
    {
        $data           = $request->validated();
        $compraOrderDetail      = $this->compraOrderDetailService->createCompraOrderDetail($data);
        return new CompraOrderDetailResource($compraOrderDetail);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/compraorderdetail/{id}",
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
 *         description="Detalle Orden Compra a actualizada exitosamente",
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
 *         description="Detalle Orden Compra a no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Detalle Orden Compra a no encontrada")
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

    public function update(UpdateCompraOrderDetailRequest $request, $id)
    {
        $validatedData = $request->validated();
        $compraOrderDetail     = $this->compraOrderDetailService->getCompraOrderDetailById($id);
        if (! $compraOrderDetail) {
            return response()->json([
                'error' => 'Detalle Orden Compra No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->compraOrderDetailService->updateCompraOrderDetail($compraOrderDetail, $validatedData);
        return new CompraOrderDetailResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/compraorderdetail/{id}",
 *     summary="Eliminar un Orderpor ID",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Detalle Orden Compra a eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Detalle Orden Compra a eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Detalle Orden Compra a no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $compraOrderDetail = $this->compraOrderDetailService->getCompraOrderDetailById($id);
        if (! $compraOrderDetail) {
            return response()->json([
                'error' => 'Detalle Orden Compra No Encontrada.',
            ], 404);
        }



        $compraOrderDetail = $this->compraOrderDetailService->destroyById($id);
        return response()->json([
            'message' => 'Detalle Orden Compra eliminado exitosamente',
        ], 200);
    }


}
