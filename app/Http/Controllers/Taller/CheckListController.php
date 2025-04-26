<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckListRequest\IndexCheckListRequest;
use App\Http\Requests\CheckListRequest\StoreCheckListRequest;
use App\Http\Requests\CheckListRequest\UpdateCheckListRequest;
use App\Http\Resources\CheckListResource;
use App\Models\CheckList;
use App\Services\CheckListService;
use Illuminate\Http\Request;

class CheckListController extends Controller
{
    protected $checklistService;

    public function __construct(CheckListService $CheckListService)
    {
        $this->checklistService = $CheckListService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/checklist",
 *     summary="Obtener información de CheckLists con filtros y ordenamiento",
 *     tags={"CheckList"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de CheckLists", @OA\JsonContent(ref="#/components/schemas/CheckList")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexCheckListRequest $request)
    {
        return $this->getFilteredResults(
            CheckList::class,
            $request,
            CheckList::filters,
            CheckList::sorts,
            CheckListResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/checklist/{id}",
 *     summary="Obtener detalles de un Check List a por ID",
 *     tags={"CheckList"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del CheckList", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Check List a encontrado", @OA\JsonContent(ref="#/components/schemas/CheckList")),
 *     @OA\Response(response=404, description="Check List a no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Check List a no encontrada")))
 * )
 */

    public function show($id)
    {
        $checklist = $this->checklistService->getCheckListById($id);
        if (! $checklist) {
            return response()->json([
                'error' => 'Check List a No Encontrada',
            ], 404);
        }
        return new CheckListResource($checklist);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/checklist",
 *     summary="Crear CheckList",
 *     tags={"CheckList"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/CheckListRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Check List a creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/CheckList")
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

    public function store(StoreCheckListRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'ACTIVO';
        $checklist       = $this->checklistService->createCheckList($data);
        return new CheckListResource($checklist);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/checklist/{id}",
 *     summary="Actualizar un CheckList",
 *     tags={"CheckList"},
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
 *             @OA\Schema(ref="#/components/schemas/CheckListRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Check List a actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/CheckList")
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
 *         description="Check List a no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Check List a no encontrada")
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

    public function update(UpdateCheckListRequest $request, $id)
    {
        $validatedData = $request->validated();
        $checklist      = $this->checklistService->getCheckListById($id);
        if (! $checklist) {
            return response()->json([
                'error' => 'Check List a No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->checklistService->updateCheckList($checklist, $validatedData);
        return new CheckListResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/checklist/{id}",
 *     summary="Eliminar un CheckListpor ID",
 *     tags={"CheckList"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Check List a eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Check List a eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Check List a no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $checklist = $this->checklistService->getCheckListById($id);
        if (! $checklist) {
            return response()->json([
                'error' => 'Check List a No Encontrada.',
            ], 404);
        }

        // if ($checklist->repuestos()->exists()) {
        //     return response()->json([
        //         'error' => 'La categoría tiene productos asociados.',
        //     ], 422);
        // }

        $checklist = $this->checklistService->destroyById($id);
        return response()->json([
            'message' => 'Check List a eliminado exitosamente',
        ], 200);
    }
}
