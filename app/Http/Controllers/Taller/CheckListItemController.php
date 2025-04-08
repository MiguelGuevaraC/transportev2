<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckListItemRequest\IndexCheckListItemRequest;
use App\Http\Requests\CheckListItemRequest\StoreCheckListItemRequest;
use App\Http\Requests\CheckListItemRequest\UpdateCheckListItemRequest;
use App\Http\Resources\CheckListItemResource;
use App\Models\CheckListItem;
use App\Services\CheckListItemService;
use Illuminate\Http\Request;

class CheckListItemController extends Controller
{
    protected $checklistitemService;

    public function __construct(CheckListItemService $CheckListItemService)
    {
        $this->checklistitemService = $CheckListItemService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/checklistitem",
 *     summary="Obtener información de CheckListItems con filtros y ordenamiento",
 *     tags={"CheckListItem"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de CheckListItems", @OA\JsonContent(ref="#/components/schemas/CheckListItem")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexCheckListItemRequest $request)
    {
        return $this->getFilteredResults(
            CheckListItem::class,
            $request,
            CheckListItem::filters,
            CheckListItem::sorts,
            CheckListItemResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/checklistitem/{id}",
 *     summary="Obtener detalles de un Check List Itema por ID",
 *     tags={"CheckListItem"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del CheckListItem", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Check List Itema encontrado", @OA\JsonContent(ref="#/components/schemas/CheckListItem")),
 *     @OA\Response(response=404, description="Check List Itema no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Check List Itema no encontrada")))
 * )
 */

    public function show($id)
    {
        $checklistitem = $this->checklistitemService->getCheckListItemById($id);
        if (! $checklistitem) {
            return response()->json([
                'error' => 'Check List Itema No Encontrada',
            ], 404);
        }
        return new CheckListItemResource($checklistitem);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/checklistitem",
 *     summary="Crear CheckListItem",
 *     tags={"CheckListItem"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/CheckListItemRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Check List Itema creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/CheckListItem")
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

    public function store(StoreCheckListItemRequest $request)
    {
        $data           = $request->validated();
        $data['status'] = 'ACTIVO';
        $checklistitem       = $this->checklistitemService->createCheckListItem($data);
        return new CheckListItemResource($checklistitem);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/checklistitem/{id}",
 *     summary="Actualizar un CheckListItem",
 *     tags={"CheckListItem"},
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
 *             @OA\Schema(ref="#/components/schemas/CheckListItemRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Check List Itema actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/CheckListItem")
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
 *         description="Check List Itema no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Check List Itema no encontrada")
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

    public function update(UpdateCheckListItemRequest $request, $id)
    {
        $validatedData = $request->validated();
        $checklistitem      = $this->checklistitemService->getCheckListItemById($id);
        if (! $checklistitem) {
            return response()->json([
                'error' => 'Check List Itema No Encontrado',
            ], 404);
        }
        $updatedcarga = $this->checklistitemService->updateCheckListItem($checklistitem, $validatedData);
        return new CheckListItemResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/checklistitem/{id}",
 *     summary="Eliminar un CheckListItempor ID",
 *     tags={"CheckListItem"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Check List Itema eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Check List Itema eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Check List Itema no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $checklistitem = $this->checklistitemService->getCheckListItemById($id);
        if (! $checklistitem) {
            return response()->json([
                'error' => 'Check List Itema No Encontrada.',
            ], 404);
        }

        // if ($checklistitem->repuestos()->exists()) {
        //     return response()->json([
        //         'error' => 'La categoría tiene productos asociados.',
        //     ], 422);
        // }

        $checklistitem = $this->checklistitemService->destroyById($id);
        return response()->json([
            'message' => 'Check List Itema eliminado exitosamente',
        ], 200);
    }
}
