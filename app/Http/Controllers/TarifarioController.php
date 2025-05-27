<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TarifarioRequest\IndexTarifarioRequest;
use App\Http\Requests\TarifarioRequest\StoreTarifarioRequest;
use App\Http\Requests\TarifarioRequest\UpdateTarifarioRequest;
use App\Http\Resources\TarifarioResource;
use App\Models\Tarifario;
use App\Services\CarrierGuideService;
use App\Services\TarifarioService;

class TarifarioController extends Controller
{
    protected $tarofarioService;

    public function __construct(TarifarioService $TarifarioService)
    {
        $this->tarofarioService = $TarifarioService;
    }


/**
 * @OA\Get(
 *     path="/transporte/public/api/tarifario",
 *     summary="Obtener información de Tarifarios con filtros y ordenamiento",
 *     tags={"Tarifario"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="tarifa", in="query", description="Filtrar por tarifa", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="description", in="query", description="Filtrar por descripción", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="origin_id", in="query", description="Filtrar por ID de origen", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="destination_id", in="query", description="Filtrar por ID de destino", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="person_id", in="query", description="Filtrar por ID de persona", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="unity_id", in="query", description="Filtrar por ID de unidad de medida", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="limitweight_min", in="query", description="Filtrar por peso mínimo", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="limitweight_max", in="query", description="Filtrar por peso máximo", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="created_at", in="query", description="Filtrar por fecha de creación", required=false, @OA\Schema(type="string", format="date-time")),
 *     @OA\Response(response=200, description="Lista de Tarifarios", @OA\JsonContent(ref="#/components/schemas/Tarifario")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */



 public function index(IndexTarifarioRequest $request)
 {

     return $this->getFilteredResults(
         Tarifario::class,
         $request,
         Tarifario::filters,
         Tarifario::sorts,
         TarifarioResource::class
     );
 }
/**
* @OA\Get(
*     path="/transporte/public/api/tarifario/{id}",
*     summary="Obtener detalles de un Tarifario por ID",
*     tags={"Tarifario"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(name="id", in="path", description="ID del Tarifario", required=true, @OA\Schema(type="integer", example=1)),
*     @OA\Response(response=200, description="Tarifario encontrado", @OA\JsonContent(ref="#/components/schemas/Tarifario")),
*     @OA\Response(response=404, description="Tarifario no encontrado", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Tarifario no encontrado")))
* )
*/

 public function show($id)
 {

     $tarifario = $this->tarofarioService->getTarifarioById($id);

     if (! $tarifario) {
         return response()->json([
             'error' => 'Tarifario No Encontrado',
         ], 404);
     }

     return new TarifarioResource($tarifario);
 }

/**
* @OA\Post(
*     path="/transporte/public/api/tarifario",
*     summary="Crear Tarifario",
*     tags={"Tarifario"},
*     security={{"bearerAuth": {}}},
*     @OA\RequestBody(
*         required=true,
*         @OA\MediaType(
*             mediaType="multipart/form-data",
*             @OA\Schema(ref="#/components/schemas/TarifarioRequest")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Tarifario creada exitosamente",
*         @OA\JsonContent(ref="#/components/schemas/Tarifario")
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

 public function store(StoreTarifarioRequest $request)
 {
     $tarifario = $this->tarofarioService->createTarifario($request->validated());
     return new TarifarioResource($tarifario);
 }

/**
* @OA\Put(
*     path="/transporte/public/api/tarifario/{id}",
*     summary="Actualizar un Tarifario",
*     tags={"Tarifario"},
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
*             @OA\Schema(ref="#/components/schemas/TarifarioRequest")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Tarifario actualizada exitosamente",
*         @OA\JsonContent(ref="#/components/schemas/Tarifario")
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
*         description="Tarifario no encontrado",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Tarifario no encontrado")
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

 public function update(UpdateTarifarioRequest $request, $id)
 {

     $validatedData = $request->validated();

     $tarifario = $this->tarofarioService->getTarifarioById($id);
     if (! $tarifario) {
         return response()->json([
             'error' => 'Tarifario No Encontrado',
         ], 404);
     }

     $updatedcarga = $this->tarofarioService->updateTarifario($tarifario, $validatedData);
     return new TarifarioResource($updatedcarga);
 }

/**
* @OA\Delete(
*     path="/transporte/public/api/tarifario/{id}",
*     summary="Eliminar un Tarifariopor ID",
*     tags={"Tarifario"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
*     @OA\Response(response=200, description="Tarifario eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Tarifario eliminado exitosamente"))),
*     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Tarifario no encontrado"))),

* )
*/

 public function destroy($id)
 {

     $proyect = $this->tarofarioService->getTarifarioById($id);

     if (! $proyect) {
         return response()->json([
             'error' => 'Tarifario No Encontrado.',
         ], 404);
     }
     $proyect = $this->tarofarioService->destroyById($id);

     return response()->json([
         'message' => 'Tarifarioeliminado exitosamente',
     ], 200);
 }

}
