<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankRequest\IndexBankRequest;
use App\Http\Requests\BankRequest\StoreBankRequest;
use App\Http\Requests\BankRequest\UpdateBankRequest;
use App\Http\Resources\BankResource;
use App\Models\Bank;
use App\Services\BankService;

class BankController extends Controller
{
    protected $bankService;

    public function __construct(BankService $BankService)
    {
        $this->bankService = $BankService;
    }

    /**
     * Get all banks with pagination
     * @OA\Get (
     *      path="/transportedev/public/api/bank",
     *      tags={"Bank"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="List of active banks",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Bank")),
     *              @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transportedev/public/api/bank?page=1"),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transportedev/public/api/bank?page=2"),
     *              @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transportedev/public/api/bank"),
     *              @OA\Property(property="per_page", type="integer", example=15),
     *              @OA\Property(property="prev_page_url", type="string", example="null"),
     *              @OA\Property(property="to", type="integer", example=15)
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function index()
    {
        return response()->json(Bank::simplePaginate(15));
    }

/**
 * @OA\Get(
 *     path="/transportedev/public/api/bank-list",
 *     summary="Obtener información de Banks con filtros y ordenamiento",
 *     tags={"Bank"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

 *     @OA\Response(response=200, description="Lista de Banks", @OA\JsonContent(ref="#/components/schemas/Bank")),
 *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
 * )
 */

    public function list(IndexBankRequest $request)
    {

        return $this->getFilteredResults(
            Bank::class,
            $request,
            Bank::filters,
            Bank::sorts,
            BankResource::class
        );
    }
/**
 * @OA\Get(
 *     path="/transportedev/public/api/bank/{id}",
 *     summary="Obtener detalles de un Bank por ID",
 *     tags={"Bank"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", description="ID del Bank", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Bank encontrado", @OA\JsonContent(ref="#/components/schemas/Bank")),
 *     @OA\Response(response=404, description="Banco no encontrada", @OA\JsonContent(type="object", @OA\Property(property="error", type="string", example="Banco no encontrada")))
 * )
 */

    public function show($id)
    {

        $bank = $this->bankService->getBankById($id);

        if (! $bank) {
            return response()->json([
                'error' => 'Banco No Encontrada',
            ], 404);
        }

        return new BankResource($bank);
    }

/**
 * @OA\Post(
 *     path="/transportedev/public/api/bank",
 *     summary="Crear Bank",
 *     tags={"Bank"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/BankRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Bank creada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Bank")
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

    public function store(StoreBankRequest $request)
    {
        $bank = $this->bankService->createBank($request->validated());
        return new BankResource($bank);
    }

/**
 * @OA\Put(
 *     path="/transportedev/public/api/bank/{id}",
 *     summary="Actualizar un Bank",
 *     tags={"Bank"},
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
 *             @OA\Schema(ref="#/components/schemas/BankRequest")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Bank actualizada exitosamente",
 *         @OA\JsonContent(ref="#/components/schemas/Bank")
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
 *         description="Banco no encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Banco no encontrada")
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

    public function update(UpdateBankRequest $request, $id)
    {

        $validatedData = $request->validated();

        $bank = $this->bankService->getBankById($id);
        if (! $bank) {
            return response()->json([
                'error' => 'Banco No Encontrado',
            ], 404);
        }

        $updatedcarga = $this->bankService->updateBank($bank, $validatedData);
        return new BankResource($updatedcarga);
    }

/**
 * @OA\Delete(
 *     path="/transportedev/public/api/bank/{id}",
 *     summary="Eliminar un Bankpor ID",
 *     tags={"Bank"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Bank eliminado", @OA\JsonContent(@OA\Property(property="message", type="string", example="Bank eliminado exitosamente"))),
 *     @OA\Response(response=404, description="No encontrado", @OA\JsonContent(@OA\Property(property="error", type="string", example="Banco no encontrada"))),

 * )
 */

    public function destroy($id)
    {

        $bank = $this->bankService->getBankById($id);

        if (! $bank) {
            return response()->json([
                'error' => 'Banco No Encontrada.',
            ], 404);
        }
        $bank = $this->bankService->destroyById($id);

        return response()->json([
            'message' => 'Banco eliminado exitosamente',
        ], 200);
    }

}
