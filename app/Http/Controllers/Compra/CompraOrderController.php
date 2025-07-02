<?php
namespace App\Http\Controllers\Compra;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompraOrderRequest\IndexCompraOrderRequest;
use App\Http\Requests\CompraOrderRequest\StoreCompraOrderRequest;
use App\Http\Requests\CompraOrderRequest\UpdateCompraOrderRequest;
use App\Http\Resources\CompraOrderResource;
use App\Models\CompraOrder;
use App\Services\CompraOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CompraOrderController extends Controller
{
    protected $compraOrderService;

    public function __construct(CompraOrderService $OrderService)
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

    public function index(IndexCompraOrderRequest $request)
    {
        return $this->getFilteredResults(
            CompraOrder::class,
            $request,
            CompraOrder::filters,
            CompraOrder::sorts,
            CompraOrderResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/compraorder/{id}",
 *     summary="Obtener detalles de un Orden Compra a por ID",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Order", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Orden Compra a encontrado", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=404, description="Orden Compra a no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Orden Compra a no encontrada")))
 * )
 */

    public function show($id)
    {
        $compraOrder = $this->compraOrderService->getCompraOrderById($id);
        if (! $compraOrder) {
            return response()->json([
                'error' => 'Orden Compra No Encontrada',
            ], 404);
        }
        return new CompraOrderResource($compraOrder);
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
 *         description="Orden Compra a creada exitosamente",
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

    public function store(StoreCompraOrderRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'ACTIVO';
        $compraOrder    = $this->compraOrderService->createCompraOrder($data);
        return new CompraOrderResource($compraOrder);
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
 *         description="Orden Compra a actualizada exitosamente",
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
 *         description="Orden Compra a no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Orden Compra a no encontrada")
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

    public function update(UpdateCompraOrderRequest $request, $id)
    {
        $validatedData = $request->validated();
        $compraOrder   = $this->compraOrderService->getCompraOrderById($id);
        if (! $compraOrder) {
            return response()->json([
                'error' => 'Orden Compra No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->compraOrderService->updateCompraOrder($compraOrder, $validatedData);
        return new CompraOrderResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/compraorder/{id}",
 *     summary="Eliminar un Orderpor ID",
 *     tags={"Order"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Orden Compra a eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Orden Compra a eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Orden Compra a no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $compraOrder = $this->compraOrderService->getCompraOrderById($id);
        if (! $compraOrder) {
            return response()->json([
                'error' => 'Orden Compra No Encontrada.',
            ], 404);
        }

        $compraOrder = $this->compraOrderService->destroyById($id);
        return response()->json([
            'message' => 'Orden Compra eliminado exitosamente',
        ], 200);
    }

    public function reportpdf(Request $request, $id = 0)
    {
        $ordercompra = CompraOrder::find($id);
        if (! $ordercompra) {
            abort(404);
        }

        $pdf      = Pdf::loadView('ordercompra-report', ['data' => $ordercompra]);
        $canvas   = $pdf->getDomPDF()->get_canvas();
        $fileName = $ordercompra->number . '-' . now()->format('Y-m-d_H-i-s') . '.pdf';
        $fileName = str_replace(' ', '_', $fileName); // Reemplazar espacios con guiones bajos
        return $pdf->download($fileName);
    }

}
