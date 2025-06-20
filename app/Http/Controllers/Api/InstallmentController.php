<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstallmentRequest\PayInstallmentRequest;
use App\Models\BankAccount;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\PayInstallment;
use App\Models\Person;
use App\Services\BankMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
    protected $bankmovementService;

    public function __construct(BankMovementService $BankMovementService)
    {
        $this->bankmovementService = $BankMovementService;
    }
/**
 * Get all Installments
 *
 * @OA\Get (
 *     path="/transporte/public/api/installment",
 *     summary="Get all Installments",
 *     tags={"Installment"},
 *     description="Retrieve all installments with optional filters for 'status' and 'person_id'. Includes related 'moviment' and 'payInstallments' information. Results are paginated.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter installments by status",
 *         required=false,
 *         @OA\Schema(type="string", example="active")
 *     ),
 *
 *     @OA\Parameter(
 *         name="person_id",
 *         in="query",
 *         description="Filter installments by the ID of the person associated with the movement",
 *         required=false,
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of Installments",
 *         @OA\JsonContent(
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Installment")),
 *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/installment?page=1"),
 *             @OA\Property(property="from", type="integer", example=1),
 *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/installment?page=2"),
 *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/installment"),
 *             @OA\Property(property="per_page", type="integer", example=15),
 *             @OA\Property(property="prev_page_url", type="string", example="null"),
 *             @OA\Property(property="to", type="integer", example=15)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message", type="string", example="Unauthenticated"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message", type="string", example="Internal Server Error"
 *             )
 *         )
 *     )
 * )
 */

    public function index(Request $request)
    {
        $status           = $request->input('status') ?? '';
        $personId         = $request->input('person_id') ?? '';
        $start            = $request->input('start') ?? '';            // Fecha de inicio de Installment
        $end              = $request->input('end') ?? '';              // Fecha de fin de Installment
        $sequentialNumber = $request->input('sequentialNumber') ?? ''; // Este campo está dentro de Moviment

        $branchOffice_id = $request->input('branchOffice_id') ?? '';
        if ($branchOffice_id && is_numeric($branchOffice_id)) {
            $branchOffice = BranchOffice::find($branchOffice_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }

        $box_id = $request->input('box_id') ?? '';
        if ($box_id != '') {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }

        // Actualizar estado de cuotas vencidas
        Installment::where('status', 'Pendiente')
            ->where('date', '<', now())        // Si la fecha actual es mayor que la fecha de vencimiento
            ->update(['status' => 'Vencido']); // Actualiza el estado a "Vencido"

        // Iniciar la consulta base
        // Iniciar la consulta base
        $query = Installment::with([
            'moviment',
            'moviment.creditNote',
            'moviment.person',
            'moviment.person.anticipos_cliente_con_saldo',
            'payInstallments',
            'payInstallments.bank',
            'payInstallments.latest_bank_movement_anticipo',  // Relación real
            'payInstallments.latest_bank_movement_transaction', // Relación real
            'payInstallments.bank_account',
            'payInstallments.movement'

        ]);

        // Filtro de rango de fechas (start y end)

// Aplicar filtros si están presentes
        if (! empty($status) && $status != '""') {
            $query->where('status', $status);
        }

        if (! empty($personId)) {
            $query->whereHas('moviment', function ($q) use ($personId) {
                $q->where('person_id', $personId);
            });
        }

        if (! empty($branchOffice_id)) {
            $query->whereHas('moviment', function ($q) use ($branchOffice_id) {
                $q->where('branchOffice_id', $branchOffice_id);
            });
        }

        if (! empty($box_id)) {
            $query->whereHas('moviment', function ($q) use ($box_id) {
                $q->where('box_id', $box_id);
            });
        }

        if (! empty($sequentialNumber)) {
            $query->whereHas('moviment', function ($q) use ($sequentialNumber) {
                $q->where('sequentialNumber', 'like', '%' . $sequentialNumber . '%');
            });
        }
         if (! empty($start)) {
            // $query->where('date', '>=', $start);
             $query->whereHas('moviment', function ($q) use ($start) {
                $q->where('paymentDate', '>=',$start);
            });
        }
        if (! empty($end)) {
            // $query->where('date', '<=', $end);
              $query->whereHas('moviment', function ($q) use ($end) {
                $q->where('paymentDate','<=', $end);
            });
        }
// Calcular la suma total de totalDebt
        $totalDebtSum = $query->sum('totalDebt');

// Obtener los parámetros de paginación desde el request
        $perPage = $request->input('per_page', 15); // Número de registros por página
        $page    = $request->input('page', 1);      // Página actual

// Obtener los registros filtrados con paginación
        $list = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        $list->getCollection()->each(function ($installment) {
            if ($installment->moviment && $installment->moviment->person) {
                $installment->moviment->person->updateAnticipadoAmountClient();
            }
        });

        // Gets raw SQL from $query using `toSql` and `getBindings` and combines their results with `vsprintf`
        $rawSql = vsprintf(str_replace(['?'], ['\'%s\''], $query->toSql()), $query->getBindings());
        //error_log($rawSql);

        // Formatear la respuesta en el formato deseado
        return response()->json([
            'data'         => [
                'total'          => $list->total(),
                'data'           => $list->items(),
                'current_page'   => $list->currentPage(),
                'last_page'      => $list->lastPage(),
                'per_page'       => $list->perPage(),
                'pagination'     => $list->perPage(), // Campo para el tamaño de la paginación
                'first_page_url' => $list->url(1),
                'from'           => $list->firstItem(),
                'next_page_url'  => $list->nextPageUrl(),
                'path'           => $list->path(),
                'prev_page_url'  => $list->previousPageUrl(),
                'to'             => $list->lastItem(),
            ],
            'totalDebtSum' => $totalDebtSum,
        ], 200);
    }

/**
 * @OA\Put(
 *     path="/installment/{id}/pay",
 *     summary="Pay an installment",
 *     tags={"Installment"},
 *     description="Process a payment for a specific installment",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the installment to pay",
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Payment details",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="paymentDate",
 *                     type="string",
 *                     format="date",
 *                     description="Date of payment",
 *                     example="2024-09-02"
 *                 ),
 *                 @OA\Property(
 *                     property="yape",
 *                     type="number",
 *                     format="float",
 *                     description="Amount paid via Yape",
 *                     nullable=true,
 *                     example=20.00
 *                 ),
 *                 @OA\Property(
 *                     property="deposit",
 *                     type="number",
 *                     format="float",
 *                     description="Amount paid via deposit",
 *                     nullable=true,
 *                     example=30.00
 *                 ),
 *                 @OA\Property(
 *                     property="cash",
 *                     type="number",
 *                     format="float",
 *                     description="Amount paid in cash",
 *                     nullable=true,
 *                     example=50.00
 *                 ),
 *                 @OA\Property(
 *                     property="card",
 *                     type="number",
 *                     format="float",
 *                     description="Amount paid via card",
 *                     nullable=true,
 *                     example=0.50
 *                 ),
 *                 @OA\Property(
 *                     property="plin",
 *                     type="number",
 *                     format="float",
 *                     description="Amount paid via Plin",
 *                     nullable=true,
 *                     example=50.00
 *                 ),
 *                 @OA\Property(
 *                     property="comment",
 *                     type="string",
 *                     description="Comment on the payment",
 *                     nullable=true,
 *                     example="Pago parcial"
 *                 ),
 *                 @OA\Property(
 *                     property="installment_id",
 *                     type="integer",
 *                     description="ID of the installment being paid",
 *                     example=1
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payment processed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="number", type="string", example="CC01-00000001"),
 *             @OA\Property(property="paymentDate", type="string", example="2024-09-02"),
 *             @OA\Property(property="total", type="number", format="float", example=150.50),
 *             @OA\Property(property="yape", type="number", format="float", example=20.00),
 *             @OA\Property(property="deposit", type="number", format="float", example=30.00),
 *             @OA\Property(property="cash", type="number", format="float", example=50.00),
 *             @OA\Property(property="card", type="number", format="float", example=0.50),
 *             @OA\Property(property="plin", type="number", format="float", example=50.00),
 *             @OA\Property(property="comment", type="string", example="Pago parcial"),
 *             @OA\Property(property="installment_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Installment No Encontrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

    public function payInstallment(PayInstallmentRequest $request, $id)
    {
        $installment = Installment::find($id);

        if (! $installment) {
            return response()->json(['error' => "Cuota no encontrada."], 404);
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

        if ($installment->totalDebt - $total < 0) {
            $difference = abs($installment->totalDebt - $total);
            return response()->json([
                'error' => "El pago de $total soles excede la deuda de {$installment->totalDebt} soles por $difference soles.",
            ], 422);
        }

        // Actualizar la deuda y estado de pago
        $installment->totalDebt -= $total;
        if ($installment->totalDebt == 0) {
            $installment->status = 'Pagado';
        }
        $installment->save();

        // Generar número de transacción
        $tipo         = 'CC01';
        $siguienteNum = DB::table('pay_installments')
            ->whereRaw('SUBSTRING_INDEX(number, "-", 1) = ?', [$tipo])
            ->max(DB::raw('CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)')) + 1;

        // Buscar cuenta bancaria
        $bank_account = isset($validatedData['bank_account_id']) ? BankAccount::find($validatedData['bank_account_id']) : null;

        // Crear pago
        $installmentPay = PayInstallment::create([
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
            'installment_id'   => $installment->id,
            'bank_id'          => isset($validatedData['bank_id']) ? $validatedData['bank_id'] : optional($bank_account)->bank_id,
            'bank_movement_id' => isset($validatedData['bank_movement_id']) ? $validatedData['bank_movement_id'] : null,
            'is_detraction'    => isset($validatedData['is_detraction']) ? $validatedData['is_detraction'] : 0,
            'bank_account_id'  => isset($bank_account) ? $bank_account->id : null,
        ]);

        // Registrar movimiento bancario si hay cuenta bancaria
        $isanticipo = isset($validatedData['is_anticipo']) ? $validatedData['is_anticipo'] : 0;

        if ($isanticipo == 1) {
            $mov_anticipo = $this->bankmovementService->getBankMovementById($validatedData['bank_movement_id']);
            $mov_anticipo->update_montos_anticipo();
        } else {
            if ($bank_account) {
                $data_movement_bank = [
                    'pay_installment_id'     => isset($installmentPay->id) ? $installmentPay->id : null,
                    'bank_id'                => isset($bank_account->bank_id) ? $bank_account->bank_id : null,
                    'bank_account_id'        => isset($bank_account->id) ? $bank_account->id : null,
                    'currency'               => isset($bank_account->currency) ? $bank_account->currency : null,
                    'date_moviment'          => isset($installmentPay->paymentDate) ? $installmentPay->paymentDate : null,
                    'total_moviment'         => isset($total) ? $total : null,
                    'comment'                => isset($installmentPay->comment) ? $installmentPay->comment : null,
                    'user_created_id'        => isset(Auth::user()->id) ? Auth::user()->id : null,
                    'transaction_concept_id' => '5', //Deposito en cuenta
                    'person_id'              => isset($installmentPay->installment->moviment->person->id) ? $installmentPay->installment->moviment->person->id : null,
                    'type_moviment'          => 'ENTRADA',
                    'number_operation'          => $nroOperacion,
                ];
                $this->bankmovementService->createBankMovement($data_movement_bank);
            }

        }
        // Verificar si todas las cuotas del movimiento están pagadas
        $moviment = Moviment::find($installment->moviment_id);
        if ($moviment) {
            $allPaid = $moviment->installments->every(fn($i) => $i->status === 'Pagado');
            if ($allPaid) {
                $moviment->status = 'Pagado';
            }
            $moviment->updateSaldo();
            $moviment->save();
        }

        return response()->json(
            PayInstallment::with(['bank', 'latest_bank_movement', 'bank_account',
                'installment.moviment',
                'installment.moviment.creditNote',
                'installment.moviment.person',
                'installment.moviment.person.anticipos_cliente_con_saldo',
            ])->find($installmentPay->id),
            200
        );
    }

    public function show($id)
    {
        $person = Person::find($id);
        if (! $person) {
            return response()->json(['error' => "Persona No Encontrada"], 422);
        }

        $moviments = $person->movimentsVenta()->with(['person',
            'installments', 'installments.payInstallments'])->get(); // Obtener todos los movimientos de venta con las relaciones

        return response()->json([$moviments], 200);
    }

}
