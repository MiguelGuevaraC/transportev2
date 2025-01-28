<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\Installment;
use App\Models\PayInstallment;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
/**
 * Get all Installments
 *
 * @OA\Get (
 *     path="/transportev2/public/api/installment",
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
 *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transportev2/public/api/installment?page=1"),
 *             @OA\Property(property="from", type="integer", example=1),
 *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transportev2/public/api/installment?page=2"),
 *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transportev2/public/api/installment"),
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
        $status = $request->input('status') ?? '';
        $personId = $request->input('person_id') ?? '';
        $start = $request->input('start') ?? ''; // Fecha de inicio de Installment
        $end = $request->input('end') ?? ''; // Fecha de fin de Installment
        $sequentialNumber = $request->input('sequentialNumber') ?? ''; // Este campo está dentro de Moviment

        $branchOffice_id = $request->input('branchOffice_id') ?? '';
        if ($branchOffice_id && is_numeric($branchOffice_id)) {
            $branchOffice = BranchOffice::find($branchOffice_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }

        $box_id = $request->input('box_id') ?? '';
        if ($box_id != '') {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }

        // Actualizar estado de cuotas vencidas
        Installment::where('status', 'Pendiente')
            ->where('date', '<', now()) // Si la fecha actual es mayor que la fecha de vencimiento
            ->update(['status' => 'Vencido']); // Actualiza el estado a "Vencido"

        // Iniciar la consulta base
        // Iniciar la consulta base
        $query = Installment::with([
            'moviment',
            'moviment.person',
            'payInstallments',
            'payInstallments.bank'
            ,'payInstallments.movement',
        ]);

        // Filtro de rango de fechas (start y end)
 
        

// Aplicar filtros si están presentes
        if (!empty($status) && $status!='""') {
            $query->where('status', $status);
        }

        if (!empty($personId)) {
            $query->whereHas('moviment', function ($q) use ($personId) {
                $q->where('person_id', $personId);
            });
        }

        if (!empty($branchOffice_id)) {
            $query->whereHas('moviment', function ($q) use ($branchOffice_id) {
                $q->where('branchOffice_id', $branchOffice_id);
            });
        }

        if (!empty($box_id)) {
            $query->whereHas('moviment', function ($q) use ($box_id) {
                $q->where('box_id', $box_id);
            });
        }


        if (!empty($sequentialNumber)) {
            $query->whereHas('moviment', function ($q) use ($sequentialNumber) {
                $q->where('sequentialNumber', 'like', '%' . $sequentialNumber . '%');
            });
        }
        if (!empty($start)) {
            $query->where('date', '>=', $start);
        } 
        if (!empty($end)) {
            $query->where('date', '<=', $end);
        }

// Calcular la suma total de totalDebt
        $totalDebtSum = $query->sum('totalDebt');

// Obtener los parámetros de paginación desde el request
        $perPage = $request->input('per_page', 15); // Número de registros por página
        $page = $request->input('page', 1); // Página actual

// Obtener los registros filtrados con paginación
        $list = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        
        // Gets raw SQL from $query using `toSql` and `getBindings` and combines their results with `vsprintf`
        $rawSql = vsprintf(str_replace(['?'], ['\'%s\''], $query->toSql()), $query->getBindings());
        //error_log($rawSql);
        
        // Formatear la respuesta en el formato deseado
        return response()->json([
            'data' => [
                'total' => $list->total(),
                'data' => $list->items(),
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(),
                'pagination' => $list->perPage(), // Campo para el tamaño de la paginación
                'first_page_url' => $list->url(1),
                'from' => $list->firstItem(),
                'next_page_url' => $list->nextPageUrl(),
                'path' => $list->path(),
                'prev_page_url' => $list->previousPageUrl(),
                'to' => $list->lastItem(),
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

    public function payInstallment(Request $request, $id)
    {
        $installment = Installment::find($id);
        if (!$installment) {
            return response()->json(['error' => "Installment No Encontrado"], 422);
        }
        $validator = validator()->make($request->all(), [

            'paymentDate' => 'required|date',
            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'installment_id' => 'required|exists:installments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;
        $comentario = $request->input('comment') ?? 0;
        $nroOperacion = $request->input('nroOperacion') ?? '';

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        if ($installment->totalDebt - $total < 0) {
            $difference = abs($installment->totalDebt - $total);
            return response()->json([
                'error' => "Se encontró un total de $total soles, pero la deuda es de {$installment->totalDebt} soles. Hay $difference soles de más.",
            ], 422);
        }

        $installment->totalDebt = $installment->totalDebt - $total;

        $installment->save();

        if ($installment->totalDebt == 0) {
            $installment->status = 'Pagado';
            $installment->save();
        }

        $tipo = 'CC01';
        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
             FROM pay_installments
             WHERE SUBSTRING_INDEX(number, "-", 1) = ?',
            [$tipo]
        );

        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

        $data = [

            'number' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate' => $request->input('paymentDate'),
            'total' => $total ?? 0,
            'yape' => $yape,
            'deposit' => $deposito,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,
            'type' => 'Pago Amortizado',

            'nroOperacion' => $nroOperacion,
            'comment' => $comentario,
            'installment_id' => $installment->id,
            'bank_id' => $request->input('bank_id'),
            'is_detraction' => $request->input('is_detraction'),

        ];

        $installmentPay = PayInstallment::create($data);

        $moviment = $installmentPay->installment->moviment;
        $installments = $moviment->installments;

        $allPaid = $installments->every(function ($installment) {
            return $installment->status === 'Pagado';
        });
        if ($allPaid) {
            $moviment->status = 'Pagado';
            $moviment->updateSaldo();
            $moviment->save();
        }
        $moviment->updateSaldo();
        $moviment->save();

        $installmentPay = PayInstallment::with(
            ['installment', 'bank'])
            ->find($installmentPay->id);

        return response()->json($installmentPay, 200);
    }

/**
 * @OA\Post(
 *     path="/transportev2/public/api/payMasivo/{id}",
 *     summary="Process bulk payments for a person",
 *     tags={"Installment"},
 *     description="Processes payments for a person identified by ID, updating pending installments and creating payment records.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the person for whom payments are being processed",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="paymentDate", type="string", format="date", example="2024-09-01"),
 *             @OA\Property(property="totalPago", type="number", format="float", example=100.00),
 *             @OA\Property(property="comment", type="string", example="Pago parcial")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Payments processed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Pagos procesados exitosamente.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation or processing error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="message", type="string", example="El monto total debe ser mayor a cero."),
 *                 @OA\Property(property="details", type="string", example="El monto total del pago es menor que el monto total de las cuotas pendientes.")
 *             )
 *         )
 *     )
 * )
 */

    public function payMasivo(Request $request, $id)
    {
        $person = Person::find($id);
        if (!$person) {
            return response()->json(['error' => "Persona No Encontrada"], 422);
        }

        $validator = validator()->make($request->all(), [
            'paymentDate' => 'required|date',
            'totalPago' => 'nullable|numeric',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;
        $comentario = $request->input('comment') ?? '';
        $nroOperacion = $request->input('nroOperacion') ?? '';

        $totalPago = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $totalPago = $request->input('totalPago');
        if ($totalPago <= 0) {
            return response()->json(['error' => 'El monto total debe ser mayor a cero.'], 422);
        }

        $moviments = $person->movimentsVenta; // Obtener todos los movimientos de venta
        $installments = $moviments->flatMap(function ($moviment) {
            return $moviment->installmentPendientes;
        }); // Obtener todas las cuotas pendientes a partir de los movimientos de venta

        $totalDeuda = $installments->sum('totalDebt'); // Sumar todas las deudas

        if ($totalPago > $totalDeuda) {
            return response()->json(['error' => 'El monto total del pago es menor que el monto total de las cuotas pendientes.'], 422);
        }

        $remainingAmount = $totalPago;
        $payInstallmentIds = [];
        foreach ($installments as $installment) {
            if ($remainingAmount <= 0) {
                break;
            }

            $amountToPay = min($remainingAmount, $installment->totalDebt);
            $remainingAmount -= $amountToPay;

            // Actualizar la deuda de la cuota
            $installment->totalDebt -= $amountToPay;
            if ($installment->totalDebt <= 0) {
                $installment->totalDebt = 0;
                $installment->status = 'Pagado';
            }
            $installment->save();

            // Crear el registro del pago
            $tipo = 'CC01';
            $resultado = DB::select(
                'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
             FROM pay_installments
             WHERE SUBSTRING_INDEX(number, "-", 1) = ?',
                [$tipo]
            );

            $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

            $data = [
                'number' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentDate' => $request->input('paymentDate'),
                'total' => $amountToPay,
                'yape' => 0,
                'nroOperacion' => $nroOperacion,
                'deposit' => 0,
                'cash' => 0,
                'card' => 0,
                'plin' => 0,
                'comment' => $comentario,
                'type' => 'Pago Masivo',
                'installment_id' => $installment->id,
                'bank_id' => $request->input('bank_id'),
                'is_detraction' => $request->input('is_detraction'),
            ];

            $payInst = PayInstallment::create($data);
            $payInstallmentIds[] = $payInst->id;
        }

        $moviments = $person->movimentsVenta; // Obtener todos los movimientos de venta
        foreach ($moviments as $moviment) {
            $installments = $moviment->installments;

            $allPaid = $installments->every(function ($installment) {
                return $installment->status === 'Pagado';
            });
            if ($allPaid) {
                $moviment->status = 'Pagado';

                $moviment->updateSaldo();
                $moviment->save();
            }
            $moviment->updateSaldo();
        }

        $payInstallments = PayInstallment::with(['installment',
            'installment.moviment', 'installment.moviment.person'])
            ->whereIn('id', $payInstallmentIds)
            ->get();

        // Retornar solo los IDs de los PayInstallment creados y los installments actualizados
        return response()->json([
            'payInstallments' => $payInstallments,
        ], 200);
    }

    public function show($id)
    {
        $person = Person::find($id);
        if (!$person) {
            return response()->json(['error' => "Persona No Encontrada"], 422);
        }

        $moviments = $person->movimentsVenta()->with(['person',
            'installments', 'installments.payInstallments'])->get(); // Obtener todos los movimientos de venta con las relaciones

        return response()->json([$moviments], 200);
    }

}
