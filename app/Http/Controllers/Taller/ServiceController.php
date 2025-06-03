<?php
namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\IndexServiceRequest;
use App\Http\Requests\ServiceRequest\StoreServiceRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $ServiceService)
    {
        $this->serviceService = $ServiceService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/service",
 *     summary="Obtener información de Services con filtros y ordenamiento",
 *     tags={"Service"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Services", @OA\JsonContent(ref="#/components/schemas/Service")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexServiceRequest $request)
    {
        return $this->getFilteredResults(
            Service::class,
            $request,
            Service::filters,
            Service::sorts,
            ServiceResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/service/{id}",
 *     summary="Obtener detalles de un Service por ID",
 *     tags={"Service"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Service", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Service encontrado", @OA\JsonContent(ref="#/components/schemas/Service")),
 *     @OA\Response(response=404, description="Service no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Service no encontrada")))
 * )
 */

    public function show($id)
    {
        $service = $this->serviceService->getServiceById($id);
        if (! $service) {
            return response()->json([
                'error' => 'Servicio No Encontrado',
            ], 404);
        }
        return new ServiceResource($service);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/service",
 *     summary="Crear Service",
 *     tags={"Service"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/ServiceRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Service")
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

    public function store(StoreServiceRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'ACTIVO';
        $service        = $this->serviceService->createService($data);
        return new ServiceResource($service);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/service/{id}",
 *     summary="Actualizar un Service",
 *     tags={"Service"},
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
 *             @OA\Schema(ref="#/components/schemas/ServiceRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Service actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Service")
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
 *         description="Service no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Service no encontrada")
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

    public function update(UpdateServiceRequest $request, $id)
    {
        $validatedData = $request->validated();
        $service       = $this->serviceService->getServiceById($id);
        if (! $service) {
            return response()->json([
                'error' => 'Servicio No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->serviceService->updateService($service, $validatedData);
        return new ServiceResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/service/{id}",
 *     summary="Eliminar un Servicepor ID",
 *     tags={"Service"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Service eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Service eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Service no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $service = $this->serviceService->getServiceById($id);
        if (! $service) {
            return response()->json([
                'error' => 'Servicio No Encontrado.',
            ], 404);
        }
        if ($service->details_maintenance()->exists()) {
            return response()->json([
                'error' => 'No se puede eliminar el servicio porque tiene detalles de mantenimiento relacionados.',
            ], 409); // 409 Conflict
        }

        $service = $this->serviceService->destroyById($id);
        return response()->json([
            'message' => 'Servicio eliminado exitosamente',
        ], 200);
    }
}
