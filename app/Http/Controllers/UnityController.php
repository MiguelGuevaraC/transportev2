<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnityRequest\IndexUnityRequest;
use App\Http\Requests\UnityRequest\StoreUnityRequest;
use App\Http\Requests\UnityRequest\UpdateUnityRequest;
use App\Http\Resources\UnityResource;
use App\Models\Unity;
use App\Services\CarrierGuideService;
use App\Services\UnityService;

class UnityController extends Controller
{
    protected $unityService;

    public function __construct(UnityService $UnityService)
    {
        $this->unityService = $UnityService;
    }

    
/**
 * @OA\Get(
 *     path="/transportev2/public/api/unity",
 *     summary="Obtener información de Unitys con filtros y ordenamiento",
 *     tags={"Unity"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="code", in="query", description="Filtrar por codigo", required=false, @OA\Schema(type="string")),
 
 *     @OA\Response(response=200, description="Lista de Unitys", @OA\JsonContent(ref="#/components/schemas/Unity")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */


 public function index(IndexUnityRequest $request)
 {

     return $this->getFilteredResults(
         Unity::class,
         $request,
         Unity::filters,
         Unity::sorts,
         UnityResource::class
     );
 }
/**
* @OA\Get(
*     path="/transportev2/public/api/unity/{id}",
*     summary="Obtener detalles de un Unity por ID",
*     tags={"Unity"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(name="id", in="path", description="ID del Unity", required=true, @OA\Schema(type="integer", example=1)),
*     @OA\Response(response=200, description="Unity encontrado", @OA\JsonContent(ref="#/components/schemas/Unity")),
*     @OA\Response(response=404, description="Unidad no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Unidad no encontrada")))
* )
*/

 public function show($id)
 {

     $unity = $this->unityService->getUnityById($id);

     if (! $unity) {
         return response()->json([
             'error' => 'Unidad No Encontrada',
         ], 404);
     }

     return new UnityResource($unity);
 }

/**
* @OA\Post(
*     path="/transportev2/public/api/unity",
*     summary="Crear Unity",
*     tags={"Unity"},
*     security={{"bearerAuth": {}}},  
*     @OA\RequestBody(
*         required=true,
*         @OA\MediaType(
*             mediaType="multipart/form-data",
*             @OA\Schema(ref="#/components/schemas/UnityRequest")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Unity creada exitosamente",
*         @OA\JsonContent(ref="#/components/schemas/Unity")
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

 public function store(StoreUnityRequest $request)
 {
     $unity = $this->unityService->createUnity($request->validated());
     return new UnityResource($unity);
 }

/**
* @OA\Put(
*     path="/transportev2/public/api/unity/{id}",
*     summary="Actualizar un Unity",
*     tags={"Unity"},
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
*             @OA\Schema(ref="#/components/schemas/UnityRequest")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Unity actualizada exitosamente",
*         @OA\JsonContent(ref="#/components/schemas/Unity")
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
*         description="Unidad no encontrada",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Unidad no encontrada")
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

 public function update(UpdateUnityRequest $request, $id)
 {

     $validatedData = $request->validated();

     $unity = $this->unityService->getUnityById($id);
     if (! $unity) {
         return response()->json([
             'error' => 'Unidad No Encontrada',
         ], 404);
     }

     $updatedcarga = $this->unityService->updateUnity($unity, $validatedData);
     return new UnityResource($updatedcarga);
 }

/**
* @OA\Delete(
*     path="/transportev2/public/api/unity/{id}",
*     summary="Eliminar un Unitypor ID",
*     tags={"Unity"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
*     @OA\Response(response=200, description="Unity eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unity eliminado exitosamente"))),
*     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Unidad no encontrada"))),

* )
*/

 public function destroy($id)
 {

     $proyect = $this->unityService->getUnityById($id);

     if (! $proyect) {
         return response()->json([
             'error' => 'Unidad No Encontrada.',
         ], 404);
     }
     $proyect = $this->unityService->destroyById($id);

     return response()->json([
         'message' => 'Unidad eliminado exitosamente',
     ], 200);
 }

}
