<?php
namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\TallerRequest\IndexTallerRequest;
use App\Http\Requests\TallerRequest\StoreTallerRequest;
use App\Http\Requests\TallerRequest\UpdateTallerRequest;
use App\Http\Resources\TallerResource;
use App\Models\Taller;
use App\Services\TallerService;
use Illuminate\Http\Request;

class TallerController extends Controller
{
    protected $tallerService;

    public function __construct(TallerService $TallerService)
    {
        $this->tallerService = $TallerService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/taller",
 *     summary="Obtener información de Tallers con filtros y ordenamiento",
 *     tags={"Taller"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Tallers", @OA\JsonContent(ref="#/components/schemas/Taller")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexTallerRequest $request)
    {
        return $this->getFilteredResults(
            Taller::class,
            $request,
            Taller::filters,
            Taller::sorts,
            TallerResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/taller/{id}",
 *     summary="Obtener detalles de un Taller por ID",
 *     tags={"Taller"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Taller", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Taller encontrado", @OA\JsonContent(ref="#/components/schemas/Taller")),
 *     @OA\Response(response=404, description="Taller no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Taller no encontrada")))
 * )
 */

    public function show($id)
    {
        $taller = $this->tallerService->getTallerById($id);
        if (! $taller) {
            return response()->json([
                'error' => 'Taller No Encontrada',
            ], 404);
        }
        return new TallerResource($taller);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/taller",
 *     summary="Crear Taller",
 *     tags={"Taller"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/TallerRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Taller creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Taller")
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

    public function store(StoreTallerRequest $request)
    {
        $data= $request->validated();
        $data['status']='ACTIVO';
        $taller = $this->tallerService->createTaller($data);
        return new TallerResource($taller);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/taller/{id}",
 *     summary="Actualizar un Taller",
 *     tags={"Taller"},
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
 *             @OA\Schema(ref="#/components/schemas/TallerRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Taller actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Taller")
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
 *         description="Taller no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Taller no encontrada")
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

    public function update(UpdateTallerRequest $request, $id)
    {
        $validatedData = $request->validated();
        $taller = $this->tallerService->getTallerById($id);
        if (! $taller) {
            return response()->json([
                'error' => 'Taller No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->tallerService->updateTaller($taller, $validatedData);
        return new TallerResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/taller/{id}",
 *     summary="Eliminar un Tallerpor ID",
 *     tags={"Taller"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Taller eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Taller eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Taller no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $taller = $this->tallerService->getTallerById($id);
        if (! $taller) {
            return response()->json([
                'error' => 'Taller No Encontrada.',
            ], 404);
        }
        $taller = $this->tallerService->destroyById($id);
        return response()->json([
            'message' => 'Taller eliminado exitosamente',
        ], 200);
    }

}
