<?php
namespace App\Http\Controllers\LargeCashBox;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConceptTransactionRequest\IndexConceptTransactionRequest;
use App\Http\Requests\ConceptTransactionRequest\StoreConceptTransactionRequest;
use App\Http\Requests\ConceptTransactionRequest\UpdateConceptTransactionRequest;
use App\Http\Resources\TransactionConceptResource;
use App\Models\TransactionConcept;
use App\Services\TransactionConceptsService;
use Illuminate\Http\Request;

class TransactionConceptsController extends Controller
{
    protected $transcationConceptService;

    public function __construct(TransactionConceptsService $TransactionConceptService)
    {
        $this->transcationConceptService = $TransactionConceptService;
    }

/**
 * @OA\Get(
 *     path="/transportev2/public/api/transcationConcept",
 *     summary="Obtener información de TransactionConcepts con filtros y ordenamiento",
 *     tags={"TransactionConcept"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="type", in="query", description="Filtrar por tipo", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de TransactionConcepts", @OA\JsonContent(ref="#/components/schemas/TransactionConcept")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexConceptTransactionRequest $request)
    {

        return $this->getFilteredResults(
            TransactionConcept::class,
            $request,
            TransactionConcept::filters,
            TransactionConcept::sorts,
            TransactionConceptResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportev2/public/api/transcationConcept/{id}",
 *     summary="Obtener detalles de un TransactionConcept por ID",
 *     tags={"TransactionConcept"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del TransactionConcept", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="TransactionConcept encontrado", @OA\JsonContent(ref="#/components/schemas/TransactionConcept")),
 *     @OA\Response(response=404, description="Concepto no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Concepto no encontrada")))
 * )
 */

    public function show($id)
    {

        $transcationConcept = $this->transcationConceptService->getTransactionConceptById($id);

        if (! $transcationConcept) {
            return response()->json([
                'error' => 'Concepto No Encontrada',
            ], 404);
        }

        return new TransactionConceptResource($transcationConcept);
    }

/**
 * @OA\Post(
 *     path="/transportev2/public/api/transcationConcept",
 *     summary="Crear TransactionConcept",
 *     tags={"TransactionConcept"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/TransactionConceptRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="TransactionConcept creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/TransactionConcept")
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

    public function store(StoreConceptTransactionRequest $request)
    {
        $transcationConcept = $this->transcationConceptService->createTransactionConcept($request->validated());
        return new TransactionConceptResource($transcationConcept);
    }

/**
 * @OA\Put(
 *     path="/transportev2/public/api/transcationConcept/{id}",
 *     summary="Actualizar un TransactionConcept",
 *     tags={"TransactionConcept"},
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
 *             @OA\Schema(ref="#/components/schemas/TransactionConceptRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="TransactionConcept actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/TransactionConcept")
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
 *         description="Concepto no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Concepto no encontrada")
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

    public function update(UpdateConceptTransactionRequest $request, $id)
    {

        $validatedData = $request->validated();

        $transcationConcept = $this->transcationConceptService->getTransactionConceptById($id);
        if (! $transcationConcept) {
            return response()->json([
                'error' => 'Concepto No Encontrada',
            ], 404);
        }

        $updatedcarga = $this->transcationConceptService->updateTransactionConcept($transcationConcept, $validatedData);
        return new TransactionConceptResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportev2/public/api/transcationConcept/{id}",
 *     summary="Eliminar un TransactionConceptpor ID",
 *     tags={"TransactionConcept"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="TransactionConcept eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="TransactionConcept eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Concepto no encontrada"))),

 * )
 */

    public function destroy($id)
    {

        $transactionconcept = $this->transcationConceptService->getTransactionConceptById($id);

        if (! $transactionconcept) {
            return response()->json([
                'error' => 'Concepto No Encontrada.',
            ], 404);
        }
        $transactionconcept = $this->transcationConceptService->destroyById($id);

        return response()->json([
            'message' => 'Concepto eliminado exitosamente',
        ], 200);
    }
}
