<?php
namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest\IndexMaintenanceRequest;
use App\Http\Requests\MaintenanceRequest\StoreMaintenanceRequest;
use App\Http\Requests\MaintenanceRequest\UpdateMaintenanceRequest;
use App\Http\Resources\MaintenanceResource;
use App\Models\Maintenance;
use App\Services\MaintenanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MaintananceController extends Controller
{
    protected $maintenanceService;

    public function __construct(MaintenanceService $MaintenanceService)
    {
        $this->maintenanceService = $MaintenanceService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/maintenance",
 *     summary="Obtener información de Maintenances con filtros y ordenamiento",
 *     tags={"Maintenance"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Maintenances", @OA\JsonContent(ref="#/components/schemas/Maintenance")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexMaintenanceRequest $request)
    {
        return $this->getFilteredResults(
            Maintenance::class,
            $request,
            Maintenance::filters,
            Maintenance::sorts,
            MaintenanceResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/maintenance/{id}",
 *     summary="Obtener detalles de un Maintenance por ID",
 *     tags={"Maintenance"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Maintenance", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Maintenance encontrado", @OA\JsonContent(ref="#/components/schemas/Maintenance")),
 *     @OA\Response(response=404, description="Maintenance no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Maintenance no encontrada")))
 * )
 */

    public function show($id)
    {
        $maintenance = $this->maintenanceService->getMaintenanceById($id);
        if (! $maintenance) {
            return response()->json([
                'error' => 'Mantenimiento No Encontrado',
            ], 404);
        }
        return new MaintenanceResource($maintenance);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/maintenance",
 *     summary="Crear Maintenance",
 *     tags={"Maintenance"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/MaintenanceRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Maintenance creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Maintenance")
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

    public function store(StoreMaintenanceRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'Pendiente';
        $maintenance    = $this->maintenanceService->createMaintenance($data);
        return new MaintenanceResource($maintenance);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/maintenance/{id}",
 *     summary="Actualizar un Maintenance",
 *     tags={"Maintenance"},
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
 *             @OA\Schema(ref="#/components/schemas/MaintenanceRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Maintenance actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Maintenance")
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
 *         description="Maintenance no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Maintenance no encontrada")
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

    public function update(UpdateMaintenanceRequest $request, $id)
    {
        $validatedData = $request->validated();
        $maintenance   = $this->maintenanceService->getMaintenanceById($id);
        if (! $maintenance) {
            return response()->json([
                'error' => 'Mantenimiento No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->maintenanceService->updateMaintenance($maintenance, $validatedData);
        return new MaintenanceResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/maintenance/{id}",
 *     summary="Eliminar un Maintenancepor ID",
 *     tags={"Maintenance"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Maintenance eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Maintenance eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Maintenance no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $maintenance = $this->maintenanceService->getMaintenanceById($id);
        if (! $maintenance) {
            return response()->json([
                'error' => 'Mantenimiento No Encontrado.',
            ], 404);
        }
        $maintenance = $this->maintenanceService->destroyById($id);
        return response()->json([
            'message' => 'Mantenimiento eliminado exitosamente',
        ], 200);
    }

public function report(Request $request, $id = 0)
{
    $mantenimiento = Maintenance::findOrFail($id);

    $logoPath = public_path('storage/img/logoTransportes.jpeg');
    $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath));

    $pdf = Pdf::loadView('mantenimiento-report', [
        'mantenimiento' => $mantenimiento, // ahora se llama "data"
        'logoBase64' => $logoBase64
    ]);

    return $pdf->stream('mantenimiento-' . now()->format('Y-m-d_H-i-s') . '.pdf');
}



}
