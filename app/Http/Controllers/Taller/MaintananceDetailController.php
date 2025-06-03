<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceDetailRequest\IndexMaintenanceDetailRequest;
use App\Http\Requests\MaintenanceDetailRequest\StoreMaintenanceDetailRequest;
use App\Http\Requests\MaintenanceDetailRequest\UpdateMaintenanceDetailRequest;
use App\Http\Resources\MaintenanceDetailResource;
use App\Models\MaintenanceDetail;
use App\Services\MaintenanceDetailService;
use Illuminate\Http\Request;

class MaintananceDetailController extends Controller
{
    protected $maintenancedetailService;

    public function __construct(MaintenanceDetailService $MaintenanceDetailService)
    {
        $this->maintenancedetailService = $MaintenanceDetailService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/maintenancedetail",
 *     summary="Obtener información de MaintenanceDetails con filtros y ordenamiento",
 *     tags={"MaintenanceDetail"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de MaintenanceDetails", @OA\JsonContent(ref="#/components/schemas/MaintenanceDetail")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexMaintenanceDetailRequest $request)
    {
        return $this->getFilteredResults(
            MaintenanceDetail::class,
            $request,
            MaintenanceDetail::filters,
            MaintenanceDetail::sorts,
            MaintenanceDetailResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/maintenancedetail/{id}",
 *     summary="Obtener detalles de un MaintenanceDetail por ID",
 *     tags={"MaintenanceDetail"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del MaintenanceDetail", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="MaintenanceDetail encontrado", @OA\JsonContent(ref="#/components/schemas/MaintenanceDetail")),
 *     @OA\Response(response=404, description="MaintenanceDetail no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="MaintenanceDetail no encontrada")))
 * )
 */

    public function show($id)
    {
        $maintenancedetail = $this->maintenancedetailService->getMaintenanceDetailById($id);
        if (! $maintenancedetail) {
            return response()->json([
                'error' => 'Detalle Mantenimiento No Encontrado',
            ], 404);
        }


        return new MaintenanceDetailResource($maintenancedetail);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/maintenancedetail",
 *     summary="Crear MaintenanceDetail",
 *     tags={"MaintenanceDetail"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/MaintenanceDetailRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="MaintenanceDetail creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceDetail")
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

    public function store(StoreMaintenanceDetailRequest $request)
    {
        $data= $request->validated();
        $data['status']='ACTIVO';
        $maintenancedetail = $this->maintenancedetailService->createMaintenanceDetail($data);
        return new MaintenanceDetailResource($maintenancedetail);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/maintenancedetail/{id}",
 *     summary="Actualizar un MaintenanceDetail",
 *     tags={"MaintenanceDetail"},
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
 *             @OA\Schema(ref="#/components/schemas/MaintenanceDetailRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="MaintenanceDetail actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/MaintenanceDetail")
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
 *         description="MaintenanceDetail no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="MaintenanceDetail no encontrada")
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

    public function update(UpdateMaintenanceDetailRequest $request, $id)
    {
        $validatedData = $request->validated();
        $maintenancedetail = $this->maintenancedetailService->getMaintenanceDetailById($id);
        if (! $maintenancedetail) {
            return response()->json([
                'error' => 'Detalle Mantenimiento No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->maintenancedetailService->updateMaintenanceDetail($maintenancedetail, $validatedData);
        return new MaintenanceDetailResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/maintenancedetail/{id}",
 *     summary="Eliminar un MaintenanceDetailpor ID",
 *     tags={"MaintenanceDetail"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="MaintenanceDetail eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="MaintenanceDetail eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="MaintenanceDetail no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $maintenancedetail = $this->maintenancedetailService->getMaintenanceDetailById($id);
        if (! $maintenancedetail) {
            return response()->json([
                'error' => 'Detalle Mantenimiento No Encontrado.',
            ], 404);
        }
        $maintenancedetail = $this->maintenancedetailService->destroyById($id);
        return response()->json([
            'message' => 'Detalle Mantenimiento eliminado exitosamente',
        ], 200);
    }
}
