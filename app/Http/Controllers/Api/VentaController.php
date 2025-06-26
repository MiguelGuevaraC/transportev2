<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest\PayMasiveSaleRequest;
use App\Http\Resources\VentaResource;
use App\Models\Bitacora;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\CreditNote;
use App\Models\DetailMoviment;
use App\Models\DetalleMoviment;
use App\Models\Installment;
use App\Models\Moviment;
use App\Models\PaymentConcept;
use App\Models\Reception;
use App\Models\ReceptionBySale;
use App\Services\MovimentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VentaController extends Controller
{
    protected $movimentService;

    public function __construct(MovimentService $movimentService)
    {
        $this->movimentService = $movimentService;
    }
    /**
     * @OA\Post(
     *     path="/transportedev/public/api/sale",
     *     summary="Store a new sale",
     *     tags={"Sale"},
     *     description="Create a new sale",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sale data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="paymentDate",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha de pago",
     *                     nullable=true,
     *                     example="2023-06-17"
     *                 ),
     *                 @OA\Property(
     *                     property="yape",
     *                     type="number",
     *                     format="float",
     *                     description="Pago por Yape",
     *                     nullable=true,
     *                     example=20.00
     *                 ),
     *                      @OA\Property(
     *                     property="bank_id",
     *                     type="number",
     *                     format="integer",
     *                     description="ID bank_id",
     *                     nullable=true,
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="deposit",
     *                     type="number",
     *                     format="float",
     *                     description="Depósito",
     *                     nullable=true,
     *                     example=30.00
     *                 ),
     *                 @OA\Property(
     *                     property="cash",
     *                     type="number",
     *                     format="float",
     *                     description="Efectivo",
     *                     nullable=true,
     *                     example=50.00
     *                 ),
     *                 @OA\Property(
     *                     property="card",
     *                     type="number",
     *                     format="float",
     *                     description="Pago por tarjeta",
     *                     nullable=true,
     *                     example=0.50
     *                 ),
     *                 @OA\Property(
     *                     property="plin",
     *                     type="number",
     *                     format="float",
     *                     description="Pago por Plin",
     *                     nullable=true,
     *                     example=50.00
     *                 ),
     *                 @OA\Property(
     *                     property="comment",
     *                     type="string",
     *                     description="Comentario",
     *                     nullable=true,
     *                     example="Pago parcial"
     *                 ),
     *                 @OA\Property(
     *                     property="typeDocument",
     *                     type="string",
     *                     description="Tipo de documento",
     *                     nullable=true,
     *                     example="F"
     *                 ),
     *                 @OA\Property(
     *                     property="typePayment",
     *                     type="string",
     *                     description="Contado / Credito",
     *                     nullable=true,
     *                     example="Contado"
     *                 ),
     *                 @OA\Property(
     *                     property="numberVoucher",
     *                     type="string",
     *                     description="Numero del voucher",
     *                 ),
     *                 @OA\Property(
     *                     property="isBankPayment",
     *                     type="integer",
     *                     description="0 Desactivado / 1 Activado",
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="typeSale",
     *                     type="string",
     *                     description="Normal / Detraccion",
     *                     nullable=true,
     *                     example="Estandar"
     *                 ),
     *                 @OA\Property(
     *                     property="box_id",
     *                     type="integer",
     *                     description="ID de la caja",
     *                     nullable=true,
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="branchOffice_id",
     *                     type="integer",
     *                     description="ID de la sucursal",
     *                     nullable=true,
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="reception_id",
     *                     type="integer",
     *                     description="ID de la recepción",
     *                     nullable=true,
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="person_id",
     *                     type="integer",
     *                     description="ID de la persona",
     *                     nullable=true,
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product", type="string", example="Descripción"),
     *                     @OA\Property(property="weight", type="number", format="float", example=0.00),
     *                     @OA\Property(property="price", type="number", format="float", example=0.00),
     *                     @OA\Property(property="quantity", type="number", format="float", example=0.00),

     *                  ),
     *             ),
     *
     *                 @OA\Property(
     *                 property="installment",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="number", format="date", example="7"),
     *                     @OA\Property(property="total", type="number", format="float", example="100")
     *                 )
     *             ),
     *                 @OA\Property(
     *                     property="routeVoucher",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen del voucher",
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale created successfully",
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

            'paymentDate' => 'required|date',
            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',
            'comment' => 'nullable|string',

            'typeDocument' => 'nullable|string|in:F,T,B',
            'typePayment' => 'nullable|string',
            'typeSale' => 'nullable|string',
            'codeDetraction' => 'nullable|string',
            'observation' => 'nullable|string',

            'programming_id' => 'nullable|exists:programmings,id',

            'isBankPayment' => 'required|in:0,1',

            'bank_id' => 'nullable|exists:banks,id',

            // 'paymentConcept_id' => 'required|exists:payment_concepts,id', //venta
            'box_id' => 'required|exists:boxes,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'reception_id' => 'nullable|exists:receptions,id',
            'person_id' => 'required|exists:people,id',
            'installments' => 'nullable|array',

            'details' => 'nullable|array',

            'data' => 'nullable|array',
            'data.*.reception_id' => 'nullable|exists:receptions,id',
            'data.*.description' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario Sin caja Asginada'], 422);
        }
        if (Auth()->user()->box->serie == null) {
            return response()->json(['error' => 'Caja No tiene Serie'], 422);
        }
        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }
        // else {
        //     $box_id = auth()->user()->box_id;
        //     $box = Box::find($box_id);
        // }

        $branch_office_id = $request->input('branchOffice_id');
        $branchOffice = BranchOffice::find($request->input('branchOffice_id'));

        // Mapeo de tipos de documento
        // Mapeo de tipos de documento
        $documentTypes = [
            'T' => 'T',
            'B' => 'B',
            'F' => 'F',
        ];

        // Obtener el tipo de documento del request
        $typeDocument = $request->input('typeDocument');

        // Validar y asignar el tipo usando el array de mapeo
        if (isset($documentTypes[$typeDocument])) {
            $tipo = $documentTypes[$typeDocument];
        } else {
            return response()->json(['error' => 'Boleta:B. Factura:F Ticket:T'], 422);
        }

        // $branchOfficeIdFormatted = str_pad($box_id, 3, '0', STR_PAD_LEFT) * 100;
        $branchOfficeIdFormatted = str_pad($box->serie, 3, '0', STR_PAD_LEFT);

        $tipo .= $branchOfficeIdFormatted;

        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(sequentialNumber, LOCATE("-", sequentialNumber) + 1) AS UNSIGNED)), 0) + 1 AS siguienteNum
             FROM moviments
             WHERE movType = "Venta"
             AND SUBSTRING(sequentialNumber, 1, 4) = ?',
            [$tipo]
        );
        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;
        $tipoCompleto = $tipo . $branchOfficeIdFormatted . '-' . $siguienteNum;
        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $routeVoucher = null;
        $numberVoucher = null;
        $bank_id = null;
        $depositAmount = 0;

        if ($request->input('isBankPayment') == 1) {
            $routeVoucher = 'ruta.jpg';
            $numberVoucher = $request->input('numberVoucher');
            $bank_id = $request->input('bank_id');
            $depositAmount = $request->input('deposit') ?? 0;
        }
        $paymentConcetp = PaymentConcept::find($request->input('paymentConcept_id'));

        $data = [

            'sequentialNumber' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate' => $request->input('paymentDate'),
            'total' => $total ?? 0,
            'saldo' => $total ?? 0,
            'yape' => $request->input('yape') ?? 0,
            'deposit' => $depositAmount ?? 0,
            'cash' => $request->input('cash') ?? 0,
            'card' => $request->input('card') ?? 0,
            'plin' => $request->input('plin') ?? 0,
            'comment' => $request->input('comment') ?? '-',
            'typeDocument' => 'Ingreso',
            'bank_id' => $bank_id,
            'nroTransferencia' => $request->input('nroTransferencia') ?? '',

            'isBankPayment' => $request->input('isBankPayment'),
            'routeVoucher' => $routeVoucher,
            'numberVoucher' => $numberVoucher,
            'movType' => 'Venta',
            'observation' => $request->input('observation'),

            'typePayment' => $request->input('typePayment') ?? null,
            'typeSale' => $request->input('typeSale') ?? '-',
            'codeDetraction' => $request->input('codeDetraction'),
            'status' => 'Pendiente',
            'programming_id' => $request->input('programming_id'),
            'paymentConcept_id' => 3, //venta
            'branchOffice_id' => $request->input('branchOffice_id'),
            'reception_id' => $request->input('reception_id'),
            'person_id' => $request->input('person_id'),
            'user_id' => auth()->id(),
            'box_id' => $request->input('box_id'),
            'person_reception_id' => $request->input('person_reception_id'),
        ];

        if (Reception::find($request->input('reception_id')) != null) {

            $reception = Reception::find($request->input('reception_id'));

            $exceedAmount = $reception->debtAmount - $total;

            if ($reception->debtAmount - $total < 0) {
                return response()->json([
                    'error' => "El Monto ingresado de $total Supera el Saldo de $reception->debtAmount por $exceedAmount",
                ], 422);
            }
            $reception->save();

        }

        $object = Moviment::create($data);
        if ($object->reception_id != null) {
            $movProgramming = Reception::find($request->input('reception_id'));

            $movProgramming->debtAmount = $movProgramming->debtAmount - $total;
            $movProgramming->status = 'Pago_Generado';
            $movProgramming->save();
        }

        $descriptionString = '';
        if ($object) {
            $details = $request->input('details');

            if (!empty($details)) {
                $descriptionArray = [];

                foreach ($details as $detail) {
                    $objectData = [
                        'product' => $detail['product'] ?? '-',
                        'quantity' => $detail['quantity'] ?? 1,
                        'weight' => $detail['weight'] ?? 0.00,
                        'price' => $detail['price'] ?? 0.00,
                        'moviment_id' => $object->id,
                    ];

                    DetailMoviment::create($objectData);
                    $descriptionArray[] = $detail['product'] ?? '-';
                }

                $descriptionString = implode(', ', $descriptionArray);
                $object->productList = $descriptionString;
                $object->save();
            }
        }
        $installments = $request->input('installments') ?? [];
        // if ($request->input('typePayment') == 'Contado') {
        if (empty($installments)) {
            $tipo = 'M001';
            $resultado = DB::select(
                'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(correlative, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                 FROM moviments
                 WHERE movType = "Caja"
                 AND SUBSTRING_INDEX(correlative, "-", 1) = ?',
                [$tipo]
            );

            $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

            $data = [
                'sequentialNumber' => $object->sequentialNumber,
                'correlative' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentDate' => $request->input('paymentDate'),
                'total' => $total ?? 0,
                'yape' => $request->input('yape') ?? 0,
                'deposit' => $depositAmount ?? 0,
                'cash' => $request->input('cash') ?? 0,
                'card' => $request->input('card') ?? 0,
                'plin' => $request->input('plin') ?? 0,
                'comment' => $request->input('comment') ?? '-',
                'typeDocument' => 'Ingreso',
                'bank_id' => $bank_id,

                'isBankPayment' => $request->input('isBankPayment'),
                'routeVoucher' => $routeVoucher,
                'numberVoucher' => $numberVoucher,
                'movType' => 'Caja',
                'observation' => $request->input('observation'),

                'typePayment' => $request->input('typePayment') ?? null,
                'typeSale' => $request->input('typeSale') ?? '-',
                'codeDetraction' => $request->input('codeDetraction'),
                'status' => 'Pagado',
                'programming_id' => $request->input('programming_id'),
                'paymentConcept_id' => 3, //venta
                'branchOffice_id' => $request->input('branchOffice_id'),
                'reception_id' => $request->input('reception_id'),
                'person_id' => $request->input('person_id'),
                'user_id' => auth()->id(),
                'box_id' => $request->input('box_id'),
                'mov_id' => $object->id,
                'person_reception_id' => $request->input('person_reception_id'),
            ];

            $object2 = Moviment::create($data);
        } else {

            if (!empty($installments)) {
                // Variables para acumular el total
                $totalAcumulado = 0;

                foreach ($installments as $installment) {
                    $dias = $installment['date'];

                    // Datos para crear una cuota
                    $data = [
                        'date' => now()->addDays($dias),
                        'days' => $dias,
                        'total' => $installment['importe'],
                        'totalDebt' => $installment['importe'],
                        'moviment_id' => $object->id,
                    ];

                    // Crear la cuota en la base de datos
                    $install = Installment::create($data);

                    // Acumular el importe de la cuota en el total
                    // $totalAcumulado += $install->total;
                }

                // Asignar el total acumulado a los campos total y saldo
                // $object->total = $totalAcumulado;
                // $object->saldo = $totalAcumulado;
                $object->storeTotalInCredit();
                // Guardar los cambios en el objeto Moviment
                $object->save();
            }

        }

        //IMAGEN
        $image = $request->file('routeVoucher');

        if ($image) {
            Log::info('Imagen recibida: ' . $image->getClientOriginalName());
            $file = $image;
            $currentTime = now();
            $filename = $currentTime->format('YmdHis') . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/photosVouchers', $filename);
            Log::info('Imagen almacenada en: ' . $path);
            $rutaImagen = Storage::url($path);
            $object->routeVoucher = $rutaImagen;
            $object->save();
            Log::info('Imagen guardada en la base de datos con ruta: ' . $rutaImagen);
        }

        if ($request->data != [] && $request->data) {
            $data = $request->data;

            // foreach ($data as $item) {
            //     $reception = Reception::find($item['reception_id']);

            //     if ($reception) {
            //         $item['monto'] = $reception->paymentAmount;
            //         $item['guia'] = $reception->firstCarrierGuide?->numero ?? '-';
            //         $item['placaVehiculo'] = $reception->firstCarrierGuide?->tract?->currentPlate ?? '-';
            //     } else {
            //         $item['monto'] = 0;
            //         $item['guia'] = '-';
            //         $item['placaVehiculo'] = '-';
            //     }
            // }

            $jsonData = json_encode($request->data);
            $object->productList = $jsonData;
            $object->save();
        }
        $totalDebtSum = Moviment::where('box_id', $request->input('box_id'))
            ->where('movType', 'Venta')
            ->whereDate('paymentDate', now())
            ->get()->sum('total');

        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'detailsMoviment',
            'detalles',
            'reception.details',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments',
            'installments.payInstallments'
        ])->find($object->id);

        return response()->json(["venta" => $object, 'totalSum' => $totalDebtSum], 200);

    }
    //COMENTADO DESDE DEV
    public function declararBoletaFactura(Request $request, $idventa, $idtipodocumento)
    {
        $empresa_id = 1;

        $moviment = Moviment::find($idventa);

        $sequentialNumber = $moviment->sequentialNumber;
        if (!in_array(substr($sequentialNumber, 0, 1), ['F', 'B'])) {
            return response()->json([
                'error' => 'El número secuencial debe comenzar con "F" o "B".',
            ], 400);
        }

        if (!$moviment) {
            return response()->json(['message' => 'VENTA NO ENCONTRADA'], 422);
        }
        if ($moviment->status_facturado != 'Pendiente') {
            return response()->json(['message' => 'VENTA NO SE ENCUENTRA EN PENDIENTE DE ENVÍO'], 422);
        }

        // Definir la función de acuerdo al tipo de documento
        if ($idtipodocumento == 3) {
            $funcion = "enviarBoleta";
        } else {
            $funcion = "enviarFactura";
        }

        // Construir la URL con los parámetros
        $url = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion' => $funcion,
            'idventa' => $idventa,
            'empresa_id' => $empresa_id,
        ];
        $url .= '?' . http_build_query($params);

        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($ch);

        // Verificar si ocurrió algún error
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            // Registrar el error en el log
            Log::error("Error en cURL al enviar VENTA. ID venta: $idventa,$funcion Error: $error");
            // echo 'Error en cURL: ' . $error;
        } else {
            // Registrar la respuesta en el log
            Log::error("Respuesta recibida de VENTA para ID venta: $idventa,$funcion Respuesta: $response");
            // Mostrar la respuesta
            // echo 'Respuesta: ' . $response;
        }

        // Cerrar cURL
        curl_close($ch);
        // Log del cierre de la solicitud
        Log::info("Solicitud de VENTA finalizada para ID venta: $idventa. $funcion");
        $moviment->user_factured_id = Auth::user()->id;
        $moviment->status_facturado = 'Enviado';
        $moviment->save();

        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'reception.details',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments'
        ])->find($moviment->id);

        Bitacora::create([
            'user_id' => Auth::id(),  // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'POST',      // Acción realizada
            'table_name' => 'moviments', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Declarar Venta',      // Descripción de la acción
            'ip_address' => $request->ip(),        // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        return response()->json($moviment, 200);
    }
    //COMENTADO DESDE DEV
    public function declararBoletaFacturaById($idventa, $idtipodocumento)
    {
        $empresa_id = 1;

        $moviment = Moviment::find($idventa);

        if (!$moviment) {
            return response()->json(['message' => 'VENTA NO ENCONTRADA'], 422);
        }
        // if ($moviment->status_facturado != 'Pendiente') {
        //     return response()->json(['message' => 'VENTA NO SE ENCUENTRA EN PENDIENTE DE ENVÍO'], 422);
        // }

        // Definir la función de acuerdo al tipo de documento
        if ($idtipodocumento == 3) {
            $funcion = "enviarBoleta";
        } else {
            $funcion = "enviarFactura";
        }

        // Construir la URL con los parámetros
        $url = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion' => $funcion,
            'idventa' => $idventa,
            'empresa_id' => $empresa_id,
        ];
        $url .= '?' . http_build_query($params);

        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($ch);

        // Verificar si ocurrió algún error
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            // Registrar el error en el log
            Log::error("Error en cURL al enviar VENTA. ID venta: $idventa,$funcion Error: $error");
            // echo 'Error en cURL: ' . $error;
        } else {
            // Registrar la respuesta en el log
            Log::error("Respuesta recibida de VENTA para ID venta: $idventa,$funcion Respuesta: $response");
            // Mostrar la respuesta
            // echo 'Respuesta: ' . $response;
        }

        // Cerrar cURL
        curl_close($ch);
        // Log del cierre de la solicitud
        Log::info("Solicitud de VENTA finalizada para ID venta: $idventa. $funcion");
        $moviment->user_factured_id = Auth::user()->id;
        $moviment->status_facturado = 'Enviado';
        $moviment->save();
        return response()->json($moviment, 200);
    }
    //COMENTADO DESDE DEV
    public function declararVentasHoy()
    {
        $empresa_id = 1;
        // $fecha = Carbon::now()->toDateString();
        $fecha = Carbon::now()->subDay()->toDateString();

        // Obtener todas las guías que cumplen con la fecha y el estado "Pendiente"
        $ventas = Moviment::whereDate('paymentDate', $fecha)
            ->where('status_facturado', 'Pendiente')
            ->where('movType', 'Venta')
            ->get();

        Log::error("INICIO ENVIO MASIVO $fecha: DE VENTAS DEL DÍA");
        $contador = 0;
        // Procesar cada guía encontrada
        foreach ($ventas as $venta) {
            $numero = $venta->sequentialNumber;
            if ($numero[0] === 'B') {
                $funcion = "enviarBoleta";
            } elseif ($numero[0] === 'F') {
                $funcion = "enviarFactura";
            } else {
                Log::error("NO ES BOLETA NI FACTURA: " . $venta);
            }

            $idventa = $venta->id;
            $contador++;
            // Construir la URL con los parámetros
            $url = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
            $params = [
                'funcion' => $funcion,
                'idventa' => $idventa,
                'empresa_id' => $empresa_id,
            ];
            $url .= '?' . http_build_query($params);

            // Inicializar cURL
            $ch = curl_init();

            // Configurar opciones cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Ejecutar la solicitud y obtener la respuesta
            $response = curl_exec($ch);

            // Verificar si ocurrió algún error
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                // Registrar el error en el log
                Log::error("Error en cURL al enviar VENTA. ID venta: $idventa,$funcion Error: $error");
                // echo 'Error en cURL: ' . $error;
            } else {
                // Registrar la respuesta en el log
                Log::error("Respuesta recibida de VENTA para ID venta: $idventa,$funcion Respuesta: $response");
                // Mostrar la respuesta
                // echo 'Respuesta: ' . $response;
            }

            // Cerrar cURL
            curl_close($ch);

            // Actualizar el estado de la guía a "Enviado"
            $venta->status_facturado = 'Enviado';
            $venta->save();

            // Log del cierre de la solicitud para cada guía
            Log::info("Solicitud de VENTA finalizada para ID venta: $idventa. $funcion");

            Bitacora::create([
                'user_id' => null,        // ID del usuario que realiza la acción
                'record_id' => $venta->id,  // El ID del usuario afectado
                'action' => 'CRON',      // Acción realizada
                'table_name' => 'moviments', // Tabla afectada
                'data' => json_encode($venta),
                'description' => 'Declaración Automatica Ventas 23:45 pm', // Descripción de la acción
                'ip_address' => null,                                      // Dirección IP del usuario
                'user_agent' => null,                                      // Información sobre el navegador/dispositivo
            ]);
        }
        Log::error("FINALIZADO ENVIO MASIVO $fecha, VENTAS ENVIADAS: $contador");

    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/saleWithReceptions",
     *     summary="Store a new sale",
     *     tags={"Sale1"},
     *     description="Create a new sale",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sale data",
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
     *                 property="yape",
     *                 type="number",
     *                 format="float",
     *                 description="Pago por Yape",
     *                 nullable=true,
     *                 example=20.00
     *             ),
     *             @OA\Property(
     *                 property="bank_id",
     *                 type="integer",
     *                 description="ID del banco",
     *                 nullable=true,
     *                 example=1
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
     *                 description="Tipo de pago (Contado / Crédito)",
     *                 nullable=true,
     *                 example="Contado"
     *             ),
     *             @OA\Property(
     *                 property="numberVoucher",
     *                 type="string",
     *                 description="Número del voucher",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="isBankPayment",
     *                 type="integer",
     *                 description="Indica si es un pago bancario (0: No, 1: Sí)",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="typeSale",
     *                 type="string",
     *                 description="Tipo de venta (Normal / Detracción)",
     *                 nullable=true,
     *                 example="Estandar"
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
     *                 property="receptions",
     *                 type="array",
     *                 @OA\Items(
     *                     type="integer",
     *                     description="ID de la recepción",
     *                     example=1
     *                 ),
     *                 description="Array de IDs de recepciones"
     *             ),
     *             @OA\Property(
     *                 property="person_id",
     *                 type="integer",
     *                 description="ID de la persona",
     *                 nullable=true,
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product", type="string", example="Descripción del producto"),
     *                     @OA\Property(property="weight", type="number", format="float", example=0.00),
     *                     @OA\Property(property="price", type="number", format="float", example=0.00),
     *                     @OA\Property(property="quantity", type="number", format="float", example=0.00)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="installments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string", format="number", example="7"),
     *                     @OA\Property(property="importe", type="number", format="float", example=100.00)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venta creada con éxito",
     *         @OA\JsonContent(ref="#/components/schemas/MovimentRequest")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Algunos campos son requeridos.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="No autenticado.")
     *         )
     *     )
     * )
     */

    public function storeWithReceptions(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'paymentDate' => 'required|date',
            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'typeDocument' => 'nullable|string|in:F,T,B',
            'typePayment' => 'nullable|string',
            'typeSale' => 'nullable|string',
            'observation' => 'nullable|string',

            'codeDetraction' => 'nullable|string',
            'programming_id' => 'nullable|exists:programmings,id',
            'isBankPayment' => 'required|in:0,1',
            'bank_id' => 'nullable|exists:banks,id',
            'box_id' => 'required|exists:boxes,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'receptions.*' => [
                'exists:receptions,id',
                function ($attribute, $value, $fail) {
                    if (
                        DB::table('receptions')->where('id', $value)
                            ->whereNotNull('moviment_id')->exists()
                    ) {
                        $reception = Reception::find($value);
                        $fail("La recepción con codigo {$reception->codeReception} ya tiene una venta anidada.");
                    }
                },
            ],
            'is_consolidated' => 'nullable|boolean',
            'description_consolidated' => 'required_if:is_consolidated,1|string|max:1000',

            'person_id' => 'required|exists:people,id',
            'installments' => 'nullable|array',
            'details' => 'nullable|array',

            'data' => 'nullable|array',
            'data.*.reception_id' => 'nullable|exists:receptions,id',
            'data.*.description' => 'nullable|string',

        ])->after(function ($validator) use ($request) {
            // Sumar los paymentAmount de cada Reception asociada
            $totalReceptionPayments = collect($request->input('data', []))->sum(function ($item) {
                $reception = Reception::find($item['reception_id'] ?? null);
                return $reception?->paymentAmount ?? 0;
            });

            // Verificar el tipo de pago
            $typePayment = $request->input('typePayment');

            if ($typePayment === 'Créditos') {
                // Si es crédito, sumamos los importes de installments en lugar de los métodos de pago
                $totalPayments = collect($request->input('installments', []))->sum('importe');
            } else {
                // Si no es crédito, sumamos los métodos de pago normalmente
                $totalPayments =
                    ($request->input('cash', 0) ?: 0) +
                    ($request->input('yape', 0) ?: 0) +
                    ($request->input('plin', 0) ?: 0) +
                    ($request->input('card', 0) ?: 0) +
                    ($request->input('deposit', 0) ?: 0);
            }

            // Validar que la suma de paymentAmount en Reception sea igual a los pagos o importes
            if (abs($totalReceptionPayments - $totalPayments) > 0.0001) {
                $difference = $totalReceptionPayments - $totalPayments;
                $validator->errors()->add('reception_payment_mismatch', "La suma de los pagos de las recepciones ($totalReceptionPayments) no coincide con la suma de los valores de pago ($totalPayments). Diferencia: $difference.");
            }
        });

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario Sin caja Asginada'], 422);
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box = Box::find($box_id);
        }

        $is_consolidated = $request->input('is_consolidated', false);

        // Lógica de documento secuencial
        $branch_office_id = $request->input('branchOffice_id');
        $branchOffice = BranchOffice::find($request->input('branchOffice_id'));
        $tipo = '';
        $documentTypes = [
            'T' => 'T',
            'B' => 'B',
            'F' => 'F',
        ];

        // Obtener el tipo de documento del request
        $typeDocument = $request->input('typeDocument');

        // Validar y asignar el tipo usando el array de mapeo
        if (isset($documentTypes[$typeDocument])) {
            $tipo = $documentTypes[$typeDocument];
        } else {
            return response()->json(['error' => 'Boleta:B. Factura:F Ticket:T'], 422);
        }

        // $branchOfficeIdFormatted = str_pad($box_id, 3, '0', STR_PAD_LEFT) * 100;
        $branchOfficeIdFormatted = str_pad($box->serie, 3, '0', STR_PAD_LEFT);
        $tipo .= $branchOfficeIdFormatted;

        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(sequentialNumber, LOCATE("-", sequentialNumber) + 1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                     FROM moviments
                     WHERE movType = "Venta"
                     AND SUBSTRING(sequentialNumber, 1, 4) = ?',
            [$tipo]
        );
        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

        // Acumular montos
        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $routeVoucher = null;
        $numberVoucher = null;
        $bank_id = null;
        $depositAmount = 0;

        if ($request->input('isBankPayment') == 1) {
            $routeVoucher = 'ruta.jpg';
            $numberVoucher = $request->input('numberVoucher');
            $bank_id = $request->input('bank_id');
            $depositAmount = $request->input('deposit') ?? 0;
        }

        // Datos básicos del movimiento
        $data = [
            'sequentialNumber' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate' => $request->input('paymentDate'),
            'total' => $total,
            'saldo' => $total,
            'yape' => $yape,
            'deposit' => $depositAmount,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,
            'comment' => $request->input('comment') ?? '-',
            'typeDocument' => 'Ingreso',
            'nroTransferencia' => $request->input('nroTransferencia') ?? '',
            'percentDetraction' => $request->input('percentDetraction'),

            'bank_id' => $bank_id,
            'isBankPayment' => $request->input('isBankPayment'),
            'routeVoucher' => $routeVoucher,
            'numberVoucher' => $numberVoucher,
            'movType' => 'Venta',
            'observation' => $request->input('observation'),
            'is_consolidated' => $request->input('is_consolidated'),

            'typePayment' => $request->input('typePayment'),
            'typeSale' => $request->input('typeSale'),
            'codeDetraction' => $request->input('codeDetraction'),
            'status' => 'Pendiente',
            'programming_id' => $request->input('programming_id'),
            'branchOffice_id' => $request->input('branchOffice_id'),
            'person_id' => $request->input('person_id'),
            'user_id' => auth()->id(),
            'box_id' => $request->input('box_id'),
            'person_reception_id' => $request->input('person_reception_id'),
        ];

        // Lógica para múltiples recepciones
        // Lógica para múltiples recepciones
        $receptions = $request->input('receptions', 'personreception', []);
        if (($receptions) != []) {
            $remainingAmount = $total;

            foreach ($receptions as $reception_id) {
                $reception = Reception::find($reception_id);
                if ($reception) {

                    if ($remainingAmount >= $reception->debtAmount) {
                        $remainingAmount -= $reception->debtAmount; // Restar la deuda completa de esta recepción
                        $reception->debtAmount = 0;                 // Marcar esta recepción como completamente pagada
                        $reception->status = 'Facturada';
                    } else {
                        // Si la cantidad restante es menor, solo restar lo que quede
                        $reception->debtAmount -= $remainingAmount;
                        $remainingAmount = 0; // No queda más dinero para distribuir
                    }
                    $reception->save();
                }

                // Si ya se distribuyó todo el monto, no seguir procesando más recepciones
                if ($remainingAmount <= 0) {
                    break;
                }
            }

        } else {
            return response()->json([
                'error' => "No hay recepciones.",
            ], 422);
        }


        $object = Moviment::create($data);


        foreach ($receptions as $reception_id) {
            $reception = Reception::find($reception_id);
            if ($reception) {
                $reception->moviment_id = $object->id;
                $reception->save();
                $data = [
                    'reception_id' => $reception->id, // Reemplaza con el ID correspondiente
                    'moviment_id' => $object->id,    // Relacionamos con el ID del movimiento actual
                    'status' => 'Activa',
                ];

                // Crear el registro en la base de datos
                $receptionBySale = ReceptionBySale::create($data);
            }
        }
        $installments = $request->input('installments') ?? [];

        // if ($request->input('typePayment') == 'Contado') {
        if (empty($installments) && $installments == []) {
            $tipo = 'M001';
            $resultado = DB::select(
                'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(correlative, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                 FROM moviments
                 WHERE movType = "Caja"
                 AND SUBSTRING_INDEX(correlative, "-", 1) = ?',
                [$tipo]
            );

            $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

            $data = [
                'sequentialNumber' => $object->sequentialNumber,
                'correlative' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentDate' => $request->input('paymentDate'),
                'total' => $total ?? 0,
                'yape' => $request->input('yape') ?? 0,
                'deposit' => $depositAmount ?? 0,
                'cash' => $request->input('cash') ?? 0,
                'card' => $request->input('card') ?? 0,
                'plin' => $request->input('plin') ?? 0,
                'comment' => $request->input('comment') ?? '-',
                'typeDocument' => 'Ingreso',
                'bank_id' => $bank_id,

                'isBankPayment' => $request->input('isBankPayment'),
                'routeVoucher' => $routeVoucher,
                'numberVoucher' => $numberVoucher,
                'movType' => 'Caja',

                'typePayment' => $request->input('typePayment') ?? null,
                'typeSale' => $request->input('typeSale') ?? '-',
                'codeDetraction' => $request->input('codeDetraction'),
                'status' => 'Pagado',
                'programming_id' => $request->input('programming_id'),
                'paymentConcept_id' => 3, //venta
                'branchOffice_id' => $request->input('branchOffice_id'),
                'reception_id' => $request->input('reception_id'),
                'person_id' => $request->input('person_id'),
                'user_id' => auth()->id(),
                'box_id' => $request->input('box_id'),
                'mov_id' => $object->id,
                'person_reception_id' => $request->input('person_reception_id'),
            ];

            $object2 = Moviment::create($data);
            //SE PAGA LA VENTA
            $object->status = 'Pagado';
            $object->save();
        } else {

            if (!empty($installments)) {

                foreach ($installments as $installment) {

                    $dias = $installment['date'];

                    $data = [
                        'date' => now()->addDays($dias),
                        'days' => $dias,
                        'total' => $installment['importe'],
                        'totalDebt' => $installment['importe'],
                        'moviment_id' => $object->id,
                    ];
                    Installment::create($data);
                }
                $object->storeTotalInCredit();
            }

            foreach ($receptions as $reception_id) {
                $reception = Reception::find($reception_id);
                if ($reception) {

                    $reception->debtAmount = 0;
                    $reception->status = 'Facturada';

                    $reception->save();
                }

            }

        }
        $additionalText = '';
        if ($request->input('isValue_ref') == 1 or $request->input('isValue_ref') == true) {
            $valueRef = $request->input('value_ref', 0);              // Si es null, toma 0.
            $formattedValueRef = number_format((float) $valueRef, 2, '.', ''); // Asegura 2 decimales.
            $additionalText = 'Valor Ref: ' . $formattedValueRef;
        }

        if ($request->data != [] && $request->data) {
            $data = $request->data;
            if ($is_consolidated) {
                $moviment_object = Moviment::find($object->id);
                $data = [
                    'description' => $request->input('description_consolidated', 'Venta Consolidada'),
                    'placa' => '-',
                    'guia' => '-',
                    'os' => '-',
                    'cantidad' => 1,
                    'tract_id' => null,
                    'carrier_guide_id' => null,
                    'precioVenta' => $moviment_object->total,
                    'moviment_id' => $moviment_object->id,
                    'reception_id' => null,
                ];
                DetalleMoviment::create($data);

            } else {
                foreach ($data as $item) {
                    $reception = Reception::find($item['reception_id']);

                    $data = [
                        'description' => $item['description'] . ' - ' . $additionalText,

                        'placa' => $reception?->firstCarrierGuide?->tract?->currentPlate ?? '-',
                        'guia' => $reception?->firstCarrierGuide?->numero ?? '-',
                        'os' => (array_key_exists('os', $item) && !is_null($item['os'])) ? $item['os'] : '-',
                        'cantidad' => 1,
                        'tract_id' => $reception?->firstCarrierGuide?->tract->id ?? null,
                        'carrier_guide_id' => $reception?->firstCarrierGuide?->id ?? null,
                        'precioVenta' => $reception?->paymentAmount ?? 0,
                        'moviment_id' => $object->id,
                        'reception_id' => $reception->id,
                    ];
                    DetalleMoviment::create($data);

                }
            }

            $jsonData = json_encode($request->data);
            $object->productList = $jsonData;
            $object->save();
        }

        if ($request->input('isValue_ref') == 1 or $request->input('isValue_ref') == true) {

            $object->monto_detraction = $request->input('monto_detraction');
            $object->monto_neto = $request->input('monto_neto');
            $object->value_ref = $request->input('value_ref');
            $object->isValue_ref = $request->input('isValue_ref');
            if ($object->total != 0) {
                $object->percent_ref = $object->monto_detraction / $object->total * 100;
            } else {
                $object->percent_ref = 0; // O cualquier valor por defecto que consideres adecuado
            }
            $object->save();
        }

        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'reception.details',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments'
        ])->find($object->id);

        switch ($documentTypes[$typeDocument]) {
            case 'B':
                // Log::info("VENTA BOLETA DECLARADA BIEN");
                // $this->declararBoletaFactura($object->id, 3, 1);
                break;

            case 'F':
                // Log::info("VENTA FACTURA DECLARADA BIEN");
                // $this->declararBoletaFactura($object->id, 2, 1);
                break;
        }
        Bitacora::create([
            'user_id' => Auth::id(),  // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'POST',      // Acción realizada
            'table_name' => 'moviments', // Tabla afectada
            'data' => json_encode([
                'payload_request' => $request->all(),  // Datos enviados por el usuario
                'saved_object' => $object           // Objeto guardado en la BD
            ]),
            'description' => 'Guardar Venta con Recepciones', // Descripción de la acción
            'ip_address' => $request->ip(),                  // Dirección IP del usuario
            'user_agent' => $request->userAgent(),           // Información sobre el navegador/dispositivo
        ]);

        $totalDebtSum = Moviment::where('box_id', $request->input('box_id'))
            ->where('movType', 'Venta')
            ->whereDate('paymentDate', now())->get()

            ->sum('total');

        return response()->json(["venta" => new VentaResource($object), 'totalSum' => $totalDebtSum], 200);
    }

    public function storeManual(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'paymentDate' => 'required|date',
            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'typeDocument' => 'nullable|string|in:F,T,B',
            'typePayment' => 'nullable|string',
            'typeSale' => 'nullable|string',
            'codeDetraction' => 'nullable|string',
            'programming_id' => 'nullable|exists:programmings,id',
            'isBankPayment' => 'required|in:0,1',
            'bank_id' => 'nullable|exists:banks,id',
            'box_id' => 'required|exists:boxes,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'receptions' => 'nullable|array', // Array de recepciones
            // 'receptions.*' => 'exists:receptions,id', // Cada recepción debe existir
            'person_id' => 'required|exists:people,id',
            'installments' => 'nullable|array',
            // 'details' => 'nullable|array',

            'details' => 'nullable|array',
            'details.*.reception_id' => 'nullable|exists:receptions,id',
            'details.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario Sin caja Asginada'], 422);
        }
        if (Auth()->user()->box->serie == null) {
            return response()->json(['error' => 'Caja No tiene Serie'], 422);
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box = Box::find($box_id);
        }

        $tipo = '';
        $documentTypes = [
            'T' => 'T',
            'B' => 'B',
            'F' => 'F',
        ];

        // Obtener el tipo de documento del request
        $typeDocument = $request->input('typeDocument');

        if (isset($documentTypes[$typeDocument])) {
            $tipo = $documentTypes[$typeDocument];
        } else {
            return response()->json(['error' => 'Boleta:B. Factura:F Ticket:T'], 422);
        }

        // $branchOfficeIdFormatted = str_pad($box_id, 3, '0', STR_PAD_LEFT) * 100;
        $branchOfficeIdFormatted = str_pad($box->serie, 3, '0', STR_PAD_LEFT);
        $tipo .= $branchOfficeIdFormatted;

        $resultado = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(sequentialNumber, LOCATE("-", sequentialNumber) + 1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                     FROM moviments
                     WHERE movType = "Venta"
                     AND SUBSTRING(sequentialNumber, 1, 4) = ?',
            [$tipo]
        );
        $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

        // Acumular montos
        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $routeVoucher = null;
        $numberVoucher = null;
        $bank_id = null;
        $depositAmount = 0;

        if ($request->input('isBankPayment') == 1) {
            $routeVoucher = 'ruta.jpg';
            $numberVoucher = $request->input('numberVoucher');
            $bank_id = $request->input('bank_id');
            $depositAmount = $request->input('deposit') ?? 0;
        }

        // Datos básicos del movimiento
        $data = [
            'sequentialNumber' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate' => $request->input('paymentDate'),
            'total' => $total,
            'saldo' => $total,
            'yape' => $yape,
            'deposit' => $depositAmount,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,
            'comment' => $request->input('comment') ?? '-',
            'typeDocument' => 'Ingreso',
            'nroTransferencia' => $request->input('nroTransferencia') ?? '',

            'bank_id' => $bank_id,
            'isBankPayment' => $request->input('isBankPayment'),
            'routeVoucher' => $routeVoucher,
            'numberVoucher' => $numberVoucher,
            'movType' => 'Venta',
            'typePayment' => $request->input('typePayment'),
            'typeSale' => $request->input('typeSale'),
            'codeDetraction' => $request->input('codeDetraction'),
            'percentDetraction' => $request->input('percentDetraction'),
            'observation' => $request->input('observation'),

            'status' => 'Pendiente',
            'programming_id' => $request->input('programming_id'),
            'branchOffice_id' => $request->input('branchOffice_id'),
            'person_id' => $request->input('person_id'),
            'user_id' => auth()->id(),
            'box_id' => $request->input('box_id'),
            'person_reception_id' => $request->input('person_reception_id'),
        ];

        // $receptions = $request->input('receptions','personreception', []);

        $object = Moviment::create($data);
        $installments = $request->input('installments') ?? [];

        if (empty($installments) && $installments == []) {
            $tipo = 'M001';
            $resultado = DB::select(
                'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(correlative, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                 FROM moviments
                 WHERE movType = "Caja"
                 AND SUBSTRING_INDEX(correlative, "-", 1) = ?',
                [$tipo]
            );

            $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

            $data = [
                'sequentialNumber' => $object->sequentialNumber,
                'correlative' => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentDate' => $request->input('paymentDate'),
                'total' => $total ?? 0,
                'yape' => $request->input('yape') ?? 0,
                'deposit' => $depositAmount ?? 0,
                'cash' => $request->input('cash') ?? 0,
                'card' => $request->input('card') ?? 0,
                'plin' => $request->input('plin') ?? 0,
                'comment' => $request->input('comment') ?? '-',
                'typeDocument' => 'Ingreso',
                'bank_id' => $bank_id,

                'isBankPayment' => $request->input('isBankPayment'),
                'routeVoucher' => $routeVoucher,
                'numberVoucher' => $numberVoucher,
                'movType' => 'Caja',
                'observation' => $request->input('observation'),

                'typePayment' => $request->input('typePayment') ?? null,
                'typeSale' => $request->input('typeSale') ?? '-',
                'codeDetraction' => $request->input('codeDetraction'),
                'status' => 'Pagado',
                'programming_id' => $request->input('programming_id'),
                'paymentConcept_id' => 3, //venta
                'branchOffice_id' => $request->input('branchOffice_id'),
                'reception_id' => $request->input('reception_id'),
                'person_id' => $request->input('person_id'),
                'user_id' => auth()->id(),
                'box_id' => $request->input('box_id'),
                'mov_id' => $object->id,
                'person_reception_id' => $request->input('person_reception_id'),
            ];

            $object2 = Moviment::create($data);
            //SE PAGA LA VENTA
            $object->status = 'Pagado';
            $object->save();
        } else {

            if (!empty($installments)) {

                foreach ($installments as $installment) {

                    $dias = $installment['date'];

                    $data = [
                        'date' => now()->addDays($dias),
                        'days' => $dias,
                        'total' => $installment['importe'],
                        'totalDebt' => $installment['importe'],
                        'moviment_id' => $object->id,
                    ];
                    Installment::create($data);
                }
                $object->storeTotalInCredit();
            }

        }
        $additionalText = '';
        if ($request->input('isValue_ref') == 1 or $request->input('isValue_ref') == true) {
            $valueRef = $request->input('value_ref', 0);              // Si es null, toma 0.
            $formattedValueRef = number_format((float) $valueRef, 2, '.', ''); // Asegura 2 decimales.
            $additionalText = 'Valor Ref: ' . $formattedValueRef;
        }

        if ($request->details != [] && $request->details) {
            $data = $request->details;

            foreach ($data as $item) {
                $data = [
                    'description' => $item['description'] . ' - ' . $additionalText,
                    'placa' => $item['placa'] ?? '-',
                    'guia' => $item['guia'] ?? '-',
                    'os' => (array_key_exists('os', $item) && !is_null($item['os'])) ? $item['os'] : '-',
                    'cantidad' => $item['cantidad'],
                    'tract_id' => null,
                    'carrier_guide_id' => null,
                    'precioVenta' => $item['precio'],
                    'moviment_id' => $object->id,
                    'reception_id' => null,
                ];
                DetalleMoviment::create($data);

            }

            $jsonData = json_encode($request->data);
            $object->productList = $jsonData;
            $object->save();
        }

        $totalDebtSum = Moviment::where('box_id', $request->input('box_id'))
            ->where('movType', 'Venta')
            ->whereDate('paymentDate', now())->get()
            ->sum('total');

        if ($request->input('isValue_ref') == "1" or $request->input('isValue_ref') == true) {

            $object->monto_detraction = $request->input('monto_detraction');
            $object->monto_neto = $request->input('monto_neto');
            $object->value_ref = $request->input('value_ref');
            $object->isValue_ref = $request->input('isValue_ref');
            if ($object->total != 0) {
                $object->percent_ref = $object->monto_detraction / $object->total * 100;
            } else {
                $object->percent_ref = 0; // O cualquier valor por defecto que consideres adecuado
            }
            $object->save();
        }

        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'detailsMoviment',
            'reception.details',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments',
            'installments.payInstallments'
        ])->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),  // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'POST',      // Acción realizada
            'table_name' => 'moviments', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Guardar Venta Manual', // Descripción de la acción
            'ip_address' => $request->ip(),         // Dirección IP del usuario
            'user_agent' => $request->userAgent(),  // Información sobre el navegador/dispositivo
        ]);

        return response()->json(["venta" => $object, 'totalSum' => $totalDebtSum], 200);
    }

    public function updateManual(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'paymentDate' => 'required|date',
            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'typeDocument' => 'nullable|string|in:F,T,B',
            'typePayment' => 'nullable|string',
            'typeSale' => 'nullable|string',
            'observation' => 'nullable|string',

            'codeDetraction' => 'nullable|string',
            'programming_id' => 'nullable|exists:programmings,id',
            'isBankPayment' => 'required|in:0,1',
            'bank_id' => 'nullable|exists:banks,id',
            'box_id' => 'required|exists:boxes,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'receptions' => 'nullable|array', // Array de recepciones
            // 'receptions.*' => 'exists:receptions,id', // Cada recepción debe existir

            'is_consolidated' => 'nullable|boolean',
            'description_consolidated' => 'required_if:is_consolidated,1|string|max:1000',

            'person_id' => 'required|exists:people,id',
            'installments' => 'nullable|array',
            // 'details' => 'nullable|array',

            'details' => 'nullable|array',
            'details.*.reception_id' => 'nullable|exists:receptions,id',
            'details.*.description' => 'nullable|string',
        ]);

        $object = Moviment::find($id);
        if (!$object) {
            return response()->json(['message' => 'Venta no Encontrada'], 404);
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario Sin caja Asginada'], 422);
        }
        if ($object->installments->isNotEmpty() && $object->installments->first()->payInstallments->isNotEmpty()) {
            return response()->json(['error' => 'Se encontraron Amortizaciones en esta venta'], 422);
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box = Box::find($box_id);
        }

        $tipo = '';
        $documentTypes = [
            'T' => 'T',
            'B' => 'B',
            'F' => 'F',
        ];

        // Obtener el tipo de documento del request
        $typeDocument = $request->input('typeDocument');

        if (isset($documentTypes[$typeDocument])) {
            $tipo = $documentTypes[$typeDocument];
        } else {
            return response()->json(['error' => 'Boleta:B. Factura:F Ticket:T'], 422);
        }

        // $branchOfficeIdFormatted = str_pad($box_id, 3, '0', STR_PAD_LEFT) * 100;
        $branchOfficeIdFormatted = str_pad($box->serie, 3, '0', STR_PAD_LEFT);
        $tipo .= $branchOfficeIdFormatted;

        // Acumular montos
        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $routeVoucher = null;
        $numberVoucher = null;
        $bank_id = null;
        $depositAmount = 0;

        if ($request->input('isBankPayment') == 1) {
            $routeVoucher = 'ruta.jpg';
            $numberVoucher = $request->input('numberVoucher');
            $bank_id = $request->input('bank_id');
            $depositAmount = $request->input('deposit') ?? 0;
        }

        $data = [
            'paymentDate' => $request->input('paymentDate'),
            'total' => $total,
            'yape' => $yape,
            'deposit' => $depositAmount,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,
            'comment' => $request->input('comment') ?? '-',
            'typeDocument' => 'Ingreso',
            'nroTransferencia' => $request->input('nroTransferencia') ?? '',
            'bank_id' => $bank_id,
            'isBankPayment' => $request->input('isBankPayment'),
            'routeVoucher' => $routeVoucher,
            'numberVoucher' => $numberVoucher,
            'movType' => 'Venta',

            'typePayment' => $request->input('typePayment'),
            'typeSale' => $request->input('typeSale'),
            'codeDetraction' => $request->input('codeDetraction'),
            'percentDetraction' => $request->input('percentDetraction'),
            'status' => 'Pendiente',
            'programming_id' => $request->input('programming_id'),
            'branchOffice_id' => $request->input('branchOffice_id'),
            'person_id' => $request->input('person_id'),
            // 'user_id' => auth()->id(),
            // 'box_id' => $request->input('box_id'),
            'user_edited_id' => Auth::user()->id,
            'person_reception_id' => $request->input('person_reception_id'),
            'observation' => $request->input('observation'),
        ];

        // Actualiza el objeto
        $object->update($data);

        $receptions = $request->input('receptions', 'personreception', []); // Obtiene las recepciones del request
        $existingReceptions = $object->receptions()->pluck('id')->toArray();        // Obtiene las recepciones actuales del objeto

        // Eliminar recepciones que no están en la solicitud
        $receptionsToDelete = array_diff($existingReceptions, $receptions);
        if (!empty($receptionsToDelete)) {
            // Actualizar las recepciones no deseadas para eliminar su relación con el objeto
            Reception::whereIn('id', $receptionsToDelete)->update(['moviment_id' => null]);
        }

        // Agregar nuevas recepciones que vienen en la solicitud
        if (!empty($receptions)) {
            foreach ($receptions as $reception_id) {
                // Busca la recepción por su ID
                $reception = Reception::find($reception_id);

                // Si la recepción existe y no está ya asociada al objeto
                if ($reception && !in_array($reception->id, $existingReceptions)) {
                    $reception->moviment_id = $object->id; // Establece la relación
                    $reception->save();                    // Guarda los cambios en la recepción
                }
            }
        }

        // Distribuye el monto restante entre las recepciones
        $remainingAmount = $total;
        foreach ($receptions as $reception_id) {
            $reception = Reception::find($reception_id);
            if ($reception) {
                if ($remainingAmount >= $reception->debtAmount) {
                    $remainingAmount -= $reception->debtAmount; // Resta la deuda completa de esta recepción
                    $reception->debtAmount = 0;                 // Marca esta recepción como completamente pagada
                    $reception->status = 'Facturada';
                } else {
                    // Si la cantidad restante es menor, solo resta lo que quede
                    $reception->debtAmount -= $remainingAmount;
                    $remainingAmount = 0; // No queda más dinero para distribuir
                }
                $reception->save(); // Guarda los cambios en la recepción
            }

            // Si ya se distribuyó todo el monto, no seguir procesando más recepciones
            if ($remainingAmount <= 0) {
                break;
            }
        }
        $installments = $request->input('installments') ?? [];

        // if ($request->input('typePayment') == 'Contado') {
        if (empty($installments) && $installments == []) {
            // Preparar los datos para el movimiento
            // Asegúrate de que el objeto exista antes de intentar actualizar
            $object2 = Moviment::withTrashed()->where('mov_id', $object->id)->first();

            if ($object2) {
                $data = [
                    'paymentDate' => $request->input('paymentDate'),
                    'total' => $total ?? 0,
                    'yape' => $request->input('yape') ?? 0,
                    'deposit' => $depositAmount ?? 0,
                    'cash' => $request->input('cash') ?? 0,
                    'card' => $request->input('card') ?? 0,
                    'plin' => $request->input('plin') ?? 0,
                    'comment' => $request->input('comment') ?? '-',
                    'typeDocument' => 'Ingreso',
                    'bank_id' => $bank_id,
                    'isBankPayment' => $request->input('isBankPayment'),
                    'routeVoucher' => $routeVoucher,
                    'numberVoucher' => $numberVoucher,
                    'movType' => 'Caja',
                    'typePayment' => $request->input('typePayment') ?? null,
                    'typeSale' => $request->input('typeSale') ?? '-',
                    'codeDetraction' => $request->input('codeDetraction'),
                    'status' => 'Pagado',
                    'programming_id' => $request->input('programming_id'),
                    'paymentConcept_id' => 3, // venta
                    'branchOffice_id' => $request->input('branchOffice_id'),
                    'reception_id' => $request->input('reception_id'),
                    'person_id' => $request->input('person_id'),
                    // 'user_id' => auth()->id(),
                    // 'box_id' => $request->input('box_id'),
                    'deleted_at' => null,
                ];
                if ($object2->trashed()) {
                    $object2->restore(); // Restore the soft-deleted record
                }
                // Actualiza el movimiento existente
                $object2->update($data);
            }

            // Actualizar el estado del objeto original
            $object->saldo = 0;
            $object->status = 'Pagado';
            $object->save();

            // Aquí se asume que necesitas eliminar el movimiento de caja del contado
            $installments = Installment::where('moviment_id', $object->id)
                ->whereNull('deleted_at')->get();

            if ($installments->isNotEmpty()) {
                foreach ($installments as $installment) {
                    // Asignar null a los installments relacionados
                    $installment->deleted_at = now(); // Marca como eliminado
                    $installment->save();
                }
            }

        } else {

            if (!empty($installments)) {
                $monto = 0;
                $installmentsActually = $object->installments->pluck('id')->toArray(); // Obtiene los IDs actuales de installments
                $processedInstallments = [];                                            // Mantendrá los IDs procesados para verificar luego cuáles eliminar

                foreach ($installments as $installment) {
                    $dias = $installment['date'];
                    if (isset($installment['id'])) {
                        // Actualizar la cuota existente si se encuentra el ID
                        $existingInstallment = Installment::find($installment['id']);
                        if ($existingInstallment) {
                            $existingInstallment->days = $dias;
                            $existingInstallment->date = now()->addDays($dias);
                            $existingInstallment->total = $installment['importe'];
                            $existingInstallment->totalDebt = $installment['importe'];
                            $existingInstallment->save();

                            $processedInstallments[] = $installment['id']; // Agrega el ID a la lista de procesados
                            $monto += $installment['importe'];
                        }
                    } else {
                        // Datos para crear una cuota
                        $data = [
                            'date' => now()->addDays($dias),
                            'days' => $dias,
                            'total' => $installment['importe'],
                            'totalDebt' => $installment['importe'],
                            'moviment_id' => $object->id,
                        ];
                        $monto += $installment['importe'];
                        $newInstallment = Installment::create($data);

                        $processedInstallments[] = $newInstallment->id; // Agrega el nuevo ID a la lista de procesados
                    }
                }

                // Elimina los installments que no se encuentran en los datos recibidos
                $installmentsToDelete = array_diff($installmentsActually, $processedInstallments);
                Installment::whereIn('id', $installmentsToDelete)->delete();

                // Actualiza el monto total en el objeto principal
                $object->total = $monto;
                $object->saldo = $monto;
                $object->save();

                $object->status = 'Pendiente';
                $object->save();

                $movCaja = Moviment::
                    where('movType', 'Caja')
                    ->where('mov_id', $object->id)
                    ->first();
                if ($movCaja) {
                    //ELIMINAR MOVIMIENTO DE CAJA
                    $movCaja->delete();
                }
            }
        }

        if ($request->details != [] && $request->details) {
            $data = $request->details;

            $existingDetails = DetalleMoviment::where('moviment_id', $object->id)->get();

            // Eliminar solo los detalles existentes que no están en la solicitud
            foreach ($existingDetails as $detalle) {
                $detalle->delete(); // Esto usará el soft delete
            }

            // Procesar la creación o actualización de los detalles
            foreach ($data as $item) {

                // Si no hay ID, crea un nuevo registro
                DetalleMoviment::create([
                    'description' => $item['description'],
                    'placa' => $item['placa'] ?? '-',
                    'guia' => $item['guia'] ?? '-',
                    'os' => (array_key_exists('os', $item) && !is_null($item['os'])) ? $item['os'] : '-',
                    'cantidad' => $item['cantidad'],
                    'tract_id' => null,
                    'carrier_guide_id' => null,
                    'precioVenta' => $item['precio'],
                    'moviment_id' => $object->id,
                    'reception_id' => null,
                ]);

            }

            // Actualizar la lista de productos
            $jsonData = json_encode($request->data);
            $object->productList = $jsonData;
            $object->save();
        }

        $totalDebtSum = Moviment::where('box_id', $request->input('box_id'))
            ->where('movType', 'Venta')
            ->whereDate('paymentDate', now())->get()
            ->sum('total');
        if ($request->input('isValue_ref') == "1" or $request->input('isValue_ref') == true) {

            $object->monto_detraction = $request->input('monto_detraction');
            $object->monto_neto = $request->input('monto_neto');
            $object->value_ref = $request->input('value_ref');
            $object->isValue_ref = $request->input('isValue_ref');
            if ($object->total != 0) {
                $object->percent_ref = $object->monto_detraction / $object->total * 100;
            } else {
                $object->percent_ref = 0; // O cualquier valor por defecto que consideres adecuado
            }
            $object->save();
        }
        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'detailsMoviment',
            'reception.details',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments',
            'installments.payInstallments'
        ])->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),  // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'PUT',       // Acción realizada
            'table_name' => 'moviments', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Actualizar Venta con Recepciones', // Descripción de la acción
            'ip_address' => $request->ip(),                     // Dirección IP del usuario
            'user_agent' => $request->userAgent(),              // Información sobre el navegador/dispositivo
        ]);
        return response()->json(["venta" => $object, 'totalSum' => $totalDebtSum], 200);
    }
    public function updateReceptions(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'paymentDate' => 'required|date',
            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',
            'comment' => 'nullable|string',
            'typeDocument' => 'nullable|string|in:F,T,B',
            'typePayment' => 'nullable|string',
            'observation' => 'nullable|string',

            'typeSale' => 'nullable|string',
            'codeDetraction' => 'nullable|string',
            'programming_id' => 'nullable|exists:programmings,id',
            'isBankPayment' => 'required|in:0,1',
            'bank_id' => 'nullable|exists:banks,id',
            'box_id' => 'required|exists:boxes,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'receptions.*' => [
                'exists:receptions,id',
                function ($attribute, $value, $fail) use ($id) {
                    if (
                        DB::table('receptions')->where('id', $value)
                            ->whereNotNull('moviment_id')
                            ->where('moviment_id', '!=', $id) // Ignorar el ID pasado como variable
                            ->exists()
                    ) {

                        $reception = Reception::find($value);
                        $fail("La recepción con código {$reception->codeReception} ya tiene una venta anidada.");
                    }
                },
            ],

            'person_id' => 'required|exists:people,id',
            'installments' => 'nullable|array',
            // 'details' => 'nullable|array',

            'data' => 'nullable|array',
            'data.*.reception_id' => 'nullable|exists:receptions,id',
            'data.*.description' => 'nullable|string',

            'is_consolidated' => 'nullable|boolean',
        ])->after(function ($validator) use ($request) {
            // Sumar los paymentAmount de cada Reception asociada
            $totalReceptionPayments = collect($request->input('data', []))->sum(function ($item) {
                $reception = Reception::find($item['reception_id'] ?? null);
                return $reception?->paymentAmount ?? 0;
            });

            // Verificar el tipo de pago
            $typePayment = $request->input('typePayment');

            if ($typePayment === 'Créditos') {
                // Si es crédito, sumamos los importes de installments en lugar de los métodos de pago
                $totalPayments = collect($request->input('installments', []))->sum('importe');
            } else {
                // Si no es crédito, sumamos los métodos de pago normalmente
                $totalPayments =
                    ($request->input('cash', 0) ?: 0) +
                    ($request->input('yape', 0) ?: 0) +
                    ($request->input('plin', 0) ?: 0) +
                    ($request->input('card', 0) ?: 0) +
                    ($request->input('deposit', 0) ?: 0);
            }

            // Validar que la suma de paymentAmount en Reception sea igual a los pagos o importes
            if (abs($totalReceptionPayments - $totalPayments) > 0.0001) {
                $difference = $totalReceptionPayments - $totalPayments;
                $validator->errors()->add('reception_payment_mismatch', "La suma de los pagos de las recepciones ($totalReceptionPayments) no coincide con la suma de los valores de pago ($totalPayments). Diferencia: $difference.");
            }
        });



        $moviment_object = Moviment::find($id);
        $object = Moviment::find($id);
        if (!$object) {
            return response()->json(['message' => 'Venta no Encontrada'], 404);
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario Sin caja Asginada'], 422);
        }
        if ($object->installments && $object->installments->isNotEmpty()) {
            // Obtener la primera cuota
            $firstInstallment = $object->installments->first();

            if ($firstInstallment) {
                if ($firstInstallment->payInstallments->isNotEmpty()) {

                    return response()->json(['error' => 'Se encontró amortizaciones en esta venta'], 422);

                }
            }
        }

        $is_consolidated = ($request->input('is_consolidated') == 1 && $object->is_consolidated == 0)
            ? 1
            : $object->is_consolidated;

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        } else {
            $box_id = auth()->user()->box_id;
            $box = Box::find($box_id);
        }

        $tipo = '';
        $documentTypes = [
            'T' => 'T',
            'B' => 'B',
            'F' => 'F',
        ];

        // Obtener el tipo de documento del request
        $typeDocument = $request->input('typeDocument');

        if (isset($documentTypes[$typeDocument])) {
            $tipo = $documentTypes[$typeDocument];
        } else {
            return response()->json(['error' => 'Boleta:B. Factura:F Ticket:T'], 422);
        }

        // $branchOfficeIdFormatted = str_pad($box_id, 3, '0', STR_PAD_LEFT) * 100;
        $branchOfficeIdFormatted = str_pad($box->serie, 3, '0', STR_PAD_LEFT);
        $tipo .= $branchOfficeIdFormatted;

        // Acumular montos
        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $routeVoucher = null;
        $numberVoucher = null;
        $bank_id = null;
        $depositAmount = 0;

        if ($request->input('isBankPayment') == 1) {
            $routeVoucher = 'ruta.jpg';
            $numberVoucher = $request->input('numberVoucher');
            $bank_id = $request->input('bank_id');
            $depositAmount = $request->input('deposit') ?? 0;
        }

        $data = [
            'paymentDate' => $request->input('paymentDate'),
            'total' => $total,
            'yape' => $yape,
            'deposit' => $depositAmount,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,
            'comment' => $request->input('comment') ?? '-',
            'typeDocument' => 'Ingreso',
            'nroTransferencia' => $request->input('nroTransferencia') ?? '',
            'bank_id' => $bank_id,
            'isBankPayment' => $request->input('isBankPayment'),
            'routeVoucher' => $routeVoucher,
            'numberVoucher' => $numberVoucher,
            'movType' => 'Venta',

            'typePayment' => $request->input('typePayment'),
            'typeSale' => $request->input('typeSale'),
            'codeDetraction' => $request->input('codeDetraction'),
            'percentDetraction' => $request->input('percentDetraction'),
            'status' => 'Pendiente',
            'programming_id' => $request->input('programming_id'),
            'branchOffice_id' => $request->input('branchOffice_id'),
            'person_id' => $request->input('person_id'),
            // 'user_id' => auth()->id(),
            // 'box_id' => $request->input('box_id'),
            'user_edited_id' => Auth::user()->id,
            'person_reception_id' => $request->input('person_reception_id'),
            'observation' => $request->input('observation'),
            'is_consolidated' => $is_consolidated,
        ];

        // Actualiza el objeto
        $object->update($data);

        $receptions = $request->input('receptions', 'personreception', []); // Obtiene las recepciones del request
        $existingReceptions = $object->receptions()->pluck('id')->toArray();        // Obtiene las recepciones actuales del objeto

        // Eliminar recepciones que no están en la solicitud
        $receptionsToDelete = array_diff($existingReceptions, $receptions);
        if (!empty($receptionsToDelete)) {
            // Actualizar las recepciones no deseadas para eliminar su relación con el objeto
            Reception::whereIn('id', $receptionsToDelete)->update(['moviment_id' => null]);
        }

        // Agregar nuevas recepciones que vienen en la solicitud
        if (!empty($receptions)) {
            foreach ($receptions as $reception_id) {
                // Busca la recepción por su ID
                $reception = Reception::find($reception_id);

                // Si la recepción existe y no está ya asociada al objeto
                if ($reception && !in_array($reception->id, $existingReceptions)) {
                    $reception->moviment_id = $object->id; // Establece la relación
                    $reception->save();                    // Guarda los cambios en la recepción
                }
            }
        }

        // Distribuye el monto restante entre las recepciones
        $remainingAmount = $total;
        foreach ($receptions as $reception_id) {
            $reception = Reception::find($reception_id);
            if ($reception) {
                if ($remainingAmount >= $reception->debtAmount) {
                    $remainingAmount -= $reception->debtAmount; // Resta la deuda completa de esta recepción
                    $reception->debtAmount = 0;                 // Marca esta recepción como completamente pagada
                    $reception->status = 'Facturada';
                } else {
                    // Si la cantidad restante es menor, solo resta lo que quede
                    $reception->debtAmount -= $remainingAmount;
                    $remainingAmount = 0; // No queda más dinero para distribuir
                }
                $reception->save(); // Guarda los cambios en la recepción
            }

            // Si ya se distribuyó todo el monto, no seguir procesando más recepciones
            if ($remainingAmount <= 0) {
                break;
            }
        }

        if ($request->data != [] && $request->data) {
            $data = $request->data;

            $existingDetails = DetalleMoviment::where('moviment_id', $object->id)->get();

            // Eliminar solo los detalles existentes que no están en la solicitud
            foreach ($existingDetails as $detalle) {
                $detalle->delete(); // Esto usará el soft delete
            }

            if ($is_consolidated) {
                $data = [
                    'description' => $request->input('description_consolidated', 'Venta Consolidada'),
                    'placa' => '-',
                    'guia' => '-',
                    'os' => '-',
                    'cantidad' => 1,
                    'tract_id' => null,
                    'carrier_guide_id' => null,
                    'precioVenta' => $moviment_object->total,

                    'moviment_id' => $moviment_object->id,
                    'reception_id' => null,
                ];
                DetalleMoviment::create($data);

            } else {
                // Procesar la creación o actualización de los detalles
                foreach ($data as $item) {

                    $reception = Reception::find($item['reception_id']);

                    $data = [
                        'description' => $item['description'],
                        'placa' => $reception?->firstCarrierGuide?->tract?->currentPlate ?? '-',
                        'guia' => $reception?->firstCarrierGuide?->numero ?? '-',
                        'os' => (array_key_exists('os', $item) && !is_null($item['os'])) ? $item['os'] : '-',
                        'cantidad' => 1,
                        'tract_id' => $reception?->firstCarrierGuide?->tract->id ?? null,
                        'carrier_guide_id' => $reception?->firstCarrierGuide?->id ?? null,
                        'precioVenta' => $reception?->paymentAmount ?? 0,
                        'moviment_id' => $object->id,
                        'reception_id' => $reception->id,
                    ];
                    DetalleMoviment::create($data);
                }
            }

            // Actualizar la lista de productos
            $jsonData = json_encode($request->data);
            $object->productList = $jsonData;
            $object->save();
        }
        $installments = $request->input('installments') ?? [];
        // if ($request->input('typePayment') == 'Contado') {
        if (empty($installments) && $installments == []) {

            // Preparar los datos para el movimiento
            // Asegúrate de que el objeto exista antes de intentar actualizar
            $object2 = Moviment::withTrashed()->where('mov_id', $object->id)->first();

            if ($object2) {
                $data = [
                    'paymentDate' => $request->input('paymentDate'),
                    'total' => $total ?? 0,
                    'yape' => $request->input('yape') ?? 0,
                    'deposit' => $depositAmount ?? 0,
                    'cash' => $request->input('cash') ?? 0,
                    'card' => $request->input('card') ?? 0,
                    'plin' => $request->input('plin') ?? 0,
                    'comment' => $request->input('comment') ?? '-',
                    'typeDocument' => 'Ingreso',
                    'bank_id' => $bank_id,
                    'isBankPayment' => $request->input('isBankPayment'),
                    'routeVoucher' => $routeVoucher,
                    'numberVoucher' => $numberVoucher,
                    'movType' => 'Caja',
                    'typePayment' => $request->input('typePayment') ?? null,
                    'typeSale' => $request->input('typeSale') ?? '-',
                    'codeDetraction' => $request->input('codeDetraction'),
                    'status' => 'Pagado',
                    'programming_id' => $request->input('programming_id'),
                    'paymentConcept_id' => 3, // venta
                    'branchOffice_id' => $request->input('branchOffice_id'),
                    'reception_id' => $request->input('reception_id'),
                    'person_id' => $request->input('person_id'),
                    // 'user_id' => auth()->id(),
                    // 'box_id' => $request->input('box_id'),
                    'deleted_at' => null,
                ];

                // Actualiza el movimiento existente
                $object2->update($data);
            }

            if ($request->input('typePayment') == 'Contado') {
                // Aquí se asume que necesitas eliminar el movimiento de caja del contado
                $installments = Installment::where('moviment_id', $object->id)
                    ->whereNull('deleted_at')->get();

                if ($installments->isNotEmpty()) {
                    foreach ($installments as $installment) {
                        // Asignar null a los installments relacionados
                        $installment->deleted_at = now(); // Marca como eliminado
                        $installment->save();
                    }
                }
                // Actualizar el estado del objeto original
                $object->status = 'Pagado';
                $object->save();
            }

        } else {

            if (!empty($installments)) {

                $monto = 0;
                $installmentsActually = $object->installments->pluck('id')->toArray(); // Obtiene los IDs actuales de installments
                $processedInstallments = [];                                            // Mantendrá los IDs procesados para verificar luego cuáles eliminar

                foreach ($installments as $installment) {
                    $dias = $installment['date'];
                    if (isset($installment['id'])) {
                        // Actualizar la cuota existente si se encuentra el ID
                        $existingInstallment = Installment::find($installment['id']);
                        if ($existingInstallment) {
                            $existingInstallment->days = $dias;
                            $existingInstallment->date = now()->addDays($dias);
                            $existingInstallment->total = $installment['importe'];
                            $existingInstallment->totalDebt = $installment['importe'];
                            $existingInstallment->save();

                            $processedInstallments[] = $installment['id']; // Agrega el ID a la lista de procesados
                            $monto += $installment['importe'];
                        }
                    } else {
                        // Datos para crear una cuota
                        $data = [
                            'date' => now()->addDays($dias),
                            'days' => $dias,
                            'total' => $installment['importe'],
                            'totalDebt' => $installment['importe'],
                            'moviment_id' => $object->id,
                        ];
                        $monto += $installment['importe'];
                        $newInstallment = Installment::create($data);

                        $processedInstallments[] = $newInstallment->id; // Agrega el nuevo ID a la lista de procesados
                    }
                }

                // Elimina los installments que no se encuentran en los datos recibidos
                $installmentsToDelete = array_diff($installmentsActually, $processedInstallments);
                Installment::whereIn('id', $installmentsToDelete)->delete();

                // Actualiza el monto total en el objeto principal
                $object->total = $monto;
                $object->saldo = $monto;
                $object->save();

                $object->status = 'Pendiente';
                $object->save();

                $movCaja = Moviment::
                    where('movType', 'Caja')
                    ->where('mov_id', $object->id)
                    ->first();
                if ($movCaja) {
                    //ELIMINAR MOVIMIENTO DE CAJA
                    $movCaja->delete();
                }
            }

        }
        $totalDebtSum = Moviment::where('box_id', $request->input('box_id'))
            ->where('movType', 'Venta')
            ->whereDate('paymentDate', now())->get()->sum('total');
        if ($request->input('isValue_ref') == 1 or $request->input('isValue_ref') == true) {

            $object->monto_detraction = $request->input('monto_detraction');
            $object->monto_neto = $request->input('monto_neto');
            $object->value_ref = $request->input('value_ref');
            $object->isValue_ref = $request->input('isValue_ref');
            if ($object->total != 0) {
                $object->percent_ref = $object->monto_detraction / $object->total * 100;
            } else {
                $object->percent_ref = 0; // O cualquier valor por defecto que consideres adecuado
            }
            $object->save();
        }
        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'detailsMoviment',
            'reception.details',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments',
            'installments.payInstallments'
        ])->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),  // ID del usuario que realiza la acción
            'record_id' => $object->id, // El ID del usuario afectado
            'action' => 'PUT',       // Acción realizada
            'table_name' => 'moviments', // Tabla afectada
            'data' => json_encode([
                'payload_request' => $request->all(),  // Datos enviados por el usuario
                'saved_object' => $object           // Objeto guardado en la BD
            ]),
            'description' => 'Actualizar Venta Manual', // Descripción de la acción
            'ip_address' => $request->ip(),            // Dirección IP del usuario
            'user_agent' => $request->userAgent(),     // Información sobre el navegador/dispositivo
        ]);

        return response()->json(["venta" => new VentaResource($object), 'totalSum' => $totalDebtSum], 200);
    }

    public function updateMontos(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [

            'yape' => 'nullable|numeric',
            'deposit' => 'nullable|numeric',
            'cash' => 'nullable|numeric',
            'card' => 'nullable|numeric',
            'plin' => 'nullable|numeric',

        ]);

        $object = Moviment::find($id);
        if (!$object) {
            return response()->json(['message' => 'Venta no Encontrada'], 404);
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $aperturaMovement = Moviment::with(['paymentConcept', 'person', 'user.worker.person'])
            ->where('paymentConcept_id', 1) // Asumiendo que '1' es el ID del concepto de apertura
            ->where('id', '<=', $id)        // Limitar a movimientos anteriores o iguales
            ->orderBy('id', 'desc')
            ->first();
        if (!$aperturaMovement) {
            return response()->json([
                'error' => 'No se encontró un movimiento de apertura para el movimiento dado.',
            ], 404);
        }
        if ($aperturaMovement->status == "Inactiva") {
            return response()->json(['error' => 'La Caja donde está la venta ya fué Cerrada'], 422);
        }
        // Acumular montos
        $efectivo = $request->input('cash') ?? 0;
        $yape = $request->input('yape') ?? 0;
        $plin = $request->input('plin') ?? 0;
        $tarjeta = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;
        $routeVoucher = null;
        $numberVoucher = null;
        $bank_id = null;
        $depositAmount = 0;
        $depositAmount = $request->input('deposit') ?? 0;
        if ($request->input('isBankPayment') == 1) {
            $routeVoucher = 'ruta.jpg';
            $numberVoucher = $request->input('numberVoucher');
            $bank_id = $request->input('bank_id');

        }

        $data = [

            'total' => $total,
            'yape' => $yape,
            'deposit' => $depositAmount,
            'cash' => $efectivo,
            'card' => $tarjeta,
            'plin' => $plin,
            'comment' => $request->input('comment') ?? '-',

            'nroTransferencia' => $request->input('nroTransferencia') ?? '',
            'bank_id' => $bank_id,
            'isBankPayment' => $request->input('isBankPayment'),
            'routeVoucher' => $routeVoucher,
            'numberVoucher' => $numberVoucher,

            'user_edited_id' => Auth::user()->id,

        ];

        // Actualiza el objeto
        $object->update($data);

        //

        $movCaja = Moviment::withTrashed()->where('mov_id', $object->id)->first();

        if ($movCaja) {
            $data = [

                'total' => $total ?? 0,
                'yape' => $request->input('yape') ?? 0,
                'deposit' => $depositAmount ?? 0,
                'cash' => $request->input('cash') ?? 0,
                'card' => $request->input('card') ?? 0,
                'plin' => $request->input('plin') ?? 0,
                'comment' => $request->input('comment') ?? '-',

                'bank_id' => $bank_id,
                'isBankPayment' => $request->input('isBankPayment'),
                'routeVoucher' => $routeVoucher,
                'numberVoucher' => $numberVoucher,
            ];
            // if ($movCaja->trashed()) {
            //     $movCaja->restore(); // Restore the soft-deleted record
            // }
            // Actualiza el movimiento existente
            $movCaja->update($data);
        }
        //

        $totalDebtSum = Moviment::where('box_id', $request->input('box_id'))
            ->where('movType', 'Venta')
            ->whereDate('paymentDate', now())->get()
            ->sum('total');

        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'detailsMoviment',
            'reception.details',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments',
            'installments.payInstallments'
        ])->find($object->id);

        return response()->json(["venta" => $object, 'totalSum' => $totalDebtSum], 200);
    }

    /**
     * Get all Moviments
     * @OA\Get (
     *     path="/transportedev/public/api/sale",
     *     tags={"Sale"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="branch_office_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID de la sucursal para filtrar los movimientos"
     *     ),
     *     @OA\Parameter(
     *         name="typeDocument",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Tipo de documento para filtrar los movimientos"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active Sale",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MovimentRequest")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/sale?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/sale?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/sale"),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="prev_page_url", type="string", example="null"),
     *             @OA\Property(property="to", type="integer", example=15)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch Office Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Branch Office Not Found")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $branch_office_id = $request->input('branch_office_id');
        $typeDocument = $request->input('typeDocument') ?? '';

        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }

        // Parámetros de búsqueda
        $branch_office_id = $request->input('branch_office_id');
        $typeDocument = $request->input('typeDocument');
        $status = $request->input('status');
        $personId = $request->input('person_id');
        $start = $request->input('start'); // Fecha de inicio
        $end = $request->input('end');   // Fecha de fin
        $end = Carbon::parse($end)->addDay()->format('Y-m-d');
        $sequentialNumber = $request->input('sequentialNumber'); // Número secuencial (opcional)

        // Obtener per_page y page de los parámetros de la solicitud
        $perPage = $request->input('per_page', 15); // Valor por defecto es 15
        $page = $request->input('page', 1);      // Valor por defecto es 1

        // Obtener el total de la deuda sin paginación
        $totalDebtSum = $this->calcularTotalDeuda($branch_office_id, $typeDocument, $box_id, $personId, $status, $start, $end, $sequentialNumber);

        // Consulta con paginación
        $movCaja = Moviment::query()
            ->when(!empty($branch_office_id), fn($query) => $query->where('branchOffice_id', $branch_office_id))
            ->when(!empty($typeDocument), fn($query) => $query->where('typeDocument', $typeDocument))
            ->when(!empty($box_id), fn($query) => $query->where('box_id', $box_id))
            ->when(!empty($personId), fn($query) => $query->where('person_id', $personId))
            ->when(!empty($status), fn($query) => $query->where('status', $status))
            ->when(!empty($start), fn($query) => $query->where('paymentDate', '>=', $start))
            ->when(!empty($end), fn($query) => $query->where('paymentDate', '<=', $end))
            ->when(!empty($sequentialNumber), fn($query) => $query->where('sequentialNumber', 'LIKE', "%$sequentialNumber%"))
            ->where('movType', 'Venta')
            ->with([
                'receptions',
                'personreception',
                'branchOffice',
                'paymentConcept',
                'box',
                'detailsMoviment',
                'detalles',
                'reception.details',
                'detalles',
                'person' => function ($query) {
                    $query->withTrashed(); // Incluir personas eliminadas
                },
                'creditNote',
                'user.worker.person',
                'installments',
                'installments.payInstallments',
            ])
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        foreach ($movCaja as $mov) {
            $mov->updateSaldo(); // Ejecutar updateSaldo() en cada registro
        }

        $movCaja->getCollection()->transform(function ($item) {
            // $mov->updateSaldo(); // Ejecutar updateSaldo() en cada registro
            $ventaTotal = $item->total; // Total de la venta

            if ($item->creditNote || $item->status == "Anulada") {
                $notaCreditoTotal = $item->creditNote ? $item->creditNote->total : 0; // Total de la nota de crédito

                // Calculamos el nuevo total, asegurándonos de que no sea negativo
                $item->total = max($ventaTotal - $notaCreditoTotal, 0);

                // Calculamos el saldo, asegurándonos de que no sea negativo
// $item->saldo = max($item->saldo - $notaCreditoTotal, 0);
                $item->saldo = max($item->saldo, 0);

                if ($ventaTotal == $notaCreditoTotal) {
                    $item->status = 'Anulada por Nota: ' . $item->creditNote->number; // Total igual al monto de la nota de crédito
                } else {
                    if ($item->saldo == 0) {
                        $item->status = 'Pagado';
                    } else {
                        $item->status = 'Pendiente';
                    }
                }
                if ($item->creditNote) {
                    // if ($item->creditNote->reason !="13") {
//     $item->status = 'Anulada por Nota: ' . $item->creditNote->number;
// }
                }
                if ($item->status_facturado == "Anulada") {
                    $item->status = 'Anulada';
                }
            }
            return $item;
        });
        // $totalDebtSum = $movCaja->getCollection()->sum('total');

        return response()->json([
            'total' => $movCaja->total(),
            'data' => VentaResource::collection($movCaja->items()),

            'current_page' => $movCaja->currentPage(),
            'last_page' => $movCaja->lastPage(),
            'per_page' => $perPage,
            'page' => $page,
            'pagination' => $perPage,
            'first_page_url' => $movCaja->url(1),
            'from' => $movCaja->firstItem(),
            'next_page_url' => $movCaja->nextPageUrl(),
            'path' => $movCaja->path(),
            'prev_page_url' => $movCaja->previousPageUrl(),
            'to' => $movCaja->lastItem(),
            'totalSum' => $totalDebtSum, // Total sin paginación
        ], 200);
    }
    public function calcularTotalDeuda($branch_office_id = null, $typeDocument = null, $box_id = null, $personId = null, $status = null, $start = null, $end = null, $sequentialNumber = null)
    {
        $totalSumVentas = Moviment::query()
            ->when(!empty($branch_office_id), fn($query) => $query->where('branchOffice_id', $branch_office_id))
            ->when(!empty($typeDocument), fn($query) => $query->where('typeDocument', $typeDocument))
            ->when(!empty($box_id), fn($query) => $query->where('box_id', $box_id))
            ->when(!empty($personId), fn($query) => $query->where('person_id', $personId))
            ->when(!empty($status), fn($query) => $query->where('status', $status))
            ->when(!empty($start), fn($query) => $query->where('paymentDate', '>=', $start))
            ->when(!empty($end), fn($query) => $query->where('paymentDate', '<=', $end))
            ->when(!empty($sequentialNumber), fn($query) => $query->where('sequentialNumber', 'LIKE', "%$sequentialNumber%"))
            ->where('movType', 'Venta')
            ->with('creditNote')
            ->get()
            ->sum('total');

        $totalSumNC = CreditNote::whereBetween('created_at', [$start, $end])
            ->get()
            ->sum('total');

        return $totalSumVentas - $totalSumNC;
    }

    public function show($id)
    {
        $object = Moviment::find($id);

        if (!$object) {
            return response()->json(['message' => 'Venta no Encontrada'], 422);
        }

        $object = Moviment::with([
            'receptions',
            'personreception',
            'branchOffice',
            'paymentConcept',
            'box',
            'detalles',
            'person',
            'bank',
            'user.worker.person',
            'movVenta',
            'installments'
        ])->find($object->id);

        return response()->json($object, 200);
    }

    /**
     * Get all Moviments without CreditNote
     *
     * @OA\Get (
     *     path="/transportedev/public/api/saleWithoutCreditNote",
     *     tags={"Sale"},
     *     summary="Get Sales Moviments without Credit Notes",
     *     description="Retrieve a list of sales movements that do not have an associated credit note. You can filter the results by branch office, document type, and sequential number.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="branch_office_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID of the branch office to filter movements. If not provided, the user's branch office will be used."
     *     ),
     *     @OA\Parameter(
     *         name="typeDocument",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Document type to filter movements (e.g., invoice, receipt)."
     *     ),
     *     @OA\Parameter(
     *         name="sequentialNumber",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Sequential number to search for in the movements."
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of Moviments without Credit Notes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MovimentRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch Office Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Branch Office Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     )
     * )
     */

    public function saleWithoutCreditNote(Request $request)
    {
        $branch_office_id = $request->input('branch_office_id');
        $typeDocument = $request->input('typeDocument');
        $sequentialNumber = $request->input('sequentialNumber');
        $idNotaCredito = $request->input('idNotaCredito'); // Capturar el ID de la nota de crédito
        $nota = '';
        if ($idNotaCredito) {
            $nota = CreditNote::find($idNotaCredito);
        }

        // Validar la existencia de la sucursal
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (!$branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }
        // else {
        //     $branch_office_id = auth()->user()->worker->branchOffice_id;
        //     $branchOffice = BranchOffice::find($branch_office_id);
        // }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (!$box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }
        // else {
        //     $box_id = auth()->user()->box_id;
        //     $box = Box::find($box_id);
        // }

        // Realizar la consulta con los filtros
        $movCaja = Moviment::with(['person'])
            // ->where('branchOffice_id', $branch_office_id)
            ->when(!empty($typeDocument), function ($query) use ($typeDocument) {
                return $query->where('typeDocument', $typeDocument);
            })
            ->when(!empty($sequentialNumber), function ($query) use ($sequentialNumber) {
                return $query->where('sequentialNumber', 'LIKE', "%{$sequentialNumber}%"); // Filtro por sequentialNumber
            })
            //  ->when(!empty($box_id), function ($query) use ($box_id) {
            //      return $query->where('box_id', $box_id);
            //  })
            //  ->when(!empty($branch_office_id), function ($query) use ($branch_office_id) {
            //      return $query->where('branchOffice_id', $branch_office_id);
            //  })
            ->where(function ($query) {
                // Filtrar los sequentialNumber que empiezan con 'F' o 'B'
                $query->where('sequentialNumber', 'LIKE', 'F%')
                    ->orWhere('sequentialNumber', 'LIKE', 'B%');
            })
            ->where('movType', 'Venta')
            ->where('status_facturado', 'Enviado')
            ->where(function ($query) use ($idNotaCredito, $nota) {
                // Incluir movimientos que no tienen nota de crédito o que están asociados a la nota de crédito actual
                $query->doesntHave('creditNote'); // Movimientos sin nota de crédito
    
                if ($nota) {
                    $query->orWhereHas('creditNote', function ($subQuery) use ($idNotaCredito) {
                        $subQuery->where('id', $idNotaCredito); // Movimientos asociados a la nota de crédito actual
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return response()->json($movCaja, 200);
    }

    public function salesbynumber(Request $request)
    {

        $sequentialNumber = $request->input('sequentialNumber', '');

        $movCaja = Moviment::select('id', 'sequentialNumber')
            ->when(!empty($sequentialNumber), function ($query) use ($sequentialNumber) {
                return $query->where('sequentialNumber', 'LIKE', "%{$sequentialNumber}%"); // Filtro por sequentialNumber
            })

            ->where(function ($query) {
                // Filtrar los sequentialNumber que empiezan con 'F' o 'B'
                $query->where('sequentialNumber', 'LIKE', 'F%')
                    ->orWhere('sequentialNumber', 'LIKE', 'B%')
                    ->orWhere('sequentialNumber', 'LIKE', 'T%');
            })
            ->where('movType', 'Venta')
            // ->where('status_facturado', 'Enviado')

            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return response()->json($movCaja, 200);
    }

    /**
     * Get all Moviments
     * @OA\Get (
     *     path="/transportedev/public/api/saleIdNumber",
     *     tags={"Sale"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="branch_office_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID de la sucursal para filtrar los movimientos"
     *     ),
     *     @OA\Parameter(
     *         name="typeDocument",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Tipo de documento para filtrar los movimientos"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Moviments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="sequentialNumber", type="string", example="20210001")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch Office Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Branch Office Not Found")
     *         )
     *     )
     * )
     */
    public function saleIdNumber(Request $request)
    {
        $branch_office_id = $request->input('branch_office_id') ?? '';
        $typeDocument = $request->input('typeDocument') ?? '';

        // if ($branch_office_id && is_numeric($branch_office_id)) {
        //     $branchOffice = BranchOffice::find($branch_office_id);
        //     if (!$branchOffice) {
        //         return response()->json([
        //             "message" => "Branch Office Not Found",
        //         ], 404);
        //     }
        // } else {
        //     $branch_office_id = auth()->user()->worker->branchOffice_id;
        //     $branchOffice = BranchOffice::find($branch_office_id);
        // }

        $movCaja = Moviment::
            when($typeDocument != '', function ($query) use ($typeDocument) {
                return $query->where('typeDocument', $typeDocument);
            })
            ->when($branch_office_id != '', function ($query) use ($branch_office_id) {
                return $query->where('branchOffice_id', $branch_office_id);
            })
            // ->where(function ($query) {
            //     $query->where('sequentialNumber', 'like', 'B%')
            //         ->orWhere('sequentialNumber', 'like', 'F%');
            // })
            ->where('movType', 'Venta')
            ->orderBy('id', 'desc')
            ->with([
                'receptions',
                'personreception',
                'branchOffice',
                'paymentConcept',
                'box',
                'detailsMoviment',
                'reception.details',
                'person',
                'user.worker.person'
            ])->where('movType', 'Venta')
            ->get();

        return response()->json($movCaja, 200);
    }

    /**
     * Get all Receptions without a Sale
     * @OA\Get (
     *     path="/transportedev/public/api/receptionWithoutSale",
     *     tags={"Sale"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="branch_office_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID de la sucursal para filtrar las recepciones"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Receptions without a Sale",

     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch Office Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Branch Office Not Found")
     *         )
     *     )
     * )
     */
    public function receptionWithoutSale(Request $request)
    {
        $branch_office_id = $request->input('branch_office_id') ?? '';

        $list = Reception::with(
            'user',
            'origin',
            'sender',
            'destination',
            'recipient',
            'pickupResponsible',
            'payResponsible',
            'seller',
            'pointDestination',
            'pointSender',
            'details'
        )
            // ->whereDoesntHave('moviment')
            ->where('debtAmount', ">", 0)
            ->when($branch_office_id != '', function ($query) use ($branch_office_id) {
                $query->where('branchOffice_id', $branch_office_id);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($list, 200);
    }

    public function getArchivosDocument($idventa, $typeDocument)
    {
        // Habilitar CORS para un origen específico
        header("Access-Control-Allow-Origin: https://transportes-hernandez-dev.vercel.app"); // Permitir solo este origen
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");                          // Permitir métodos HTTP específicos
        header("Access-Control-Allow-Headers: Content-Type, Authorization");                 // Permitir tipos de encabezados específicos

        // Si es una solicitud OPTIONS (preflight), responde sin ejecutar más lógica
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200); // Código de éxito
            exit;                    // Termina el script aquí
        }

        $funcion = 'buscarNumeroSolicitud';
        $url = 'https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php?funcion=' . $funcion . "&typeDocument=" . $typeDocument;

        // Parámetros para la solicitud
        $params = http_build_query(['idventa' => $idventa]);

        // Inicializamos cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '&' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutamos la solicitud y obtenemos la respuesta
        $response = curl_exec($ch);

        // Cerramos cURL
        curl_close($ch);

        // Verificamos si la respuesta es válida
        if ($response !== false) {
            // Decodificamos la respuesta JSON
            $data = json_decode($response, true);

            // Verificamos si la respuesta contiene la información del archivo XML
            if (isset($data['xml'])) {
                $xmlFile = $data['xml'];

                // Ruta completa del archivo XML
                $fileUrl = 'https://develop.garzasoft.com:81/transporteFacturadorZip/ficheros/' . $xmlFile;

                // Obtener el contenido del archivo XML
                $fileContent = file_get_contents($fileUrl);

                if ($fileContent !== false) {
                    // Forzar la descarga del archivo XML
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/xml');
                    header('Content-Disposition: attachment; filename="' . basename($xmlFile) . '"');
                    header('Content-Length: ' . strlen($fileContent));

                    // Enviar el contenido del archivo
                    echo $fileContent;
                    exit;
                } else {
                    echo 'Error al descargar el archivo XML.';
                }
            } else {
                echo 'Archivo XML no encontrado.';
            }
        } else {
            echo 'Error en la solicitud.';
        }
    }

    public function getNextCorrelative($prefix, $box_id)
    {
        if (!in_array($prefix, ['B', 'F', 'T'])) {
            return response()->json([
                "message" => "El prefijo debe ser 'B', 'F' o 'T'.",
            ], 422);
        }

        // Validar que el box_id exista en la tabla 'boxes'
        $box = Box::find($box_id);
        if (!$box) {
            return response()->json([
                "message" => "La caja enviada no Existe",
            ], 422);
        } else {
            if ($box->serie == null) {
                return response()->json([
                    "message" => "La caja no tiene serie",
                ], 422);
            }
        }
        $prefix = $prefix . str_pad($box->serie, 3, '0', STR_PAD_LEFT); // Suponiendo que 'series' es el campo de la serie de la caja

        // Obtener el último movimiento con el tipo "Venta" y el número secuencial que empieza con el prefijo dado
        $lastMovement = DB::table('moviments')
            ->where('movType', 'Venta')
            ->whereNull('deleted_at')
            ->where('sequentialNumber', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        // Verificar si encontramos un movimiento con el prefijo dado
        if ($lastMovement) {

            $lastSequentialNumber = $lastMovement->sequentialNumber;
            $lastNumber = substr($lastSequentialNumber, strrpos($lastSequentialNumber, '-') + 1);
            $serie = substr($lastSequentialNumber, 0, 4);
            // Verificar si el número extraído es numérico
            if (is_numeric($lastNumber)) {
                // Incrementar el número
                $nextNumber = str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT); // Mantener 8 dígitos

                // Generar el siguiente número secuencial con el prefijo y el guion
                $nextSequentialNumber = $serie . '-' . $nextNumber;
            } else {
                // Si el valor no es numérico, manejar el error o devolver un valor por defecto
                $nextSequentialNumber = $serie . '-00000001'; // Valor predeterminado si no es numérico
            }
        } else {
            $nextSequentialNumber = $prefix . '-00000001';
        }

        return $nextSequentialNumber;
    }

    public function updateNroVenta(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [

            'nro_sale' => 'nullable|string',

        ]);

        $object = Reception::find($id);
        if (!$object) {
            return response()->json(['message' => 'Venta no Encontrada'], 404);
        }
        // if ($object->nro_sale != null) {
        //     return response()->json(['message' => 'Esta Venta ya tiene registrado un Número de Venta'], 404);
        // }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'nro_sale' => $request->input('nro_sale') ?? null,

        ];

        // Actualiza el objeto
        $object->update($data);

        Bitacora::create([
            'user_id' => Auth::id(),   // ID del usuario que realiza la acción
            'record_id' => $object->id,  // El ID del usuario afectado
            'action' => 'PUT',        // Acción realizada
            'table_name' => 'receptions', // Tabla afectada
            'data' => json_encode($object),
            'description' => 'Actualizar nro Venta', // Descripción de la acción
            'ip_address' => $request->ip(),         // Dirección IP del usuario
            'user_agent' => $request->userAgent(),  // Información sobre el navegador/dispositivo
        ]);
        $object = Reception::find($object->id);

        return response()->json(["reception" => $object], 200);
    }

    public function getStatusFacturacion(Request $request)
    {
        $nombre = $request->input('nombre');
        if (empty($nombre)) {
            return response()->json(['error' => 'El campo Nombre es Requerido'], 422);
        }

        $funcion = "getstatusservidor";

        // Construir la URL con los parámetros
        $url = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion' => $funcion,
            'nombresolicitud' => $nombre,
            'empresa_id' => 437,
            'fecini' => '',
            'fecfin' => '',
        ];
        $url .= '?' . http_build_query($params);

        // Inicializar cURL
        $ch = curl_init();

        // Configurar opciones cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($ch);
        $data = [];

        // Verificar si ocurrió algún error
        if (curl_errno($ch)) {
            $error = curl_error($ch);

            // Definir la respuesta de error
            $data = [
                "mensaje" => 'Error',
                "nombre-solicitado" => $nombre,
                "response" => [],
                "status" => null,
            ];
        } else {
            // Verificar si la respuesta está vacía o no es válida
            if (empty($response)) {

                $data = [
                    "mensaje" => 'Error',
                    "nombre-solicitado" => $nombre,
                    "response" => [],
                    "status" => null,
                ];
            } else {
                $responseArray = json_decode($response, true); // Convertir JSON a array

                // Verificar si 'data' existe en la respuesta
                $status = isset($responseArray['data'][0]['descripcion']) ? $responseArray['data'][0]['descripcion'] : null;

                $data = [
                    "mensaje" => 'Correcto',
                    "nombre-solicitado" => $nombre,
                    "response" => $responseArray,
                    "status" => $status,
                ];
            }
        }

        // Cerrar cURL
        curl_close($ch);

        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportedev/public/api/getSalesPendientesByPerson",
     *     summary="Obtener cuotas pendientes de pago por cliente",
     *     description="Obtiene las cuotas pendientes de pago de un cliente específico, filtrando por el nombre del cliente. Verifica el token de autorización antes de proceder.",
     *     tags={"Sale"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="namesCadena",
     *         in="query",
     *         required=true,
     *         description="Nombre del cliente para filtrar las ventas pendientes",
     *         @OA\Schema(
     *             type="string",
     *             example="agrolmos"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Person")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación (cliente no encontrado)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cliente no encontrado o sin ventas pendientes")
     *         )
     *     )
     * )
     */

    public function getSalesPendientesByPerson(Request $request)
    {
        $name = $request->namesCadena ?? '';
        $moviment = $this->movimentService->getSalesPendientesByPerson($name);
        return $moviment;
    }

    /**
     * @OA\Post(
     *     path="/transportedev/public/api/paymasive",
     *     summary="Realiza un pago masivo por cuotas pendientes",
     *     description="Este endpoint permite realizar un pago masivo para cuotas de un determinado cliente. Cada pago es validado para no superar el monto de deuda total de la cuota.",
     *     tags={"Sale"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="paymasive",
     *                 type="array",
     *                 description="Array de pagos masivos",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="installment_id", type="integer", example=1, description="ID de la cuota"),
     *                     @OA\Property(property="amount", type="number", format="float", example=100.00, description="Monto a pagar")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pago masivo realizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Pago masivo procesado correctamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación - Monto excede la deuda total",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El monto ingresado (150.00) no puede ser mayor a la deuda total (100.00).")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado - El token de autenticación es inválido o ha expirado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autorizado")
     *         )
     *     )
     * )
     */

    public function paymasivesalebyinstallmentdebt(PayMasiveSaleRequest $request)
    {

        $validatedData = $request->validated();
        Bitacora::create([
            'user_id' => Auth::id(),  // ID del usuario que realiza la acción
            'record_id' => null,        // El ID del usuario afectado
            'action' => 'POST',      // Acción realizada
            'table_name' => 'paymasive', // Tabla afectada
            'data' => json_encode($validatedData),
            'description' => 'Pago Masivo',         // Descripción de la acción
            'ip_address' => $request->ip(),        // Dirección IP del usuario
            'user_agent' => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);

        $response = $this->movimentService->setPayMasiveInstallments($validatedData);
        return $response;
    }
}
