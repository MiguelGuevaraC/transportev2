<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\PaymentConcept;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class MovimentController extends Controller
{

    /**
     * Get all Movimentes
     * @OA\Get (
     *     path="/transporte/public/api/moviment",
     *     tags={"Moviment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="box_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID of the box to filter the Movimentes"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active Movimentes",
     *         @OA\JsonContent(
     *             @OA\Property(property="detalle", type="object",
     *                 @OA\Property(property="MovCajaApertura", ref="#/components/schemas/MovimentRequest"),
     *                 @OA\Property(property="MovCajaCierre", ref="#/components/schemas/MovimentRequest"),
     *                 @OA\Property(property="MovCajaInternos", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MovimentRequest")),
     *                     @OA\Property(property="first_page_url", type="string", example="http://localhost/transporte/public/api/moviment?page=1"),
     *                     @OA\Property(property="from", type="integer", example=1),
     *                     @OA\Property(property="next_page_url", type="string", example="null"),
     *                     @OA\Property(property="path", type="string", example="http://localhost/transporte/public/api/moviment"),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="prev_page_url", type="string", example="null"),
     *                     @OA\Property(property="to", type="integer", example=2)
     *                 ),
     *                 @OA\Property(property="resumenCaja", type="object",
     *                     @OA\Property(property="total_ingresos", type="string", example="150.50"),
     *                     @OA\Property(property="total_egresos", type="string", example="150.50"),
     *                     @OA\Property(property="efectivo_ingresos", type="string", example="50.00"),
     *                     @OA\Property(property="efectivo_egresos", type="string", example="50.00"),
     *                     @OA\Property(property="yape_ingresos", type="string", example="20.00"),
     *                     @OA\Property(property="yape_egresos", type="string", example="20.00"),
     *                     @OA\Property(property="plin_ingresos", type="string", example="0.00"),
     *                     @OA\Property(property="plin_egresos", type="string", example="50.00"),
     *                     @OA\Property(property="tarjeta_ingresos", type="string", example="0.50"),
     *                     @OA\Property(property="tarjeta_egresos", type="string", example="0.50"),
     *                     @OA\Property(property="deposito_ingresos", type="string", example="30.00"),
     *                     @OA\Property(property="deposito_egresos", type="string", example="30.00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message", type="string", example="Unauthenticated"
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }

        $movCaja = Moviment::where('status', 'Activa')
            ->where('paymentConcept_id', 1)
            ->where('box_id', $box_id)
            ->orderBy('id', 'desc')
            ->first();

        $data = [];
        if ($movCaja) {
            $data = $this->detalleCajaAperturada($request, $movCaja->id)->original;
        }

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/moviment/{id}",
     *     summary="Get a moviment by ID",
     *     tags={"Moviment"},
     *     description="Retrieve a moviment by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the moviment to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moviment found",
     *        @OA\JsonContent(
     *              type="object",
     *              ref="#/components/schemas/MovimentRequest"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function show($id)
    {
        $object = Moviment::with(['paymentConcept', 'person', 'user.worker.person'])->find($id);

        if (! $object) {
            return response()->json(['message' => 'Moviment not found'], 422);
        }

        return response()->json($object, 200);
    }

/**
 * @OA\Get(
 *     path="/transporte/public/api/moviment/last/{idBox}",
 *     summary="Get the last moviment with paymentConcept_id = 2",
 *     tags={"Moviment"},
 *     description="Retrieve the last moviment with paymentConcept_id = 2 for a specific box",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="idBox",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="ID of the box"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Moviment found",
 *         @OA\JsonContent(
 *              type="object",
 *              ref="#/components/schemas/MovimentRequest"
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Moviment not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     ),
 * )
 */
    public function showLastMovPayment($idBox)
    {
        $box = Box::find($idBox);
        if (! $box) {
            return response()->json(['message' => 'Box not found'], 422);
        }
        $object = Moviment::with(['paymentConcept', 'person', 'user.worker.person'])
            ->where('paymentConcept_id', 2)
            ->where('box_id', $idBox)
            ->orderBy('created_at', 'desc')
            ->where('movType', 'Caja')
            ->first();

        if (! $object) {
            return response()->json(null, 200);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transporte/public/api/moviment/{id}",
     *     summary="Delete a moviment",
     *     tags={"moviment"},
     *     description="Delete a moviment by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the moviment to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model_functional deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *        @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function destroy($id)
    {
        $object = Moviment::find($id);
        if (! $object) {
            return response()->json(['message' => 'Moviment not found'], 404); // Cambiado a 404 para mejor claridad
        }

                           // Aquí puedes agregar lógica para eliminar o procesar el objeto
                           // Si el objetivo es eliminar el registro:
        $object->delete(); // Cambia esto si se necesita otra lógica
        return response()->json(['message' => 'Moviment deleted successfully'], 200);
    }

    public function destroy_venta_ticket($id)
    {
        $object = Moviment::find($id);

        if (! $object) {
            return response()->json(['message' => 'Movimiento no encontrado'], 404);
        }

        // Validar que sea un ticket (sequentialNumber empieza con "T")
        if (! Str::startsWith($object->sequentialNumber, 'T')) {
            return response()->json(['message' => 'No es un ticket'], 400);
        }

        $moviments = Moviment::where('sequentialNumber', $object->sequentialNumber)->get();

        foreach ($moviments as $mov) {
            $mov->delete();
        }

        return response()->json(['message' => 'Ticket Eliminado Exitosamente'], 200);
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/moviment",
     *     summary="Store a new moviment",
     *     tags={"Moviment"},
     *     description="Create a new moviment",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Moviment data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="paymentDate",
     *                 type="string",
     *                 format="date-time",
     *                 description="Fecha de pago",
     *                 nullable=true,
     *                 example="2023-06-17"
     *             ),
     *             @OA\Property(
     *                 property="paymentConcept_id",
     *                 type="integer",
     *                 description="ID del concepto de pago",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="yape",
     *                 type="number",
     *                 format="float",
     *                 description="Pago por Yape",
     *                 nullable=true,
     *                 example=20.00
     *             ),
     *             @OA\Property(
     *                 property="deposit",
     *                 type="number",
     *                 format="float",
     *                 description="Depósito",
     *                 nullable=true,
     *                 example=30.00
     *             ),
     *             @OA\Property(
     *                 property="cash",
     *                 type="number",
     *                 format="float",
     *                 description="Efectivo",
     *                 nullable=true,
     *                 example=50.00
     *             ),
     *             @OA\Property(
     *                 property="card",
     *                 type="number",
     *                 format="float",
     *                 description="Pago por tarjeta",
     *                 nullable=true,
     *                 example=0.50
     *             ),
     *             @OA\Property(
     *                 property="plin",
     *                 type="number",
     *                 format="float",
     *                 description="Pago por Plin",
     *                 nullable=true,
     *                 example=50.00
     *             ),
     *             @OA\Property(
     *                 property="comment",
     *                 type="string",
     *                 description="Comentario",
     *                 nullable=true,
     *                 example="Pago parcial"
     *             ),
     *             @OA\Property(
     *                 property="typeDocument",
     *                 type="string",
     *                 description="Tipo de documento",
     *                 nullable=true,
     *                 example="F"
     *             ),
     *             @OA\Property(
     *                 property="typePayment",
     *                 type="string",
     *                 description="Contado / Crédito",
     *                 nullable=true,
     *                 example="Contado"
     *             ),
     *             @OA\Property(
     *                 property="typeSale",
     *                 type="string",
     *                 description="Normal / Detracción",
     *                 nullable=true,
     *                 example="Estandar"
     *             ),
     *             @OA\Property(
     *                 property="programming_id",
     *                 type="integer",
     *                 description="ID de programación",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="box_id",
     *                 type="integer",
     *                 description="ID de la caja",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="branchOffice_id",
     *                 type="integer",
     *                 description="ID de la sucursal",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="reception_id",
     *                 type="integer",
     *                 description="ID de la recepción",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="person_id",
     *                 type="integer",
     *                 description="ID de la persona",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="installment",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string", format="date", example="2023-06-17"),
     *                     @OA\Property(property="total", type="number", format="float", example="100")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moviment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MovimentRequest")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some fields are required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'paymentDate'       => 'required|date',

            'yape'              => 'nullable|numeric',
            'deposit'           => 'nullable|numeric',
            'cash'              => 'nullable|numeric',
            'card'              => 'nullable|numeric',
            'plin'              => 'nullable|numeric',
            'comment'           => 'nullable|string',

            'typeDocument'      => 'nullable|string',
            'typePayment'       => 'nullable|string',
            'typeSale'          => 'nullable|string',

            'programming_id'    => 'nullable|exists:programmings,id',
            'paymentConcept_id' => 'required|exists:payment_concepts,id',
            'box_id'            => 'required|exists:boxes,id',
            'branchOffice_id'   => 'required|exists:branch_offices,id',
            'reception_id'      => 'nullable|exists:receptions,id',
            'person_id'         => 'required|exists:people,id',
            'installments'      => 'nullable|array',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario Sin caja Asginada'], 422);
        }

        $paymentConcept = PaymentConcept::find($request->input('paymentConcept_id'));

        if ($paymentConcept->type == 'Egreso') {
            $contraseña    = $request->input('password');
            $userPermitidos = User::whereIn('id', [1, 9])->get(); // Cambiado to whereIn

            if ($userPermitidos->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron usuarios permitidos.',
                ], 404);
            }

            $passwordCorrect = false;

            foreach ($userPermitidos as $userAdmin) {
                if (Hash::check($contraseña, $userAdmin->password)) {
                    $passwordCorrect = true; // La contraseña coincide con uno de los usuarios
                    break;                   // Salir del bucle si se encuentra una coincidencia
                }
            }

            // Si la contraseña no coincide con ninguno de los usuarios
            if (! $passwordCorrect) {
                return response()->json([
                    'message' => 'Las contraseñas no coinciden.',
                ], 409);
            }

            // Aquí puedes continuar con la lógica que necesites después de la verificación
        }

        $box_id = $request->input('box_id');

        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }

        $box = Box::find($request->input('box_id'));
        if ($box->status != 'Activa') {
            return response()->json(['error' => 'Caja No está Aperturada'], 422);
        }
        $branch_office_id = $request->input('branchOffice_id');

        $movCaja = Moviment::where('status', 'Activa')
            ->where('paymentConcept_id', 1)
            ->where('branchOffice_id', $branch_office_id)->first();
        $cadena = '';
        $estado = 0;
        if (! $movCaja) {
            return response()->json(['error' => 'Caja No está Aperturada'], 422);

        }

        $tipo = 'M' . str_pad($box_id, 3, '0', STR_PAD_LEFT);

        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(correlative, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
             FROM moviments
             WHERE movType = "Caja"
             AND SUBSTRING_INDEX(correlative, "-", 1) = ?',
            [$tipo]
        );

        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

        $efectivo = $request->input('cash') ?? 0;
        $yape     = $request->input('yape') ?? 0;
        $plin     = $request->input('plin') ?? 0;
        $tarjeta  = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        $data = [
            'correlative'       => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'sequentialNumber'  => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate'       => $request->input('paymentDate'),
            'total'             => $total ?? 0,
            'yape'              => $request->input('yape') ?? 0,
            'deposit'           => $request->input('deposit') ?? 0,
            'cash'              => $request->input('cash') ?? 0,
            'card'              => $request->input('card') ?? 0,
            'plin'              => $request->input('plin') ?? 0,

            'comment'           => $request->input('comment') ?? '-',
            'typeDocument'      => $request->input('typeDocument') ?? '-',
            'movType'           => 'Caja',
            'typeCaja'          => 'nullable|string',
            'operationNumber'   => $request->input('operationNumber'),
            'typePayment'       => $request->input('typePayment') ?? null,
            'typeSale'          => $request->input('typeSale') ?? '-',
            'status'            => 'Generada',
            'programming_id'    => $request->input('programming_id'),
            'paymentConcept_id' => $request->input('paymentConcept_id'),
            'branchOffice_id'   => $request->input('branchOffice_id'),
            'reception_id'      => $request->input('reception_id'),
            'person_id'         => $request->input('person_id'),
            'user_id'           => auth()->id(),
            'box_id'            => $request->input('box_id'),
        ];

        $object = Moviment::create($data);

        $installments = $request->input('installments') ?? [];
        if ($installments != []) {
            foreach ($installments as $installment) {

                $data = [
                    'date'        => $installment['date'],
                    'total'       => $installment['total'],
                    'totalDebt'   => $installment['total'],

                    'moviment_id' => $object->id,
                ];
                Installment::create($data);
            }
        }

        $object = Moviment::with(['branchOffice', 'paymentConcept', 'box',
            'person', 'user.worker.person', 'installments', 'movVenta'])->find($object->id);

        $object->detalle = $this->detalleCajaAperturada($request, $movCaja->id)->original;

        return response()->json($object, 200);

    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/movimentAperturaCierre",
     *     summary="Store a new moviment",
     *     tags={"Moviment"},
     *     description="Create a new moviment Apertura/Cierre",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Moviment data",
     *         @OA\JsonContent(

     *             @OA\Property(
     *                 property="paymentDate",
     *                 type="string",
     *                 format="date-time",
     *                 description="Fecha de pago",
     *                 nullable=true,
     *                 example="2023-06-17"
     *             ),
     *             @OA\Property(
     *                 property="paymentConcept_id",
     *                 type="integer",
     *                 description="ID del concepto de pago",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="yape",
     *                 type="number",
     *                 format="decimal",
     *                 description="Pago por Yape",
     *                 nullable=true,
     *                 example=20.00
     *             ),
     *             @OA\Property(
     *                 property="deposit",
     *                 type="number",
     *                 format="decimal",
     *                 description="Depósito",
     *                 nullable=true,
     *                 example=30.00
     *             ),
     *             @OA\Property(
     *                 property="cash",
     *                 type="number",
     *                 format="decimal",
     *                 description="Efectivo",
     *                 nullable=true,
     *                 example=50.00
     *             ),
     *             @OA\Property(
     *                 property="plin",
     *                 type="number",
     *                 format="decimal",
     *                 description="Efectivo",
     *                 nullable=true,
     *                 example=50.00
     *             ),
     *             @OA\Property(
     *                 property="card",
     *                 type="number",
     *                 format="decimal",
     *                 description="Pago por tarjeta",
     *                 nullable=true,
     *                 example=0.50
     *             ),
     *             @OA\Property(
     *                 property="comment",
     *                 type="string",
     *                 description="Comentario",
     *                 nullable=true,
     *                 example="Pago parcial"
     *             ),

     *             @OA\Property(
     *                 property="box_id",
     *                 type="integer",
     *                 description="ID de la caja",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="branchOffice_id",
     *                 type="integer",
     *                 description="ID de la sucursal",
     *                 nullable=true,
     *                 example=1
     *             ),

     *             @OA\Property(
     *                 property="person_id",
     *                 type="integer",
     *                 description="ID de la persona",
     *                 nullable=true,
     *                 example=1
     *             ),

     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moviment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/MovimentRequest")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Some fields are required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function aperturaCierre(Request $request)
    {

        $validator = validator()->make($request->all(), [
            'paymentDate'       => 'required|date',
            'paymentConcept_id' => 'required|in:1,2|exists:payment_concepts,id',
            'yape'              => 'nullable|numeric',
            'deposit'           => 'nullable|numeric',
            'cash'              => 'nullable|numeric',
            'plin'              => 'nullable|numeric',
            'card'              => 'nullable|numeric',
            'comment'           => 'nullable|string',
            'saldo'             => 'nullable|numeric',

            'box_id'            => 'required|exists:boxes,id',
            'branchOffice_id'   => 'required|exists:branch_offices,id',

            'person_id'         => 'required|exists:people,id',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }
        $branch_office_id = $request->input('branchOffice_id');

        if ($request->input('paymentConcept_id') == 1) {
            $letra       = 'A';
            $status      = 'Activa';
            $box         = Box::find($box_id);
            $box->status = 'Activa';
            $box->save();
            $typeDocument = 'Ingreso';
        } else if (($request->input('paymentConcept_id') == 2)) {
            $letra  = 'C';
            $status = 'Inactiva';

            $box         = Box::find($box_id);
            $box->status = 'Inactiva';
            $box->save();

            $movCaja = Moviment::where('status', 'Activa')
                ->where('paymentConcept_id', 1)
                ->where('box_id', $box_id)
                ->first();

            $movCaja->status = 'Inactiva';
            $movCaja->save();
            $typeDocument = 'Egreso';
        }

        // $tipo = $letra . str_pad($branch_office_id, 3, '0', STR_PAD_LEFT);
        // $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);

        // $tipo = $letra . str_pad($box_id, 3, '0', STR_PAD_LEFT) * 100;
        $tipo = $letra . str_pad($box_id, 3, '0', STR_PAD_LEFT);

        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(sequentialNumber,
            LOCATE("-", sequentialNumber) + 1) AS UNSIGNED)), 0) + 1 AS siguienteNum
            FROM moviments
            WHERE SUBSTRING(sequentialNumber, 1, 4) = ?',
            [$tipo]
        );

        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

        $efectivo = $request->input('cash') ?? 0;
        $yape     = $request->input('yape') ?? 0;
        $plin     = $request->input('plin') ?? 0;
        $tarjeta  = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        $data = [
            'correlative'       => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'sequentialNumber'  => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate'       => $request->input('paymentDate'),
            'total'             => $total ?? 0,
            'yape'              => $request->input('yape') ?? 0,
            'deposit'           => $request->input('deposit') ?? 0,
            'cash'              => $request->input('cash') ?? 0,
            'card'              => $request->input('card') ?? 0,
            'plin'              => $request->input('plin') ?? 0,
            'comment'           => $request->input('comment') ?? '-',
            'saldo'             => $total,
            'status'            => $status,
            'typeDocument'      => $typeDocument,
            'typeCaja'          => 'nullable|string',
            'operationNumber'   => $request->input('operationNumber'),
            'paymentConcept_id' => $request->input('paymentConcept_id'),
            'branchOffice_id'   => $branch_office_id,
            'person_id'         => $request->input('person_id'),
            'user_id'           => auth()->id(),
            'box_id'            => $box_id,
        ];

        $object = Moviment::create($data);

        $object = Moviment::with(['branchOffice', 'paymentConcept', 'box',
            'person', 'user.worker.person'])->find($object->id);
        return response()->json($object, 200);

    }

    public function detalleCajaAperturada(Request $request, $id)
    {
        $box_id = $request->input('box_id');

        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box    = Box::find($box_id);
        }

        $movCajaAperturada = Moviment::where('id', $id)
            ->where('paymentConcept_id', 1)
            ->where('box_id', $box_id)
            ->first();

        // $box_id = $movCajaAperturada->box_id;
        if (! $movCajaAperturada) {
            return response()->json([
                "message" => "Movimiento de Apertura no encontrado",
            ], 404);
        }

        $movCajaCierre = Moviment::where('id', '>', $movCajaAperturada->id)
        // ->where('branchOffice_id', $movCajaAperturada->branchOffice_id)
            ->where('paymentConcept_id', 2)
            ->where('box_id', $box_id)
            ->orderBy('id', 'asc')->first();

        if ($movCajaCierre == null) {
            //CAJA ACTIVA
            $movimientosCaja = Moviment::select(['*', DB::raw('(SELECT obtenerFormaPagoPorCaja(moviments.id)) AS formaPago')])
                ->where('id', '>=', $movCajaAperturada->id)
            // ->where('branchOffice_id', $movCajaAperturada->branchOffice_id)
                ->where('box_id', $box_id)
                ->orderBy('id', 'desc')
                ->where('movType', 'Caja')
                ->with(['paymentConcept', 'person', 'user.worker.person', 'installments'])
                ->simplePaginate();

            $resumenCaja = Moviment::selectRaw('
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.total ELSE 0 END), 0.00) as total_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.total ELSE 0 END), 0.00) as total_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_egresos')
                ->leftJoin('payment_concepts as cp', 'moviments.paymentConcept_id', '=', 'cp.id')
                ->where('moviments.id', '>=', $movCajaAperturada->id)
                ->where('moviments.box_id', $box_id)
            // ->where('moviments.branchOffice_id', $movCajaAperturada->branchOffice_id)
                ->first();

            $movCajaCierreArray = null;
        } else {
            $movimientosCaja = Moviment::select(['*', DB::raw('(SELECT obtenerFormaPagoPorCaja(moviments.id)) AS formaPago')])
                ->where('id', '>=', $movCajaAperturada->id)
            // ->where('branchOffice_id', $movCajaAperturada->branchOffice_id)
                ->where('id', '<', $movCajaCierre->id)
                ->where('box_id', $box_id)
                ->where('movType', 'Caja')
                ->orderBy('id', 'desc')
                ->with(['paymentConcept', 'person', 'user.worker.person', 'installments'])
                ->simplePaginate();

            $resumenCaja = Moviment::selectRaw('
                COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.total ELSE 0 END), 0.00) as total_ingresos,
                COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.total ELSE 0 END), 0.00) as total_egresos,
                COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_ingresos,
                COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_egresos,
                COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_ingresos,
                COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.yape ELSE 0 END), 0.00) as yape_egresos,
                COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_ingresos,
                COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.plin ELSE 0 END), 0.00) as plin_egresos,
                COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_ingresos,
                COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_egresos,
                COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_ingresos,
                COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.deposit ELSE 0 END), 0.00) as deposito_egresos')
                ->leftJoin('payment_concepts as cp', 'moviments.paymentConcept_id', '=', 'cp.id')

                ->where('moviments.id', '>=', $movCajaAperturada->id)
                ->where('moviments.id', '<', $movCajaCierre->id)
                ->where('moviments.box_id', $box_id)
            // ->where('moviments.branchOffice_id', $movCajaAperturada->branchOffice_id)
                ->first();

            $forma_pago = DB::select('SELECT obtenerFormaPagoPorCaja(:id) AS forma_pago', ['id' => $movCajaCierre->id]);

        }

        return response()->json([

            'MovCajaApertura' => $movCajaAperturada,
            'MovCajaCierre'   => $movCajaCierre,
            'MovCajaInternos' => $movimientosCaja,

            "resumenCaja"     => $resumenCaja ?? null,
        ]);

    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/validateBox/{id}",
     *     summary="Get a Moviment",
     *     tags={"Moviment"},
     *     description="Get a Moviment by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the Moviment to Get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moviment retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", description="Moviment details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict: Existing open box",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Existe una caja aperturada, aperturada el día: YYYY-MM-DD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function validateBox(Request $request)
    {
        $boxId = $request->input('box_id');
        // Obtener la caja aperturada
        $movCaja = Moviment::where('status', 'Activa')
            ->where('paymentConcept_id', 1)
            ->where('box_id', $boxId)
            ->first();

        if ($movCaja) {
            $fechaApertura = Carbon::parse($movCaja->created_at)->format('Y-m-d');

            $fechaActual = Carbon::now()->format('Y-m-d');

            if ($fechaApertura !== $fechaActual) {
                return response()->json([
                    'message' => "Existe una caja aperturada, aperturada el día: $fechaApertura",
                ], 409);
            }
        }

                                          // Continúa con el procesamiento si no hay problemas
        return response()->json([], 200); // Respuesta opcional
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/showAperturaMovements",
     *     summary="Listado de Aperturas",
     *     tags={"Moviment"},
     *     description="Por cada apertura, muestra su reporte con un filtro opcional por fechas basado en el campo created_at.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar los movimientos (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar los movimientos (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Movimientos obtenidos con éxito.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MovimentRequest"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Movements not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function showAperturaMovements(Request $request)
    {
        $box = Box::find($request->input('box_id'));
        if (! $box) {
            return response()->json(['message' => 'Box Not Found'], 404);
        }

        $branchOfficeId = $box->branchOffice_id;

        // Construir la consulta base
        $query = Moviment::with(['paymentConcept', 'person', 'user.worker.person'])
            ->where('paymentConcept_id', 1)  // Filtrar solo aperturas
            ->orderBy('created_at', 'desc'); // Orden inverso (más recientes primero)

        // Filtrar por fechas y caja
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }
        if ($request->filled('box_id')) {
            $query->where('box_id', $request->input('box_id'));
        }
        if ($branchOfficeId) {
            $query->where('branchOffice_id', $branchOfficeId);
        }

        // Obtener movimientos paginados
        $page      = $request->input('page', 1);
        $perPage   = $request->input('per_page', 50);
        $aperturas = $query->paginate($perPage, ['*'], 'page', $page);

        // Convertir los datos paginados a una colección y formatearlos
        $formattedMovements = collect($aperturas->items())->map(function ($apertura) use ($branchOfficeId) {
                                                              // Buscar el cierre asociado
            $cierre = Moviment::where('paymentConcept_id', 2) // Cierre
                ->where('box_id', $apertura->box_id)
                ->where('branchOffice_id', $branchOfficeId)
                ->where('created_at', '>', $apertura->created_at) // Posterior a la apertura
                ->orderBy('created_at', 'asc')
                ->first();

            return [
                'apertura'    => $apertura,
                'cierre'      => $cierre,
                'resumenCaja' => $this->getMovementsSummary(
                    $apertura->id,
                    $cierre?->id,
                    $apertura->box_id
                ),
            ];
        });

        // Respuesta con datos estructurados
        return response()->json([
            'total'          => $aperturas->total(),
            'data'           => $formattedMovements,
            'current_page'   => $aperturas->currentPage(),
            'last_page'      => $aperturas->lastPage(),
            'per_page'       => $aperturas->perPage(),
            'first_page_url' => $aperturas->url(1),
            'from'           => $aperturas->firstItem(),
            'to'             => $aperturas->lastItem(),
            'next_page_url'  => $aperturas->nextPageUrl(),
            'prev_page_url'  => $aperturas->previousPageUrl(),
        ], 200);
    }

    /**
     * Obtener resumen de movimientos entre dos IDs de apertura y cierre.
     */
    private function getMovementsSummary($startId, $endId, $boxId)
    {
        $query = Moviment::selectRaw('
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.total ELSE 0 END), 0.00) as total_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.total ELSE 0 END), 0.00) as total_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.cash ELSE 0 END), 0.00) as efectivo_egresos,
            COALESCE(SUM(CASE WHEN cp.type = "Ingreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_ingresos,
            COALESCE(SUM(CASE WHEN cp.type = "Egreso" THEN moviments.card ELSE 0 END), 0.00) as tarjeta_egresos
        ')
            ->leftJoin('payment_concepts as cp', 'moviments.paymentConcept_id', '=', 'cp.id')
            ->where('moviments.box_id', $boxId);

        if ($startId) {
            $query->where('moviments.id', '>=', $startId);
        }
        if ($endId) {
            $query->where('moviments.id', '<', $endId);
        }

        return $query->first();
    }

}
