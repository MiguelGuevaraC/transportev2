<?php
namespace App\Http\Controllers\LargeCashBox;

use App\Exports\ExcelExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankMovementRequest\IndexBankMovementRequest;
use App\Http\Requests\BankMovementRequest\StoreBankMovementRequest;
use App\Http\Requests\BankMovementRequest\UpdateBankMovementRequest;
use App\Http\Resources\BankMovementResource;
use App\Models\BankMovement;
use App\Services\BankMovementService;
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
 *     path="/transporte/public/api/bank-movement",
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
        $request['all'] = "true";
        return $this->getFilteredResults(
            BankMovement::class,
            $request,
            BankMovement::filters,
            BankMovement::sorts,
            BankMovementResource::class
        );
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/bank-movement-export-excel",
     *     summary="Exportar BankMovements con filtros y ordenamiento",
     *     tags={"BankMovement"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="filters", in="query", description="Filtros aplicables", @OA\Schema(ref="#/components/schemas/BankMovementFilters")),
     *     @OA\Response(response=200, description="Lista de BankMovements", @OA\JsonContent(ref="#/components/schemas/BankMovement")),
     *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
     * )
     */
    public function index_export_excel(IndexBankMovementRequest $request, $sumColumns = ['Monto'])
    {
        $request['all'] = "true";
        $data           = $this->index($request);
        $fileName       = 'Caja_Grande_' . now()->timestamp . '.xlsx';

        $columns = [
            'Tipo'    => 'type_moviment',
            'Fecha'   => 'date_moviment',
            'Monto'   => 'total_moviment',
            'Moneda'  => 'currency',
            'Banco'   => 'bank.name',
            'Cuenta'  => 'bank_account.account_number',
            'Persona' => ['person.names', 'person.fatherSurname', 'person.businessName'],
        ];

        return Excel::download(new ExcelExport($data, $columns, $sumColumns), $fileName);
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/bank-movement/{id}",
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
 *     path="/transporte/public/api/bank-movement",
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
        $bank = $this->bankmovementService->createBankMovement($request->validated());
        return new BankMovementResource($bank);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/bank-movement/{id}",
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
 *     path="/transporte/public/api/bank-movement/{id}",
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
        $bank = $this->bankmovementService->destroyById($id);

        return response()->json([
            'message' => 'Movimiento de Banco eliminado exitosamente',
        ], 200);
    }

}
