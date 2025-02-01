<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CargaDocumentRequest\IndexCargaDocumentRequest;
use App\Http\Requests\CargaDocumentRequest\StoreCargaDocumentRequest;
use App\Http\Requests\CargaDocumentRequest\UpdateCargaDocumentRequest;
use App\Http\Resources\CargaResource;
use App\Models\CargaDocument;
use App\Services\CargaDocumentService;
use App\Services\CarrierGuideService;

class CargarDocumentController extends Controller
{
    protected $cargaDocumentService;

    public function __construct(CargaDocumentService $CargaDocumentService)
    {
        $this->cargaDocumentService = $CargaDocumentService;
    }

    
/**
 * @OA\Get(
 *     path="/transportev2/public/api/cargaDocument",
 *     summary="Obtener información de CargaDocuments con filtros y ordenamiento",
 *     tags={"CargaDocument"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="name", in="query", description="Filtrar por nombre de CargaDocument", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="start_date", in="query", description="Filtrar por fecha de inicio", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="end_date", in="query", description="Filtrar por fecha de fin", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="proyect_id", in="query", description="Filtrar por ID del proyecto", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="objective", in="query", description="Filtrar por objetivo de la actividad", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="total_amount", in="query", description="Filtrar por monto total de la actividad", required=false, @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="collected_amount", in="query", description="Filtrar por monto recolectado", required=false, @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="status", in="query", description="Filtrar por estado de la actividad", required=false, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Lista de CargaDocuments", @OA\JsonContent(ref="#/components/schemas/CargaDocument")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */


 public function index(IndexCargaDocumentRequest $request)
 {

     return $this->getFilteredResults(
         CargaDocument::class,
         $request,
         CargaDocument::filters,
         CargaDocument::sorts,
         CargaResource::class
     );
 }
/**
* @OA\Get(
*     path="/transportev2/public/api/cargaDocument/{id}",
*     summary="Obtener detalles de un Documento de Carga por ID",
*     tags={"CargaDocument"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(name="id", in="path", description="ID del CargaDocument", required=true, @OA\Schema(type="integer", example=1)),
*     @OA\Response(response=200, description="Documento de Carga encontrado", @OA\JsonContent(ref="#/components/schemas/CargaDocument")),
*     @OA\Response(response=404, description="Documento de Carga no encontrado", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Documento de Carga no encontrado")))
* )
*/

 public function show($id)
 {

     $carga = $this->cargaDocumentService->getCargaDocumentById($id);

     if (! $carga) {
         return response()->json([
             'error' => 'Documento de Carga No Encontrado',
         ], 404);
     }

     return new CargaResource($carga);
 }

/**
* @OA\Post(
*     path="/transportev2/public/api/cargaDocument",
*     summary="Crear CargaDocument",
*     tags={"CargaDocument"},
*     security={{"bearerAuth": {}}},  
*     @OA\RequestBody(
*         required=true,
*         @OA\MediaType(
*             mediaType="multipart/form-data",
*             @OA\Schema(ref="#/components/schemas/CargaDocumentRequest")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Documento de Carga creada exitosamente",
*         @OA\JsonContent(ref="#/components/schemas/CargaDocument")
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

 public function store(StoreCargaDocumentRequest $request)
 {
     $carga = $this->cargaDocumentService->createCargaDocument($request->validated());
     return new CargaResource($carga);
 }

/**
* @OA\Put(
*     path="/transportev2/public/api/cargaDocument/{id}",
*     summary="Actualizar un CargaDocument",
*     tags={"CargaDocument"},
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
*             @OA\Schema(ref="#/components/schemas/CargaDocumentRequest")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Documento de Carga actualizada exitosamente",
*         @OA\JsonContent(ref="#/components/schemas/CargaDocument")
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
*         description="Documento de Carga no encontrado",
*         @OA\JsonContent(
*             @OA\Property(property="error", type="string", example="Documento de Carga no encontrado")
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

 public function update(UpdateCargaDocumentRequest $request, $id)
 {

     $validatedData = $request->validated();

     $carga = $this->cargaDocumentService->getCargaDocumentById($id);
     if (! $carga) {
         return response()->json([
             'error' => 'Documento de Carga No Encontrado',
         ], 404);
     }

     $updatedcarga = $this->cargaDocumentService->updateCargaDocument($carga, $validatedData);
     return new CargaResource($updatedcarga);
 }

/**
* @OA\Delete(
*     path="/transportev2/public/api/cargadocument/{id}",
*     summary="Eliminar un Documento de Cargapor ID",
*     tags={"CargaDocument"},
*     security={{"bearerAuth": {}}},
*     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
*     @OA\Response(response=200, description="Documento de Carga eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Documento de Carga eliminado exitosamente"))),
*     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Documento de Carga no encontrado"))),

* )
*/

 public function destroy($id)
 {

     $proyect = $this->cargaDocumentService->getCargaDocumentById($id);

     if (! $proyect) {
         return response()->json([
             'error' => 'Documento de Carga No Encontrado.',
         ], 404);
     }
     $proyect = $this->cargaDocumentService->destroyById($id);

     return response()->json([
         'message' => 'Documento de Cargaeliminado exitosamente',
     ], 200);
 }

}
