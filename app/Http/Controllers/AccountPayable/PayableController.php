<?php
namespace App\Http\Controllers\AccountPayable;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayableRequest\IndexPayableRequest;
use App\Http\Requests\PayableRequest\PayPayableRequest;
use App\Http\Resources\PayableResource;
use App\Http\Resources\PayPayableResource;
use App\Models\BankAccount;
use App\Models\Payable;
use App\Models\PayPayable;
use App\Services\BankMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayableController extends Controller
{

    protected $bankmovementService;

    public function __construct(BankMovementService $BankMovementService)
    {
        $this->bankmovementService = $BankMovementService;
    }
    /**
     * @OA\Get(
     *     path="/transportedev/public/api/payable",
     *     summary="Obtener información de Banks con filtros y ordenamiento",
     *     tags={"Bank"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="query", description="Filtrar por ID", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="name", in="query", description="Filtrar por name", required=false, @OA\Schema(type="string")),

     *     @OA\Response(response=200, description="Lista de Banks", @OA\JsonContent(ref="#/components/schemas/Bank")),
     *     @OA\Response(response=422, description="Validación fallida", @OA\JsonContent(type="object", @OA\Property(property="error", type="string")))
     * )
     */

    public function list(IndexPayableRequest $request)
    {

        $query = Payable::whereHas('driver_expense.programming', function ($q) use ($request) {
            if ($request->filled('programming_numero')) {
                $q->where('numero', 'like', '%' . $request->programming_numero . '%');
            }
        });
        return $this->getFilteredResults(
            $query,
            $request,
            Payable::filters,
            Payable::sorts,
            PayableResource::class
        );
    }

/**
 * @OA\Put(
 *     path="/payable/{id}/pay",
 *     summary="Realiza el pago de una cuota",
 *     tags={"payable"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la cuota a pagar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Datos del pago",
 *         @OA\JsonContent(
 *             required={"paymentDate", "payable_id"},
 *             @OA\Property(property="paymentDate", type="string", format="date", example="2024-09-02"),
 *             @OA\Property(property="payable_id", type="integer", example=1),
 *             @OA\Property(property="yape", type="number", format="float", nullable=true, example=20.00),
 *             @OA\Property(property="deposit", type="number", format="float", nullable=true, example=30.00),
 *             @OA\Property(property="cash", type="number", format="float", nullable=true, example=50.00),
 *             @OA\Property(property="card", type="number", format="float", nullable=true, example=0.50),
 *             @OA\Property(property="plin", type="number", format="float", nullable=true, example=50.00),
 *             @OA\Property(property="comment", type="string", nullable=true, example="Pago parcial"),
 *             @OA\Property(property="nroOperacion", type="string", nullable=true, example="OP123456"),
 *             @OA\Property(property="bank_id", type="integer", nullable=true, example=2),
 *             @OA\Property(property="bank_movement_id", type="integer", nullable=true, example=5),
 *             @OA\Property(property="bank_account_id", type="integer", nullable=true, example=3),
 *             @OA\Property(property="is_detraction", type="boolean", example=false),
 *             @OA\Property(property="is_anticipo", type="boolean", example=false)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Pago procesado exitosamente"),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(@OA\Property(property="error", type="string", example="El pago excede la deuda"))
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="No autenticado",
 *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Unauthenticated."))
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Cuota no encontrada",
 *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Cuota por pagar no encontrada."))
 *     )
 * )
 */

    public function pay_payable(PayPayableRequest $request, $id)
    {
        $payable = Payable::find($id);

        if (! $payable) {
            return response()->json(['error' => "Cuota por pagar no encontrada."], 404);
        }

        $validatedData = $request->validated();

        if (! $validatedData || empty($validatedData)) {
            return response()->json(['error' => 'Datos no válidos o incompletos'], 422);
        }

        // Asignar valores con fallback a 0
        $efectivo     = $validatedData['cash'] ?? 0;
        $yape         = $validatedData['yape'] ?? 0;
        $plin         = $validatedData['plin'] ?? 0;
        $tarjeta      = $validatedData['card'] ?? 0;
        $deposito     = $validatedData['deposit'] ?? 0;
        $comentario   = $validatedData['comment'] ?? null;
        $nroOperacion = $validatedData['nroOperacion'] ?? '';

        // Calcular el total del pago
        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        if ($payable->totalDebt - $total < 0) {
            $difference = abs($payable->totalDebt - $total);
            return response()->json([
                'error' => "El pago de $total soles excede la deuda de {$payable->totalDebt} soles por $difference soles.",
            ], 422);
        }

        // Actualizar la deuda y estado de pago
        $payable->totalDebt -= $total;
        if ($payable->totalDebt == 0) {
            $payable->status = 'Pagado';
        }
        $payable->save();

        // Generar número de transacción
        $tipo         = 'CP01';
        $siguienteNum = DB::table('pay_payables')
            ->whereRaw('SUBSTRING_INDEX(number, "-", 1) = ?', [$tipo])
            ->max(DB::raw('CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)')) + 1;

        // Buscar cuenta bancaria
        $bank_account = isset($validatedData['bank_account_id']) ? BankAccount::find($validatedData['bank_account_id']) : null;

        // Crear pago
        $payable_pay = PayPayable::create([
            'number'           => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate'      => isset($validatedData['paymentDate']) ? $validatedData['paymentDate'] : null,
            'total'            => $total,
            'yape'             => $yape,
            'deposit'          => $deposito,
            'cash'             => $efectivo,
            'card'             => $tarjeta,
            'plin'             => $plin,
            'type'             => 'Pago Amortizado',
            'nroOperacion'     => $nroOperacion,
            'comment'          => $comentario,
            'payable_id'   => $payable->id,
            'bank_id'          => isset($validatedData['bank_id']) ? $validatedData['bank_id'] : optional($bank_account)->bank_id,
            'bank_movement_id' => isset($validatedData['bank_movement_id']) ? $validatedData['bank_movement_id'] : null,
            'is_detraction'    => isset($validatedData['is_detraction']) ? $validatedData['is_detraction'] : 0,
            'bank_account_id'  => isset($bank_account) ? $bank_account->id : null,
        ]);

        // Registrar movimiento bancario si hay cuenta bancaria
        $isanticipo = isset($validatedData['is_anticipo']) ? $validatedData['is_anticipo'] : 0;

        if ($isanticipo == 1) {
            $mov_anticipo = $this->bankmovementService->getBankMovementById($validatedData['bank_movement_id']);
            $mov_anticipo->update_montos_anticipo_egreso();
        } else {
            if ($bank_account) {
                $data_movement_bank = [
                    'pay_payable_id'     => isset($payable_pay->id) ? $payable_pay->id : null,
                    'bank_id'                => isset($bank_account->bank_id) ? $bank_account->bank_id : null,
                    'bank_account_id'        => isset($bank_account->id) ? $bank_account->id : null,
                    'currency'               => isset($bank_account->currency) ? $bank_account->currency : null,
                    'date_moviment'          => isset($payable_pay->paymentDate) ? $payable_pay->paymentDate : null,
                    'total_moviment'         => isset($total) ? $total : null,
                    'comment'                => isset($payable_pay->comment) ? $payable_pay->comment : null,
                    'user_created_id'        => isset(Auth::user()->id) ? Auth::user()->id : null,
                    'transaction_concept_id' => '6', //Deposito en cuenta EGRESO
                    'person_id'              => isset($payable_pay->payable->programming->proveedor->id) ? $payable_pay->payable->programming->proveedor->id : null,
                    'type_moviment'          => 'SALIDA',
                    'number_operation'       => $nroOperacion,
                ];
                $this->bankmovementService->createBankMovement($data_movement_bank);
            }

        }

        return response()->json(
            New PayPayableResource(PayPayable::find($payable_pay->id)),
            200
        );
    }

}
