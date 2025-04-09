<?php
namespace App\Http\Controllers\LargeCashBox;

use App\Exports\BankMovementExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankMovementRequest\IndexBankMovementRequest;
use App\Http\Requests\BankMovementRequest\StoreBankMovementRequest;
use App\Http\Requests\BankMovementRequest\UpdateBankMovementRequest;
use App\Http\Resources\BankMovementResource;
use App\Models\BankMovement;
use App\Services\BankMovementService;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class BankMovementController extends Controller
{
    protected $bankmovementService;

    public function __construct(BankMovementService $BankMovementService)
    {
        $this->bankmovementService = $BankMovementService;
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/bank-movement",
 *     summary="Obtener información de BankMovements con filtros y ordenamiento",
 *     tags={"BankMovement"},
 *     security={{"bearerAuth": {}}},

 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="type_moviment", in="query", description="Filtrar por tipo de movimiento (ENTRADA/SALIDA)", required=false, @OA\Schema(type="string", enum={"ENTRADA", "SALIDA"})),
 *     @OA\Parameter(name="date_moviment", in="query", description="Filtrar por fecha del movimiento", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="total_moviment", in="query", description="Filtrar por total del movimiento", required=false, @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="currency", in="query", description="Filtrar por tipo de moneda", required=false, @OA\Schema(type="string", maxLength=3)),
 *     @OA\Parameter(name="bank_id", in="query", description="Filtrar por ID del banco", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="bank_account_id", in="query", description="Filtrar por ID de la cuenta bancaria", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="transaction_concept_id", in="query", description="Filtrar por ID del concepto de transacción", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="person_id", in="query", description="Filtrar por ID de la persona asociada", required=false, @OA\Schema(type="integer")),

 *     @OA\Response(
 *         response=200,
 *         description="Lista de BankMovements",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/BankMovement"))
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validación fallida",
 *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
 *     )
 * )
 */

    public function index(IndexBankMovementRequest $request)
    {
        // Clonar el request para evitar modificar el original directamente
        $modifiedRequest = $request->merge([
            'to' => $request->has('to') ? Carbon::parse($request->to)->addDay()->toDateString() : null,
        ]);

        return $this->getFilteredResults(
            BankMovement::class,
            $modifiedRequest,
            BankMovement::filters,
            BankMovement::sorts,
            BankMovementResource::class
        );
    }
    /**
     * @OA\Get(
     *     path="/transportedev/public/api/bank-movement-export-excel",
     *     summary="Exportar BankMovements con filtros y ordenamiento",
     *     tags={"BankMovement"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="filters", in="query", description="Filtros aplicables", @OA\Schema(ref="#/components/schemas/BankMovementFilters")),
     *     @OA\Response(response=200, description="Lista de BankMovements", @OA\JsonContent(ref="#/components/schemas/BankMovement")),
     *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
     * )
     */
    public function index_export_excel(IndexBankMovementRequest $request)
    {
        $request['all'] = "true";
        $fileName       = 'Caja_Grande_' . now()->timestamp . '.xlsx';
        $data           = $this->index($request);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            $data = $data->getData(true); // Convertir a array asociativo
        }
        return Excel::download(new BankMovementExport($data, $request['from'], $request['to']), $fileName);
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/bank-movement/{id}",
 *     summary="Obtener detalles de un BankMovement por ID",
 *     tags={"BankMovement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Movimiento de Banco", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Movimiento de Banco encontrado", @OA\JsonContent(ref="#/components/schemas/BankMovement")),
 *     @OA\Response(response=404, description="Movimiento de Banco no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Movimiento de Banco no encontrada")))
 * )
 */

    public function show($id)
    {

        $bank = $this->bankmovementService->getBankMovementById($id);

        if (! $bank) {
            return response()->json([
                'error' => 'Movimiento de Banco No Encontrada',
            ], 404);
        }

        return new BankMovementResource($bank);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/bank-movement",
 *     summary="Crear BankMovement",
 *     tags={"BankMovement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/BankMovementRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="BankMovement creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/BankMovement")
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

    public function store(StoreBankMovementRequest $request)
    {
        $data = $request->validated();
        if ($request->transaction_concept_id == 1) {
            $data['total_anticipado_restante'] = $request->total_moviment;
            $data['total_anticipado']          = $request->total_moviment;
        }
        if ($request->transaction_concept_id == 2) {
            $data['total_anticipado_egreso_restante'] = $request->total_moviment;
            $data['total_anticipado_egreso']          = $request->total_moviment;
        }
        $bank = $this->bankmovementService->createBankMovement($data);
        return new BankMovementResource($bank);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/bank-movement/{id}",
 *     summary="Actualizar un BankMovement",
 *     tags={"BankMovement"},
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
 *             @OA\Schema(ref="#/components/schemas/BankMovementRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="BankMovement actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/BankMovement")
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
 *         description="Movimiento de Banco no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Movimiento de Banco no encontrada")
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

    public function update(UpdateBankMovementRequest $request, $id)
    {

        $validatedData = $request->validated();

        $bank = $this->bankmovementService->getBankMovementById($id);
        if (! $bank) {
            return response()->json([
                'error' => 'Movimiento de Banco No Encontrado',
            ], 404);
        }

        $updatedcarga = $this->bankmovementService->updateBankMovement($bank, $validatedData);
        return new BankMovementResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/bank-movement/{id}",
 *     summary="Eliminar un BankMovementpor ID",
 *     tags={"BankMovement"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="BankMovement eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="BankMovement eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Movimiento de Banco no encontrada"))),

 * )
 */

    public function destroy($id)
    {
        $bank = $this->bankmovementService->getBankMovementById($id);
        if (! $bank) {
            return response()->json([
                'error' => 'Movimiento de Banco No Encontrada.',
            ], 404);
        }
        if ($bank->status === "Confirmado") {
            return response()->json([
                'message' => 'El ingreso a caja grande ya fue confirmado y no se puede eliminar.',
            ], 422);
        }
        if ($bank->pay_installments()->exists()) {
            return response()->json([
                'message' => 'Este anticipo ya ha sido aplicado en una o más amortizaciones y no se puede eliminar.',
            ], 422);
        }

        $bank = $this->bankmovementService->destroyById($id);
        return response()->json([
            'message' => 'Movimiento de Banco eliminado exitosamente',
        ], 200);
    }

    public function change_status($id)
    {
        $bank = $this->bankmovementService->getBankMovementById($id);

        if (! $bank) {
            return response()->json(['error' => 'Movimiento de Banco no encontrado.'], 404);
        }

        if ($this->bankmovementService->change_status($id)) {
            $bank = $this->bankmovementService->getBankMovementById($id);
            return response()->json(['status' => $bank->status], 200);
        }

        return response()->json(['error' => 'No se pudo cambiar el estado.'], 422);
    }

}
