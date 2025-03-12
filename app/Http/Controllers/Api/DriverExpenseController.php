<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Box;
use App\Models\DriverExpense;
use App\Models\Moviment;
use App\Models\Programming;
use App\Models\User;
use App\Models\Worker;
use App\Services\BankMovementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriverExpenseController extends Controller
{

    protected $bankmovementService;

    public function __construct(BankMovementService $BankMovementService)
    {
        $this->bankmovementService = $BankMovementService;
    }

    /**
     * Get all DriverExpenses
     * @OA\Get (
     *     path="/transporte/public/api/driverExpense",
     *     tags={"DriverExpense"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="programming_id",
     *         in="query",
     *         description="ID to filter driver expenses",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active DriverExpenses",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DriverExpense")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/driverExpense?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/driverExpense?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/driverExpense"),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="prev_page_url", type="string", example="null"),
     *             @OA\Property(property="to", type="integer", example=15)
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

    public function validarUser($id_user_programming)
    {
        // Obtener el ID del usuario logueado
        $id_user_logged = auth()->id();

        // Verificar si el usuario logueado coincide con el proporcionado
        if ($id_user_logged == $id_user_programming) {
            return true; // Usuarios coinciden
        }

        return false; // Usuarios no coinciden
    }

    public function index(Request $request)
    {
        // Obtener parámetros de la solicitud, con valores predeterminados vacíos
        $programming_id = $request->input('programming_id', '');
        $per_page       = $request->input('per_page', 50); // Tamaño de página con valor predeterminado
        $page           = $request->input('page', 1);      // Página que se quiere mostrar, valor por defecto es 1
        $nameConcept    = $request->input('nameConcept');
        $typeConcept    = $request->input('typeConcept'); // Filtrar dentro de expensesConcept
        $typePay        = $request->input('typePay');
        $drivers        = $request->input('driver'); // Nombre del conductor

        // Construir la consulta base con los filtros
        $query = DriverExpense::query()
            ->when(! empty($programming_id), function ($query) use ($programming_id) {
                return $query->where('programming_id', $programming_id);
            })
            ->when(! empty($typePay), function ($query) use ($typePay) {
                return $query->where('selectTypePay', $typePay);
            })
            ->when(! empty($nameConcept), function ($query) use ($nameConcept) {
                return $query->whereHas('expensesConcept', function ($q) use ($nameConcept) {
                    $q->where('name', 'like', '%' . $nameConcept . '%');
                });
            })
            ->when(! empty($typeConcept), function ($query) use ($typeConcept) {
                return $query->whereHas('expensesConcept', function ($q) use ($typeConcept) {
                    $q->where('typeConcept', $typeConcept);
                });
            })
            ->whereHas('programming.detailsWorkers', function ($query) {
                $query->where('function', 'driver');
            })
            ->when(! empty($drivers), function ($query) use ($drivers) {
                return $query->whereHas('programming.detailsWorkers.worker.person', function ($q) use ($drivers) {
                    $q->whereRaw('LOWER(CONCAT(names, " ", fatherSurname, " ", motherSurname)) LIKE ?', ['%' . strtolower($drivers) . '%']);
                });
            });

        // Clonar la consulta para calcular los totales de ingreso y egreso
        $totalIngreso = (clone $query)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Ingreso');
            // $q->where('selectTypePay', 'Efectivo')
            //     ->orWhere('selectTypePay', 'Descuento_sueldo')
            //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
            ;
        })->sum('total');

        $totalEgreso = (clone $query)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Egreso');
            // $q->where('selectTypePay', 'Efectivo')
            //     ->orWhere('selectTypePay', 'Descuento_sueldo')
            //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
            ;
        })->sum('total');

        // Calcular el saldo (diferencia entre ingresos y egresos)
        $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');

        // Paginar la lista de resultados
        $list = $query->with(['programming', 'expensesConcept', 'worker.person'])
            ->orderBy('id', 'desc')
            ->paginate($per_page, ['*'], 'page', $page);

        // Estructurar la respuesta con la paginación y los totales
        $response = [
            'total'             => $list->total(),
            'data'              => $list->items(),
            'current_page'      => $list->currentPage(),
            'last_page'         => $list->lastPage(),
            'per_page'          => $list->perPage(),
            'pagination'        => $per_page,
            'first_page_url'    => $list->url(1),
            'from'              => $list->firstItem(),
            'next_page_url'     => $list->nextPageUrl(),
            'path'              => $list->path(),
            'prev_page_url'     => $list->previousPageUrl(),
            'to'                => $list->lastItem(),
            'current_page_size' => count($list->items()),
            // Agregar totales de ingreso, egreso y saldo
            'total_ingreso'     => number_format($totalIngreso, 2, '.', ''),
            'total_egreso'      => number_format($totalEgreso, 2, '.', ''),
            'saldo'             => number_format((float) $saldo, 2, '.', ''),

        ];

        // Devolver la respuesta JSON
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/transporte/public/api/driverExpense",
     *     summary="Get all driverExpense",
     *     tags={"DriverExpense"},
     *     description="Show all driverExpense by programming",
     * security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of driverExpense by programming",
     *        @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/DriverExpense")
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

    /**
     * @OA\Post(
     *     path="/transporte/public/api/driverExpense",
     *     summary="Store a new driverExpense",
     *     tags={"DriverExpense"},
     *     description="Create a new driverExpense",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="DriverExpense data",
     *         @OA\JsonContent(
     *             @OA\Property(property="programming_id", type="integer", example=1, description="ID of the programming"),
     *             @OA\Property(property="expensesConcept_id", type="integer", example=1, description="ID of the expenses concept"),
     *             @OA\Property(property="worker_id", type="integer", example=1, description="ID of driver"),

     *             @OA\Property(property="amount", type="number", format="float", example=50.00, description="Amount of the expense"),
     *             @OA\Property(property="quantity", type="integer", example=2, description="Quantity of items"),
     *             @OA\Property(property="place", type="string", example="Station X", description="Place where the expense occurred"),
     *             @OA\Property(property="km", type="integer", example=100, description="Kilometers at the time of expense"),
     *             @OA\Property(property="image", type="string", example="file", description="file"),
     *
     *             @OA\Property(property="comment", type="string", example="Refueling in station X", description="Additional comments"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DriverExpense created successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/DriverExpense"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Some fields are required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'programming_id'     => 'required|exists:programmings,id',
            'expensesConcept_id' => 'required|exists:expenses_concepts,id',
            'transaction_concept_id' => 'required|exists:transaction_concepts,id,deleted_at,NULL',
            'worker_id'          => 'required|exists:workers,id',
            'amount'             => 'required',
            // 'quantity' => 'required',
            'bank_id'            => 'nullable|exists:banks,id',
            'proveedor_id'       => 'nullable|exists:people,id',
            'bank_account_id'    => [
                'nullable',
                Rule::exists('bank_accounts', 'id')->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $programming = Programming::find($request->input('programming_id'));

        if ($request->input('gallons') && $request->input('amount')) {
            $total = ($request->input('amount') ?? 0) * ($request->input('gallons') ?? 1);
        } else {
            $total = ($request->input('amount') ?? 0) * ($request->input('quantity') ?? 1);
        }

        $worker = Worker::find($request->input('worker_id'));

        $personWorker = $worker->person;
        if (! $personWorker) {
            return response()->json(['error' => 'No esta registrado en Personas el conductor'], 422);
        }

        $efectivo = $total;
        $yape     = $request->input('yape') ?? 0;
        $plin     = $request->input('plin') ?? 0;
        $tarjeta  = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $totalAcum = $efectivo + $yape + $plin + $tarjeta + $deposito;

        $user = User::find(Auth()->id());

        // if ($user->typeofUser_id != 4) { //DEBE SER CAJERO
        //     return response()->json(['error' => 'El usuario debe ser del tipo Cajero'], 422);
        // }
        if (! $user->box) {
            return response()->json(['error' => 'El usuario no tiene una caja'], 422);
        }

        $box = Box::find($user->box->id);
        if ($box->status != 'Activa') {
            return response()->json(['error' => 'Caja No está Aperturada'], 422);
        }

        $tipopago = $request->input('selectTypePay');

        $data = [
            'programming_id'     => $request->input('programming_id'),
            'worker_id'          => $worker->id,
            'expensesConcept_id' => $request->input('expensesConcept_id'),
            'igv'                => $request->input('igv'),
            'gravado'            => $request->input('gravado'),
            'exonerado'          => $request->input('exonerado'),
            'date_expense'       => $request->input('date_expense'),
            'selectTypePay'      => $tipopago,

            'total'              => $total,
            'amount'             => $request->input('amount'),
            'bank_account_id'    => $request->input('bank_account_id'),
            'quantity'           => $request->input('quantity') ?? 1,
            'place'              => $request->input('place'),
            'km'                 => $request->input('km'),
            'operationNumber'    => $request->input('operationNumber'),
            'bank_id'            => $request->input('bank_id'),
            'proveedor_id'       => $request->input('proveedor_id'),
            'routeFact'          => 'ruta.jpg',
            'gallons'            => $request->input('gallons'),
            'comment'            => $request->input('comment'),
            'isMovimentCaja'     => $request->input('isMovimentCaja'),
        ];

        $objectExpense = DriverExpense::create($data);
        $bank_account  = BankAccount::find($request->input(key: 'bank_account_id'));
        if ($bank_account != null) {
            $data_movement_bank = [
                'driver_expense_id'      => $objectExpense->id,
                'bank_id'                => $request->input('bank_id'),
                'bank_account_id'        => $bank_account->id,
                'currency'               => $bank_account->currency,
                'date_moviment'          => $request->input('date_expense'),
                'total_moviment'         => $totalAcum,
                'comment'                => $request->input('comment'),
                'user_created_id'        => $user->id,
                'transaction_concept_id' => $request->input('transaction_concept_id'),
                'person_id'              => $objectExpense->worker->person->id,
                'type_moviment'          => 'SALIDA',
            ];
            $this->bankmovementService->createBankMovement($data_movement_bank);
        }

        $programming = Programming::find($request->input('programming_id'));

        $image = $request->file('image');

        if ($image) {
            Log::info('Imagen recibida: ' . $image->getClientOriginalName());
            $file        = $image;
            $currentTime = now();
            $filename    = $currentTime->format('YmdHis') . '_' . $file->getClientOriginalName();

            $path = $file->storeAs('public/photosDrivers', $filename);

            Log::info('Imagen almacenada en: ' . $path);
            $rutaImagen = Storage::url($path);

            $objectExpense->routeFact = $rutaImagen;
            $objectExpense->save();
            Log::info('Imagen guardada en la base de datos con ruta: ' . $rutaImagen);
        }
        $tipo = 'M' . str_pad($programming->branchOffice_id, 3, '0', STR_PAD_LEFT);

        $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);

        $resultado    = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(sequentialNumber, LOCATE("-", sequentialNumber) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM moviments WHERE SUBSTRING(sequentialNumber, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;



        if ($request->input('expensesConcept_id') == 1) { //GENERA MOVIMEITNO EGRESO DE CAJA

            if ($request->input('isMovimentCaja') == 1) {
                $data = [

                    'sequentialNumber'  => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                    'paymentDate'       => $request->input('date_expense') ?? Carbon::now(),
                    'total'             => $totalAcum ?? 0,
                    'yape'              => $request->input('yape') ?? 0,
                    'deposit'           => $request->input('deposit') ?? 0,
                    'cash'              => $efectivo ?? 0,
                    'card'              => $request->input('card') ?? 0,
                    'plin'              => $request->input('plin') ?? 0,

                    'comment'           => $request->input('comment') ?? '-',
                    'typeDocument'      => 'Egreso',

                    'typePayment'       => $request->input('typePayment') ?? null,
                    'typeSale'          => $request->input('typeSale') ?? '-',
                    'status'            => 'Generada',
                    'programming_id'    => $programming->id,
                    'paymentConcept_id' => 4, //EGRESO MONTO DE VIAJE
                    'branchOffice_id'   => $programming->branchOffice_id,
                    'reception_id'      => $request->input('reception_id'),
                    'bank_id'           => $request->input('bank_id'),
                    'person_id'         => $personWorker->id,
                    'user_id'           => auth()->id(),
                    'box_id'            => $user->box->id,
                    'driverExpense_id'  => $objectExpense->id,
                ];

                $object = Moviment::create($data);

            }

            $objectExpense->selectTypePay = 'Efectivo';
            $objectExpense->save();
            $programming->updateTotalDriversExpenses();

        } else {
            if ($request->input('isMovimentCaja') == 1) {
                $data = [

                    'sequentialNumber'  => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                    'paymentDate'       => $request->input('date_expense') ?? Carbon::now(),
                    'total'             => $totalAcum ?? 0,
                    'yape'              => $request->input('yape') ?? 0,
                    'deposit'           => $request->input('deposit') ?? 0,
                    'cash'              => $efectivo ?? 0,
                    'card'              => $request->input('card') ?? 0,
                    'plin'              => $request->input('plin') ?? 0,

                    'comment'           => $request->input('comment') ?? '-',
                    'typeDocument'      => 'Egreso',

                    'typePayment'       => $request->input('typePayment') ?? null,
                    'typeSale'          => $request->input('typeSale') ?? '-',
                    'status'            => 'Generada',
                    'programming_id'    => $programming->id,
                    'paymentConcept_id' => 4, //EGRESO MONTO DE VIAJE
                    'branchOffice_id'   => $programming->branchOffice_id,
                    'reception_id'      => $request->input('reception_id'),
                    'bank_id'           => $request->input('bank_id'),
                    'person_id'         => $personWorker->id,
                    'user_id'           => auth()->id(),
                    'box_id'            => $user->box->id,
                    'driverExpense_id'  => $objectExpense->id,
                ];

                $object = Moviment::create($data);

            }

            $programming->updateTotalDriversExpenses();

        }

        $totalIngreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Ingreso');
            // $q->where('selectTypePay', 'Efectivo')
            // ->orWhere('selectTypePay', 'Descuento_sueldo')
            // ->orWhere('selectTypePay', 'Proxima_liquidacion')
            ;
        })->sum('total');

        $totalEgreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Egreso');
            // $q->where('selectTypePay', 'Efectivo')
            // ->orWhere('selectTypePay', 'Descuento_sueldo')
            // ->orWhere('selectTypePay', 'Proxima_liquidacion')
            ;
        })->sum('total');

        // Calcular el saldo (diferencia entre ingresos y egresos)
        $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');

        $object =
        DriverExpense::with(['programming', 'expensesConcept', 'worker.person', 'bank'])->find($objectExpense->id);

        return response()->json(['data' => $object,
            'total_ingreso'                 => number_format($totalIngreso, 2, '.', ''),
            'total_egreso'                  => number_format($totalEgreso, 2, '.', ''),
            'saldo'                         => number_format((float) $saldo, 2, '.', ''),
        ], 200);
    }
/**
 * @OA\Post(
 *     path="/transporte/public/api/devolverMontoaCaja",
 *     summary="Return an amount to the cash box",
 *     tags={"DriverExpense"},
 *     description="Store a new driverExpense and create a related movement",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="DriverExpense and movement data",
 *         @OA\JsonContent(
 *             @OA\Property(property="programming_id", type="integer", example=1, description="ID of the programming"),
 *             @OA\Property(property="worker_id", type="integer", example=1, description="ID of the driver"),
 *             @OA\Property(property="cash", type="number", format="float", example=100.00, description="Cash amount returned"),
 *             @OA\Property(property="yape", type="number", format="float", example=50.00, description="Yape amount returned"),
 *             @OA\Property(property="plin", type="number", format="float", example=20.00, description="Plin amount returned"),
 *             @OA\Property(property="card", type="number", format="float", example=30.00, description="Card amount returned"),
 *             @OA\Property(property="deposit", type="number", format="float", example=40.00, description="Deposit amount returned"),
 *             @OA\Property(property="bank_id", type="integer", example=1, description="ID of the bank used"),
 *             @OA\Property(property="proveedor_id", type="integer", example=1, description="ID of the provider"),
 *             @OA\Property(property="paymentDate", type="string", format="date-time", example="2024-11-25T15:30:00Z",description="Payment date"),
 *             @OA\Property(property="comment", type="string", example="Returning remaining funds", description="Additional comments"),
 *             @OA\Property(property="image", type="string", format="binary", description="Image file related to the expense"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="DriverExpense created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1, description="ID of the created driverExpense"),
 *             @OA\Property(property="programming_id", type="integer", example=1, description="ID of the programming"),
 *             @OA\Property(property="worker_id", type="integer", example=1, description="ID of the driver"),
 *             @OA\Property(property="total", type="number", format="float", example=240.00, description="Total amount returned"),
 *             @OA\Property(property="routeFact", type="string", example="public/photosDrivers/20241125123000_image.jpg", description="Path to the stored image"),
 *             @OA\Property(property="comment", type="string", example="Returning remaining funds", description="Comment added"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Validation error message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

    public function devolverMontoaCaja(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'programming_id' => 'required|exists:programmings,id',
            'worker_id'      => 'required|exists:workers,id',

            'yape'           => 'nullable|numeric',
            'deposit'        => 'nullable|numeric',
            'cash'           => 'nullable|numeric',
            'plin'           => 'nullable|numeric',
            'card'           => 'nullable|numeric',
            'bank_id'        => 'nullable|exists:banks,id',
            'proveedor_id'   => 'nullable|exists:people,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $worker = Worker::find($request->input('worker_id'));

        $personWorker = $worker->person;
        if (! $personWorker) {
            return response()->json(['error' => 'No esta registrado en Personas el conductor'], 422);
        }

        $user = User::find(Auth()->id());

        if (! $user->box) {
            return response()->json(['error' => 'El usuario no tiene una caja'], 422);
        }

        $efectivo = $request->input('cash') ?? 0;
        $yape     = $request->input('yape') ?? 0;
        $plin     = $request->input('plin') ?? 0;
        $tarjeta  = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        $box = Box::find($user->box->id);
        if ($box->status != 'Activa') {
            return response()->json(['error' => 'Caja No está Aperturada'], 422);
        }

        $data = [
            'programming_id'     => $request->input('programming_id'),
            'worker_id'          => $worker->id,
            'expensesConcept_id' => 21, // DEVUELTO A CAJA
            'igv'                => $request->input('igv'),
            'gravado'            => $request->input('gravado'),
            'exonerado'          => $request->input('exonerado'),
            'selectTypePay'      => $request->input('selectTypePay') ?? "Efectivo",
            'date_expense'       => $request->input('date_expense') ?? Carbon::now(),

            'total'              => $total,
            'amount'             => $request->input('amount') ?? 0,
            'quantity'           => $request->input('quantity') ?? 1,
            'place'              => $request->input('place'),
            'km'                 => $request->input('km'),
            'operationNumber'    => $request->input('operationNumber'),
            'bank_id'            => $request->input('bank_id'),
            'proveedor_id'       => $request->input('proveedor_id'),
            'bank_account_id'    => $request->input('bank_account_id'),

            'routeFact'          => 'ruta.jpg',
            'gallons'            => $request->input('gallons'),
            'comment'            => $request->input('comment'),
            'isMovimentCaja'     => $request->input('isMovimentCaja') ?? 1,
        ];

        $objectExpense = DriverExpense::create($data);
        $programming   = Programming::find($request->input('programming_id'));

        $image = $request->file('image');

        if ($image) {
            Log::info('Imagen recibida: ' . $image->getClientOriginalName());
            $file        = $image;
            $currentTime = now();
            $filename    = $currentTime->format('YmdHis') . '_' . $file->getClientOriginalName();

            $path = $file->storeAs('public/photosDrivers', $filename);

            Log::info('Imagen almacenada en: ' . $path);
            $rutaImagen = Storage::url($path);

            $objectExpense->routeFact = $rutaImagen;
            $objectExpense->save();
            Log::info('Imagen guardada en la base de datos con ruta: ' . $rutaImagen);
        }
        $tipo = 'M' . str_pad($programming->branchOffice_id, 3, '0', STR_PAD_LEFT);

        $tipo = str_pad($tipo, 4, '0', STR_PAD_RIGHT);

        $resultado    = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(sequentialNumber, LOCATE("-", sequentialNumber) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM moviments WHERE SUBSTRING(sequentialNumber, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $efectivo = $total;
        $yape     = $request->input('yape') ?? 0;
        $plin     = $request->input('plin') ?? 0;
        $tarjeta  = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $totalAcum = $efectivo + $yape + $plin + $tarjeta + $deposito;

        $data = [

            'sequentialNumber'  => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'paymentDate'       => $request->input('paymentDate') ?? Carbon::now(),
            'total'             => $totalAcum ?? 0,
            'yape'              => $request->input('yape') ?? 0,
            'deposit'           => $request->input('deposit') ?? 0,
            'cash'              => $efectivo ?? 0,
            'card'              => $request->input('card') ?? 0,
            'plin'              => $request->input('plin') ?? 0,

            'comment'           => $request->input('comment') ?? '-',
            'typeDocument'      => 'Ingreso',

            'typePayment'       => $request->input('typePayment') ?? null,
            'typeSale'          => $request->input('typeSale') ?? '-',
            'status'            => 'Generada',
            'programming_id'    => $programming->id,
            'paymentConcept_id' => 11, //EGRESO MONTO DE VIAJE
            'branchOffice_id'   => $programming->branchOffice_id,
            'reception_id'      => $request->input('reception_id'),
            'bank_id'           => $request->input('bank_id'),
            'person_id'         => $personWorker->id,
            'user_id'           => auth()->id(),
            'box_id'            => $user->box->id,
            'driverExpense_id'  => $objectExpense->id,
        ];

        $object = Moviment::create($data);

        $programming->updateTotalDriversExpenses();

        $totalIngreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Ingreso');
            // $q->where('selectTypePay', 'Efectivo')
            //     ->orWhere('selectTypePay', 'Descuento_sueldo')
            //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
            ;
        })->sum('total');

        $totalEgreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Egreso');
            // $q->where('selectTypePay', 'Efectivo')
            //     ->orWhere('selectTypePay', 'Descuento_sueldo')
            //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
            ;
        })->sum('total');

        // Calcular el saldo (diferencia entre ingresos y egresos)
        $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');

        $object =
        DriverExpense::with(['programming', 'moviment', 'expensesConcept', 'worker.person', 'bank'])->find($objectExpense->id);

        return response()->json(['data' => $object,
            'total_ingreso'                 => number_format($totalIngreso, 2, '.', ''),
            'total_egreso'                  => number_format($totalEgreso, 2, '.', ''),
            'saldo'                         => number_format((float) $saldo, 2, '.', ''),
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/transporte/public/api/driverExpense/{id}",
     *     summary="Update an existing driverExpense",
     *     tags={"DriverExpense"},
     *     description="Update the details of an existing driverExpense",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the driverExpense to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="DriverExpense data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="programming_id", type="integer", example=1, description="ID of the programming"),
     *             @OA\Property(property="expensesConcept_id", type="integer", example=1, description="ID of the expenses concept"),
     *             @OA\Property(property="worker_id", type="integer", example=1, description="ID of driver"),
     *             @OA\Property(property="amount", type="number", format="float", example=50.00, description="Amount of the expense"),
     *             @OA\Property(property="quantity", type="integer", example=2, description="Quantity of items"),
     *             @OA\Property(property="place", type="string", example="Station X", description="Place where the expense occurred"),
     *             @OA\Property(property="km", type="integer", example=100, description="Kilometers at the time of expense"),
     *             @OA\Property(property="image", type="string", example="file", description="file"),
     *             @OA\Property(property="comment", type="string", example="Refueling in station X", description="Additional comments"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DriverExpense updated successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/DriverExpense"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Some fields are required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function update(Request $request, $id)
    {

        //en duda el actualizar porque genera mov caja
        $validator = validator()->make($request->all(), [
            'programming_id'     => 'required|exists:programmings,id',
            'expensesConcept_id' => 'required|exists:expenses_concepts,id',
            'worker_id'          => 'required|exists:workers,id',
            'proveedor_id'       => 'nullable|exists:people,id',
            'transaction_concept_id' => 'required|exists:transaction_concepts,id,deleted_at,NULL',
            'amount'             => 'required',
            // 'quantity' => 'required',
            'bank_id'            => 'nullable|exists:banks,id',

            'yape'               => 'nullable|numeric',
            'deposit'            => 'nullable|numeric',
            'cash'               => 'nullable|numeric',
            'plin'               => 'nullable|numeric',
            'card'               => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $expense = DriverExpense::find($id);
        if (! $expense) {
            return response()->json(['error' => 'DriverExpense not found'], 404);
        }

        $total  = ($request->input('amount') ?? 0) * ($request->input('quantity') ?? 0);
        $worker = Worker::find($request->input('worker_id'));

        $personWorker = Worker::find($request->input('worker_id'))->person;
        if (! $personWorker) {
            return response()->json(['error' => 'No esta registrado en Personas el conductor'], 422);
        }

        $user = User::find(Auth()->id());
        // if ($user->typeofUser_id != 4) { //DEBE SER CAJERO
        //     return response()->json(['error' => 'El usuario debe ser del tipo Cajero'], 422);
        // }
        if (! $user->box) {
            return response()->json(['error' => 'El usuario no tiene una caja'], 422);
        }

        $box = Box::find($user->box->id);
        if ($box->status != 'Activa') {
            return response()->json(['error' => 'Caja No está Aperturada'], 422);
        }

        $data = [
            'programming_id'     => $request->input('programming_id'),
            'expensesConcept_id' => $request->input('expensesConcept_id'),
            'worker_id'          => $worker->id,
            'total'              => $total,
            'amount'             => $request->input('amount'),
            'quantity'           => $request->input('quantity') ?? 1,
            'place'              => $request->input('place'),
            'km'                 => $request->input('km'),
            'gallons'            => $request->input('gallons'),
            'comment'            => $request->input('comment'),
            'operationNumber'    => $request->input('operationNumber'),
            'bank_id'            => $request->input('bank_id'),
            'date_expense'       => $request->input('date_expense'),

            'igv'                => $request->input('igv'),
            'gravado'            => $request->input('gravado'),
            'exonerado'          => $request->input('exonerado'),
            'selectTypePay'      => $request->input('selectTypePay') ?? $expense->selectTypePay,

        ];

        $expense->update($data);
        $programming = Programming::find($request->input('programming_id'));

        $image = $request->file('image');
        if ($image) {
            Log::info('Imagen recibida: ' . $image->getClientOriginalName());
            $file        = $image;
            $currentTime = now();
            $filename    = $currentTime->format('YmdHis') . '_' . $file->getClientOriginalName();
            $path        = $file->storeAs('public/photosDrivers', $filename);
            Log::info('Imagen almacenada en: ' . $path);
            $rutaImagen         = Storage::url($path);
            $expense->routeFact = $rutaImagen;
            $expense->save();
            Log::info('Imagen guardada en la base de datos con ruta: ' . $rutaImagen);
        }

        // Verifica si el driverExpense ya tiene un moviment asociado, incluyendo los eliminados con soft delete
        $movimentDriver = Moviment::where('driverExpense_id', $expense->id)->first();

        $efectivo = $request->input('cash') ?? 0;
        $yape     = $request->input('yape') ?? 0;
        $plin     = $request->input('plin') ?? 0;
        $tarjeta  = $request->input('card') ?? 0;
        $deposito = $request->input('deposit') ?? 0;

        $total = $efectivo + $yape + $plin + $tarjeta + $deposito;

        if ($movimentDriver) {
            $movimentDriver->total        = $total;
            $movimentDriver->cash         = $efectivo;
            $movimentDriver->yape         = $yape;
            $movimentDriver->deposit      = $deposito;
            $movimentDriver->card         = $tarjeta;
            $movimentDriver->plin         = $plin;
            $movimentDriver->date_expense = $request->input('date_expense');
            $movimentDriver->save();

        } else {

        }

        $bank_account  = BankAccount::find($request->input(key: 'bank_account_id'));
        if ($bank_account != null) {
            $data_movement_bank = [
                'driver_expense_id'      => $expense->id,
                'bank_id'                => $request->input('bank_id'),
                'bank_account_id'        => $bank_account->id,
                'currency'               => $bank_account->currency,
                'date_moviment'          => $request->input('date_expense'),
                'total_moviment'         => $total,
                'comment'                => $request->input('comment'),
                'user_created_id'        => $user->id,
                'transaction_concept_id' => $request->input('transaction_concept_id'),
                'person_id'              => $expense->worker->person->id,
                'type_moviment'          => 'SALIDA',
            ];
            $this->bankmovementService->updateBankMovement($expense, $data_movement_bank);
        }

        $programming->updateTotalDriversExpenses();
        $programming->save();

        $totalIngreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Ingreso');
            // $q->where('selectTypePay', 'Efectivo')
            //     ->orWhere('selectTypePay', 'Descuento_sueldo')
            //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
            // ;
        })->sum('total');

        $totalEgreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
            $q->where('typeConcept', 'Egreso');
            // $q->where('selectTypePay', 'Efectivo')
            //     ->orWhere('selectTypePay', 'Descuento_sueldo')
            //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
            // ;
        })->sum('total');

        // Calcular el saldo (diferencia entre ingresos y egresos)
        $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');

        $object =
        DriverExpense::with(['programming', 'expensesConcept', 'worker.person'])->find($expense->id);

        return response()->json(['data' => $object,
            'total_ingreso'                 => number_format($totalIngreso, 2, '.', ''),
            'total_egreso'                  => number_format($totalEgreso, 2, '.', ''),
            'saldo'                         => number_format((float) $saldo, 2, '.', ''),
        ], 200);
    }
    /**
     * @OA\Get(
     *     path="/transporte/public/api/driverExpense/{id}",
     *     summary="Get a driverExpense by ID",
     *     tags={"DriverExpense"},
     *     description="Retrieve a driverExpense by its ID",
     * security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the driverExpense to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="DriverExpense found",
     *        @OA\JsonContent(
     *              type="object",
     *   ref="#/components/schemas/DriverExpense"
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
        $object = DriverExpense::find($id);
        if (! $object) {
            return response()->json(['message' => 'Driver Expense not found'], 422);
        }

        return response()->json($object, 200);
    }

    /**
     * @OA\Delete(
     *     path="/transporte/public/api/driverExpense/{id}",
     *     summary="Delete a DriverExpense",
     *     tags={"DriverExpense"},
     *     description="Delete a DriverExpense by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the DriverExpense to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver Expense deleted successfully",
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
        // Buscar el gasto del conductor por ID
        $object = DriverExpense::find($id);

        // Si no existe el objeto, retornar error
        if (! $object) {
            return response()->json(['message' => 'Driver Expense not found'], 404); // Cambié el código de estado a 404 para 'no encontrado'
        }

        // Intentar eliminar el movimiento asociado, si existe
        if ($movimentDriver = $object->moviment) {
            // Se recomienda verificar si $movimentDriver es una instancia válida
            if ($movimentDriver instanceof Moviment) {
                $movimentDriver->delete();
            }
        }

        // Intentar eliminar el objeto y manejar posibles errores
        try {

            // Buscar la programación asociada
            $programming = Programming::find($object->programming_id);

            $object->delete();
            // Si existe la programación, actualizar los gastos totales de los conductores
            if ($programming) {
                $programming->updateTotalDriversExpenses();
            }
            $totalIngreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
                $q->where('typeConcept', 'Ingreso');
                // $q->where('selectTypePay', 'Efectivo')
                //     ->orWhere('selectTypePay', 'Descuento_sueldo')
                //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
                ;
            })->sum('total');

            $totalEgreso = DriverExpense::where('programming_id', $programming->id)->whereHas('expensesConcept', function ($q) {
                $q->where('typeConcept', 'Egreso');
                // $q->where('selectTypePay', 'Efectivo')
                //     ->orWhere('selectTypePay', 'Descuento_sueldo')
                //     ->orWhere('selectTypePay', 'Proxima_liquidacion')
                ;
            })->sum('total');

            // Calcular el saldo (diferencia entre ingresos y egresos)
            $saldo = number_format($totalIngreso - $totalEgreso, 2, '.', '');

            return response()->json([
                'total_ingreso' => number_format($totalIngreso, 2, '.', ''),
                'total_egreso'  => number_format($totalEgreso, 2, '.', ''),
                'saldo'         => number_format((float) $saldo, 2, '.', ''),
            ], 200);
        } catch (\Exception $e) {
            // Manejo de excepciones: retornar un mensaje de error
            return response()->json(['message' => 'Error al eliminar el gasto', 'error' => $e->getMessage()], 500);
        }
    }

}
