<?php
namespace App\Http\Controllers\LargeCashBox;

use App\Exports\ExcelExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest\IndexBankAccountRequest;
use App\Http\Requests\BankAccountRequest\StoreBankAccountRequest;
use App\Http\Requests\BankAccountRequest\UpdateBankAccountRequest;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BankAccountController extends Controller
{
    protected $bankAccountService;

    public function __construct(BankAccountService $BankAccountService)
    {
        $this->bankAccountService = $BankAccountService;
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/bank-account",
 *     summary="Obtener información de BankAccounts con filtros y ordenamiento",
 *     tags={"BankAccount"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="type", in="query", description="Filtrar por tipo", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de BankAccounts", @OA\JsonContent(ref="#/components/schemas/BankAccount")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index(IndexBankAccountRequest $request)
    {

        return $this->getFilteredResults(
            BankAccount::class,
            $request,
            BankAccount::filters,
            BankAccount::sorts,
            BankAccountResource::class
        );
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/bank-account-export-excel",
 *     summary="Exportar BankAccounts con filtros y ordenamiento",
 *     tags={"BankAccount"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="filters", in="query", description="Filtros aplicables", @OA\Schema(ref="#/components/schemas/BankAccountFilters")),
 *     @OA\Response(response=200, description="Lista de BankAccounts", @OA\JsonContent(ref="#/components/schemas/BankAccount")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function index_export_excel(IndexBankAccountRequest $request, $sumColumns = [])
    {
        $request['all'] = "true";
        $data           = $this->index($request);
        $fileName       = 'Caja_Grande_' . now()->timestamp . '.xlsx';
        $columns        = BankAccount::fields_export;
        return Excel::download(new ExcelExport($data, $columns, $sumColumns), $fileName);
    }
/**
 * @OA\Get(
 *     path="/transporte/public/api/bank-account/{id}",
 *     summary="Obtener detalles de un BankAccount por ID",
 *     tags={"BankAccount"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del BankAccount", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="BankAccount encontrado", @OA\JsonContent(ref="#/components/schemas/BankAccount")),
 *     @OA\Response(response=404, description="Cuenta Bancaria no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Cuenta Bancaria no encontrada")))
 * )
 */

    public function show($id)
    {

        $bankAccount = $this->bankAccountService->getBankAccountById($id);

        if (! $bankAccount) {
            return response()->json([
                'error' => 'Cuenta Bancaria No Encontrada',
            ], 404);
        }

        return new BankAccountResource($bankAccount);
    }

/**
 * @OA\Post(
 *     path="/transporte/public/api/bank-account",
 *     summary="Crear BankAccount",
 *     tags={"BankAccount"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/BankAccountRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="BankAccount creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/BankAccount")
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

    public function store(StoreBankAccountRequest $request)
    {
        $bankAccount = $this->bankAccountService->createBankAccount($request->validated());
        return new BankAccountResource($bankAccount);
    }

/**
 * @OA\Put(
 *     path="/transporte/public/api/bank-account/{id}",
 *     summary="Actualizar un BankAccount",
 *     tags={"BankAccount"},
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
 *             @OA\Schema(ref="#/components/schemas/BankAccountRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="BankAccount actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/BankAccount")
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
 *         description="Cuenta Bancaria no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Cuenta Bancaria no encontrada")
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

    public function update(UpdateBankAccountRequest $request, $id)
    {

        $validatedData = $request->validated();

        $bankAccount = $this->bankAccountService->getBankAccountById($id);
        if (! $bankAccount) {
            return response()->json([
                'error' => 'Cuenta Bancaria No Encontrada',
            ], 404);
        }

        $updatedcarga = $this->bankAccountService->updateBankAccount($bankAccount, $validatedData);
        return new BankAccountResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transporte/public/api/bank-account/{id}",
 *     summary="Eliminar un BankAccount por ID",
 *     tags={"BankAccount"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="BankAccount eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="BankAccount eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Cuenta Bancaria no encontrada"))),

 * )
 */

    public function destroy($id)
    {

        $bankaccount = $this->bankAccountService->getBankAccountById($id);

        if (! $bankaccount) {
            return response()->json([
                'error' => 'Cuenta Bancaria No Encontrada.',
            ], 404);
        }
        $bankaccount = $this->bankAccountService->destroyById($id);

        return response()->json([
            'message' => 'Cuenta Bancaria eliminado exitosamente',
        ], 200);
    }
}
