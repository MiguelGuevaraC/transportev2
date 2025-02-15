<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\BranchOffice;
use App\Models\DetailReception;
use App\Models\Reception;
use App\Models\Route;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceptionController extends Controller
{

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/reception",
     *     summary="Get all reception",
     *     tags={"Reception"},
     *     description="Show all reception",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="startPlace_id",
     *         in="query",
     *         description="ID of the start place",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endPlace_id",
     *         in="query",
     *         description="ID of the end place",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=2
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="branch_office_id",
     *         in="query",
     *         description="ID of the branch office",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=3
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reception"
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
    public function index(Request $request)
    {
        $startPlace       = $request->input('startPlace_id');
        $endPlace         = $request->input('endPlace_id');
        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice     = BranchOffice::find($branch_office_id);
        }

        $list = $this->filterReceptions($request, $branch_office_id);

        return response()->json($list, 200);

    }

    public function filterReceptions(Request $request, $branch_office_id)
    {
        $startPlace = $request->input('startPlace_id');
        $endPlace   = $request->input('endPlace_id');

        // Buscar la ruta principal
        $route = Route::where('placeStart_id', $startPlace)
            ->where('placeEnd_id', $endPlace)
            ->first();

        $subRoutes = $route ? $route->routes : [];
        $allRoutes = collect([$route])->merge($subRoutes)->filter(); // Incluye la ruta padre y las subrutas

        $receptions = Reception::with('user', 'origin', 'office', 'sender', 'destination',
            'recipient', 'pickupResponsible', 'payResponsible', 'moviment', 'cargos', 'branchOffice',
            'seller', 'pointDestination', 'pointSender', 'details', 'firstCarrierGuide')
            ->where(function ($query) use ($subRoutes) {
                foreach ($subRoutes as $subRoute) {
                    $query->orWhere(function ($q) use ($subRoute) {
                        $q->whereHas('origin', function ($q) use ($subRoute) {
                            $q->where('name', $subRoute->placeStart);
                        })
                            ->whereHas('destination', function ($q) use ($subRoute) {
                                $q->where('name', $subRoute->placeEnd);
                            });
                    });
                }
            })
            ->where('branchOffice_id', $branch_office_id)
            ->orderBy('id', 'desc')
            ->paginate();

        return ($receptions);
    }

    /**
     * @OA\Post(
     *     path="/transportev2/public/api/reception",
     *     summary="Create a new reception",
     *     tags={"Reception"},
     *     description="Create a new reception",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reception data",
     *         @OA\JsonContent(
     *             @OA\Property(property="typeService", type="string", example="Type of service", description="Type of service"),
     *             @OA\Property(property="typeDelivery", type="string", example="Type of delivery", description="Type of delivery"),
     *             @OA\Property(property="conditionPay", type="string", example="Payment condition", description="Payment condition"),
     *             @OA\Property(property="numberDays", type="integer", example="3", description="Days"),
     *             @OA\Property(property="creditAmount", type="decimal", example="100", description="Amount Credit"),
     *
     *             @OA\Property(property="comment", type="string", example="Documents Anexos", description="Comment"),
     * @OA\Property(property="tokenResponsible", type="string", example="token123", description="Token"),
     * @OA\Property(property="address", type="string", example="address1", description="Address"),

     *
     *             @OA\Property(property="paymentAmount", type="string", example=100, description="Payment amount"),
     *             @OA\Property(property="receptionDate", type="string", format="date", example="2024-05-08", description="Reception date"),
     *             @OA\Property(property="transferLimitDate", type="string", format="date", example="2024-05-10", description="Transfer limit date"),
     *             @OA\Property(property="origin_id", type="integer", example="1", description="Origin ID"),
     *             @OA\Property(property="destination_id", type="integer", example="2", description="Destination ID"),
     *             @OA\Property(property="sender_id", type="integer", example="1", description="Sender ID"),
     *             @OA\Property(property="recipient_id", type="integer", example="2", description="Recipient ID"),
     *             @OA\Property(property="pickupResponsible_id", type="integer", example="1", description="Pickup Responsible ID"),
     *             @OA\Property(property="payResponsible_id", type="integer", example="1", description="Pay Responsible ID"),
     *             @OA\Property(property="type_responsiblePay", type="string", example="Remitente", description="Who pay reception"),
     *              @OA\Property(property="seller_id", type="integer", example="1", description="Seller ID"),
     *             @OA\Property(property="pointDestination_id", type="integer", example="1", description="Point Destination ID"),
     *         @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     *         @OA\Property(property="office_id", type="integer", example=1, description="ID Branch Office"),
     * @OA\Property(property="pointSender_id", type="integer", example="2", description="Point Sender ID"),
     *               @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="description", type="string", example="Descripción"),
     *                     @OA\Property(property="weight", type="number", format="float", example=0.00),
     *                     @OA\Property(property="paymentAmount", type="number", format="float", example=0.00),
     *                     @OA\Property(property="comissionAmount", type="number", format="float", example=0.00),
     *                      @OA\Property(property="comissionAgent_id", type="integer", example=null),
     *                  ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reception created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Reception created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error: The given data was invalid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {

        $validator = validator()->make($request->all(), [

            'typeService'         => 'required',
            'typeDelivery'        => 'required',
            'conditionPay'        => 'required',
            // 'comment' => 'required',

            'paymentAmount'       => 'required',

            'receptionDate'       => 'required',
            'transferLimitDate'   => 'required',

            'origin_id'           => 'required|exists:places,id',
            'destination_id'      => 'required|exists:places,id',

            'sender_id'           => 'required|exists:people,id',
            'recipient_id'        => 'required|exists:people,id',

            // 'pickupResponsible_id' => 'nullable|exists:contact_infos,id',
            'payResponsible_id'   => 'required|exists:people,id',

            'seller_id'           => 'required|exists:workers,id',

            'pointDestination_id' => 'required|exists:addresses,id',
            'pointSender_id'      => 'required|exists:addresses,id',

            'branch_office_id'    => 'nullable|exists:branch_offices,id',
            'office_id'           => 'nullable|exists:branch_offices,id',

            'details'             => 'nullable|array',

            // 'isload' => 'required|in:0,1',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice     = BranchOffice::find($branch_office_id);
        }

        $branchOffice = BranchOffice::find($branch_office_id);

        if ($branchOffice) {
            $tipo        = 'R' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
            $tipoDetalle = 'D' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
        } else {
            return response()->json(['error' => 'Branch Office not found'], 422);
        }

        $resultado    = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(codeReception, LOCATE("-", codeReception) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM receptions WHERE SUBSTRING(codeReception, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $data = [
            'codeReception'        => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'typeService'          => $request->input('typeService') ?? null,
            'type_responsiblePay'  => $request->input('type_responsiblePay') ?? null,
            'typeDelivery'         => $request->input('typeDelivery') ?? null,
            'conditionPay'         => $request->input('conditionPay') ?? null,
            'numberDays'           => $request->input('numberDays') ?? null,
            'creditAmount'         => $request->input('creditAmount') ?? null,
            'address'              => $request->input('address') ?? null,
            'tokenResponsible'     => $request->input('tokenResponsible') ?? null,

            'comment'              => $request->input('comment') ?? null,
            'paymentAmount'        => $request->input('paymentAmount') ?? null,
            'debtAmount'           => $request->input('paymentAmount') ?? null,
            'bultosTicket'         => $request->input('bultosTicket') ?? null,

            'receptionDate'        => $request->input('receptionDate') ?? null,
            'transferLimitDate'    => $request->input('transferLimitDate') ?? null,
            'origin_id'            => $request->input('origin_id') ?? null,
            'destination_id'       => $request->input('destination_id') ?? null,
            'sender_id'            => $request->input('sender_id') ?? null,
            'user_id'              => auth()->user()->id ?? '1',
            'recipient_id'         => $request->input('recipient_id') ?? null,
            'pickupResponsible_id' => $request->input('pickupResponsible_id') ?? null,
            'payResponsible_id'    => $request->input('payResponsible_id') ?? null,
            'seller_id'            => $request->input('seller_id') ?? null,
            'pointDestination_id'  => $request->input('pointDestination_id') ?? null,
            'pointSender_id'       => $request->input('pointSender_id') ?? null,
            'branchOffice_id'      => $branch_office_id,
            'office_id'            => $request->input('office_id'),

        ];

        $object = Reception::create($data);

        if ($request->input('conditionPay') == "Crédito") {
            $object->creditAmount  = $object->paymentAmount;
            $object->paymentAmount = 0;
            $object->save();
        }

        if ($object) {
            //ASIGNAR DETALLES
            $details    = $request->input('details');
            $idDetalles = [];

            foreach ($details as $detail) {

                $tipo = $tipoDetalle;

                $resultado    = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM detail_receptions WHERE SUBSTRING(numero, 1, 4) = ?', [$tipo])[0]->siguienteNum;
                $siguienteNum = (int) $resultado;

                $objectData = [
                    'numero'            => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                    'description'       => $detail['description'] ?? '-',
                    'weight'            => $detail['weight'] ?? 0.00,
                    'cant'              => $detail['cant'] ?? 1,
                    'unit'              => $detail['unit'] ?? 'NIU',

                    'paymentAmount'     => $detail['paymentAmount'] ?? 0.00,
                    'debtAmount'        => $detail['debtAmount'] ?? 0.00,
                    'costFreight'       => $detail['costFreight'] ?? 0.00,
                    'comissionAmount'   => $detail['comissionAmount'] ?? 0.00,
                    'costLoad'          => $detail['costLoad'] ?? 0.00,
                    'costDownload'      => $detail['costDownload'] ?? 0.00,
                    'comment'           => $detail['comment'] ?? '-',
                    'status'            => 'Pendiente',
                    'comissionAgent_id' => $detail['comissionAgent_id'] ?? null,
                    'reception_id'      => $object->id,
                    'product_id'        => isset($detail['product_id']) ? $detail['product_id'] : null,

                ];
                $idDetalles[] = DetailReception::create($objectData)->id;

            }

        }
        $object->storeWeight();

        $object = Reception::with('user', 'origin', 'office', 'sender', 'destination',
            'recipient', 'pickupResponsible', 'payResponsible', 'moviment', 'cargos', 'branchOffice',
            'seller', 'pointDestination', 'pointSender', 'details', 'firstCarrierGuide')->find($object->id);

        Bitacora::create([
            'user_id'     => Auth::id(),   // ID del usuario que realiza la acción
            'record_id'   => $object->id,  // El ID del usuario afectado
            'action'      => 'POST',       // Acción realizada
            'table_name'  => 'receptions', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Guardar Recepción',  // Descripción de la acción
            'ip_address'  => $request->ip(),        // Dirección IP del usuario
            'user_agent'  => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        return response()->json($object, 200);
    }

    /**
     * @OA\Get(
     *     path="/transportev2/public/api/reception/{id}",
     *     summary="Get a reception by ID",
     *     tags={"Reception"},
     *     description="Retrieve a reception by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reception to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *@OA\Response(
     * response=200,
     * description="Reception encontrado"

     *),
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
        // Cargar la recepción junto con sus relaciones
        $object = Reception::with([
            'user', 'origin', 'sender', 'destination', 'office',
            'recipient', 'pickupResponsible', 'payResponsible',
            'seller', 'pointDestination', 'pointSender', 'branchOffice',
            'details', 'firstCarrierGuide', 'moviment', 'cargos',
        ])->find($id);

        // Verificar si la recepción existe
        if (! $object) {
            return response()->json(['message' => 'Reception not found'], 422);
        }

        try {
            // Verifica que la relación 'firstCarrierGuide' exista
            if (! isset($object->firstCarrierGuide)) {
                $object->status = 'Sin Guia';
                // Verifica que 'Programming' exista en 'firstCarrierGuide'
            } elseif (isset($object->firstCarrierGuide) && ! isset($object->firstCarrierGuide->Programming)) {
                $object->status = 'Sin Programar';
            } else {
                $object->status = 'Programado';
            }
        } catch (\Exception $e) {
            // Registrar cualquier error que ocurra al procesar el estado
            Log::error('Error al procesar el estado de la recepción al crear una guía: ' . $e->getMessage());
        }

        // Retornar la recepción con el estado actualizado
        return response()->json($object, 200);
    }

    /**
     * @OA\Put(
     *     path="/transportev2/public/api/reception/{id}",
     *     summary="Update an existing reception",
     *     tags={"Reception"},
     *     description="Update an existing reception",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reception to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reception data",
     *         @OA\JsonContent(
     *             @OA\Property(property="typeService", type="string", example="Type of service", description="Type of service"),
     *             @OA\Property(property="typeDelivery", type="string", example="Type of delivery", description="Type of delivery"),
     *             @OA\Property(property="conditionPay", type="string", example="Payment condition", description="Payment condition"),
     *             @OA\Property(property="numberDays", type="integer", example="3", description="Days"),
     *             @OA\Property(property="creditAmount", type="decimal", example="100", description="Amount Credit"),
     *
     *             @OA\Property(property="comment", type="string", example="Documents Anexos", description="Comment"),
     *             @OA\Property(property="paymentAmount", type="string", example=100, description="Payment amount"),
     *             @OA\Property(property="receptionDate", type="string", format="date", example="2024-05-08", description="Reception date"),
     *             @OA\Property(property="transferLimitDate", type="string", format="date", example="2024-05-10", description="Transfer limit date"),
     *             @OA\Property(property="origin_id", type="integer", example="1", description="Origin ID"),
     *             @OA\Property(property="destination_id", type="integer", example="2", description="Destination ID"),
     *             @OA\Property(property="sender_id", type="integer", example="1", description="Sender ID"),
     *             @OA\Property(property="recipient_id", type="integer", example="2", description="Recipient ID"),
     *             @OA\Property(property="pickupResponsible_id", type="integer", example="1", description="Pickup Responsible ID"),
     * @OA\Property(property="tokenResponsible", type="string", example="token123", description="Token"),
     * @OA\Property(property="address", type="string", example="address1", description="Address"),

     *
     * @OA\Property(property="payResponsible_id", type="integer", example="1", description="Pay Responsible ID"),
     *             @OA\Property(property="type_responsiblePay", type="string", example="Remitente", description="Who pay reception"),
     *             @OA\Property(property="branch_office_id", type="integer", example=1, description="ID Branch Office"),
     *         @OA\Property(property="office_id", type="integer", example=1, description="ID Branch Office"),
     * @OA\Property(property="seller_id", type="integer", example="1", description="Seller ID"),
     *             @OA\Property(property="pointDestination_id", type="integer", example="1", description="Point Destination ID"),
     *             @OA\Property(property="pointSender_id", type="integer", example="2", description="Point Sender ID"),
     *               @OA\Property(
     *                 property="details",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                      @OA\Property(property="idDetail", type="string", example="1"),
     *                     @OA\Property(property="description", type="string", example="Descripción"),
     *                     @OA\Property(property="weight", type="number", format="float", example=0.00),
     *                     @OA\Property(property="paymentAmount", type="number", format="float", example=0.00),
     *                     @OA\Property(property="comissionAmount", type="number", format="float", example=0.00),
     *                      @OA\Property(property="comissionAgent_id", type="integer", example=null),
     *          ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reception updated successfully"
     *
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *       @OA\Response(
     *         response=401,
     *         description="Unauthenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */

    public function update(Request $request, $id)
    {
        $object = Reception::find($id);
        if (! $object) {
            return response()->json(['message' => 'Reception not found'], 422);
        }

        // Validar los datos de entrada
        $validator = validator()->make($request->all(), [

            'origin_id'            => 'nullable|exists:places,id',
            'destination_id'       => 'nullable|exists:places,id',

            'sender_id'            => 'nullable|exists:people,id',
            'recipient_id'         => 'nullable|exists:people,id',

            'pickupResponsible_id' => 'nullable|exists:contact_infos,id',
            'payResponsible_id'    => 'nullable|exists:people,id',

            'seller_id'            => 'nullable|exists:workers,id',

            'pointDestination_id'  => 'nullable|exists:addresses,id',
            'pointSender_id'       => 'nullable|exists:addresses,id',
            'details'              => 'nullable|array',
            'branch_office_id'     => 'nullable|exists:branch_offices,id',

            'office_id'            => 'nullable|exists:branch_offices,id',
            // 'isload' => 'required|in:0,1',
            'paymentAmount'        => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $filterNullValues = function ($value) {
            return $value !== null || $value === false;
        };
        $branch_office_id = $request->input('branch_office_id');
        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        } else {
            $branch_office_id = auth()->user()->worker->branchOffice_id;
            $branchOffice     = BranchOffice::find($branch_office_id);
        }

        $Data = [
            // 'codeReception' => $request->input('codeReception'),
            'typeService'          => $request->input('typeService'),
            'type_responsiblePay'  => $request->input('type_responsiblePay'),
            'typeDelivery'         => $request->input('typeDelivery'),
            'conditionPay'         => $request->input('conditionPay'),

            'numberDays'           => $request->input('numberDays'),
            'creditAmount'         => $request->input('creditAmount'),

            'address'              => $request->input('address'),
            'tokenResponsible'     => $request->input('tokenResponsible'),

            'comment'              => $request->input('comment'),
            'paymentAmount'        => $request->input('paymentAmount'),

            'receptionDate'        => $request->input('receptionDate'),
            'transferLimitDate'    => $request->input('transferLimitDate'),
            'origin_id'            => $request->input('origin_id'),
            'destination_id'       => $request->input('destination_id'),
            'bultosTicket'         => $request->input('bultosTicket'),

            'sender_id'            => $request->input('sender_id'),
            // 'user_id' => auth()->user()->id ?? '1',
            'recipient_id'         => $request->input('recipient_id'),
            'pickupResponsible_id' => $request->input('pickupResponsible_id'),
            'payResponsible_id'    => $request->input('payResponsible_id'),
            'seller_id'            => $request->input('seller_id'),

            'pointDestination_id'  => $request->input('pointDestination_id'),
            'pointSender_id'       => $request->input('pointSender_id'),
            // 'branchOffice_id' => $request->input('branch_office_id'),
            'office_id'            => $request->input('office_id'),
            'user_edited_id'       => Auth::user()->id,

        ];

        if ($object->moviment) {
            return response()->json(['message' => 'La recepción tiene asginado un movimiento de Venta'], 409);
        } else {
            $Data['debtAmount'] = $request->input('paymentAmount');

        }

        $object->update($Data);

        if ($object) {
            $currentDetailIds = $object->details()->pluck('id')->toArray();
            $detailsUpdate    = $request->input('details');

            $newDetailIds = [];
            $user         = Auth()->user();
            $worker       = Worker::find($user->worker_id);

            if ($worker) {
                $branchOffice = BranchOffice::find($worker->branchOffice_id);

                if ($branchOffice) {
                    $tipo = 'D' . str_pad($branchOffice->id, 3, '0', STR_PAD_LEFT);
                } else {
                    return response()->json(['error' => 'Branch Office not found'], 422);
                }
            } else {
                return response()->json(['error' => 'Worker not found'], 422);
            }

            foreach ($detailsUpdate as $detailData) {
                $idDetail = isset($detailData['idDetail']) == false ? 'null' : $detailData['idDetail'];

                if (($idDetail != 'null')) {

                    $newDetailIds[] = $idDetail;

                    $detail = DetailReception::find($idDetail);

                    if ($detail) {

                        $data = [
                            'description'   => $detailData['description'] ?? '-',
                            'weight'        => $detailData['weight'] ?? 0.00,
                            'paymentAmount' => $detailData['paymentAmount'] ?? 0.00,
                            'reception_id'  => $id ?? null,
                            'cant'          => $detailData['cant'] ?? 1,
                            'unit'          => $detailData['unit'] ?? 'NIU',
                            'product_id'        => isset($detailData['product_id']) ? $detailData['product_id'] : null,
                        ];
                        $detail->update($data);
                    }
                } else {

                    $tipo         = str_pad($tipo, 4, '0', STR_PAD_RIGHT);
                    $resultado    = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(numero, LOCATE("-", numero) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM detail_receptions WHERE SUBSTRING(numero, 1, 4) = ?', [$tipo])[0]->siguienteNum;
                    $siguienteNum = (int) $resultado;

                    $data = [
                        'numero'            => $detailData['numero'] ?? $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                        'description'       => $detailData['description'] ?? '-',
                        'weight'            => $detailData['weight'] ?? 0.00,
                        'paymentAmount'     => $detailData['paymentAmount'] ?? 0.00,
                        'debtAmount'        => $detailData['debtAmount'] ?? 0.00,
                        'costFreight'       => $detailData['costFreight'] ?? 0.00,
                        'comissionAmount'   => $detailData['comissionAmount'] ?? 0.00,
                        'costLoad'          => $detailData['costLoad'] ?? 0.00,
                        'costDownload'      => $detailData['costDownload'] ?? 0.00,
                        'comment'           => $detailData['comment'] ?? '-',
                        'status'            => $detailData['status'] ?? 'Generada',
                        'comissionAgent_id' => $detailData['comissionAgent_id'] ?? null,
                        'reception_id'      => $object->id ?? null,
                        'product_id'        => isset($detailData['product_id']) ? $detailData['product_id'] : null,
                    ];

                    $newDetail      = $object->details()->create($data);
                    $newDetailIds[] = $newDetail->id;
                }
            }

            $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
            $object->details()->whereIn('id', $detailsToDelete)->delete();

        }
        $object->storeWeight();
        $object = Reception::with('user', 'origin', 'office', 'sender', 'destination',
            'recipient', 'pickupResponsible', 'payResponsible', 'moviment', 'cargos', 'branchOffice',
            'seller', 'pointDestination', 'pointSender', 'details', 'firstCarrierGuide')->find($id);

        try {
            // Verifica que la relación 'firstCarrierGuide' exista
            if (! isset($object->firstCarrierGuide)) {
                $object->status = 'Sin Guia';
                // Verifica que 'Programming' exista en 'firstCarrierGuide'
            } elseif (isset($object->firstCarrierGuide) && ! isset($object->firstCarrierGuide->Programming)) {
                $object->status = 'Sin Programar';
            } else {
                $object->status = 'Programado';
            }
        } catch (\Exception $e) {
            // Registrar cualquier error que ocurra al procesar el estado
            Log::error('Error al procesar el estado de la recepción al crear una guía: ' . $e->getMessage());
        }

        Bitacora::create([
            'user_id'     => Auth::id(),   // ID del usuario que realiza la acción
            'record_id'   => $object->id,  // El ID del usuario afectado
            'action'      => 'PUT',        // Acción realizada
            'table_name'  => 'receptions', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Editar Recepción',   // Descripción de la acción
            'ip_address'  => $request->ip(),        // Dirección IP del usuario
            'user_agent'  => $request->userAgent(), // Información sobre el navegador/dispositivo
        ]);
        // Retornar la recepción con el estado actualizado
        return response()->json($object, 200);

    }

    /**
     * @OA\Delete(
     *     path="/transportev2/public/api/reception/{id}",
     *     summary="Delete a reception",
     *     tags={"Reception"},
     *     description="Delete a reception by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the reception to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reception deleted successfully",
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
        $object = Reception::find($id);

        if (! $object) {
            return response()->json(['message' => 'Reception not found'], 422);
        }

        // Verifica si la recepción tiene una relación con CarrierGuide o Moviment
        if ($object->firstCarrierGuide || $object->moviment) {
            return response()->json(['message' => 'No se puede eliminar la recepción porque tiene relaciones asociadas'], 422);
        }

        // Marca el usuario que eliminó y elimina la recepción
        $object->user_deleted_id = Auth::user()->id;
        $object->save();

        $object->delete();

        return response()->json(['message' => 'Recepción eliminada con éxito']);
    }

}
