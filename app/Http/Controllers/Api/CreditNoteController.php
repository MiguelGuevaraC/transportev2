<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\CreditNote;
use App\Models\Moviment;
use App\Models\PayInstallment;
use App\Models\Reception;
use App\Models\ReceptionBySale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditNoteController extends Controller
{
    /**
     * Get all Moviments
     * @OA\Get (
     *     path="/transporte/public/api/creditNote",
     *     tags={"CreditNote"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="branch_office_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID de la sucursal para filtrar los movimientos"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active Sale",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MovimentRequest")),
     *             @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/creditNote?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/creditNote?page=2"),
     *             @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/creditNote"),
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
        $person_id        = $request->input('person_id');
        $docRef           = $request->input('docRef');
        $from             = $request->input('from'); // Fecha inicial
        $to               = $request->input('to');   // Fecha final
        $number           = $request->input('number');

        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    "message" => "Branch Office Not Found",
                ], 404);
            }
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }

        $page    = request()->get('page', 1);
        $perPage = request()->get('per_page', 15);

        $movCaja = CreditNote::
            when(! empty($branch_office_id) && $branch_office_id !== "null", function ($query) use ($branch_office_id) {
            $query->where('branchOffice_id', $branch_office_id);
        })
            ->when(! empty($number) && $number !== "null", function ($query) use ($number) {
                $query->where('number', $number);
            })
            ->when(! empty($person_id) && $person_id !== "null", function ($query) use ($person_id) {
                return $query->whereHas('moviment.person', function ($query) use ($person_id) {
                    $query->where('id', $person_id);
                });
            })
            ->when(! empty($docRef) && $docRef !== "null", function ($query) use ($docRef) {
                return $query->whereHas('moviment', function ($query) use ($docRef) {
                    $query->where('sequentialNumber', $docRef);
                });
            })
            ->when(! empty($box_id) && $box_id !== "null", function ($query) use ($box_id) {
                return $query->whereHas('moviment', function ($query) use ($box_id) {
                    $query->where('box_id', $box_id);
                });
            })
        // Agregar el filtro por rango de fechas
            ->when(! empty($from), function ($query) use ($from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when(! empty($to), function ($query) use ($to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->with(['branchOffice', 'moviment', 'moviment.person', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'total'          => $movCaja->total(),
            'data'           => $movCaja->items(),
            'current_page'   => $movCaja->currentPage(),
            'last_page'      => $movCaja->lastPage(),
            'per_page'       => $movCaja->perPage(),
            'pagination'     => $perPage,
            'first_page_url' => $movCaja->url(1),
            'from'           => $movCaja->firstItem(),
            'next_page_url'  => $movCaja->nextPageUrl(),
            'path'           => $movCaja->path(),
            'prev_page_url'  => $movCaja->previousPageUrl(),
            'to'             => $movCaja->lastItem(),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/transporte/public/api/creditNote",
     *     summary="Store a new credit note",
     *     tags={"CreditNote"},
     *     description="Create a new credit note",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credit Note data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="total",
     *                 type="number",
     *                 format="float",
     *                 description="Total amount",
     *                 nullable=true,
     *                 example=100.00
     *             ),
     *             @OA\Property(
     *                 property="comment",
     *                 type="string",
     *                 description="Comment related to the credit note",
     *                 nullable=true,
     *                 example="Pago parcial"
     *             ),
     *             @OA\Property(
     *                 property="reason",
     *                 type="string",
     *                 description="Reason for the credit note",
     *                 example="Devolución de producto"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Reason for the credit note",
     *                 example="Devolución de producto"
     *             ),
     *             @OA\Property(
     *                 property="moviment_id",
     *                 type="integer",
     *                 description="ID of the related moviment",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="branchOffice_id",
     *                 type="integer",
     *                 description="ID of the branch office",
     *                 example=1
     *             ),



     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit Note created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNote")
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
        // Validación de los datos del request
        $validator = validator()->make($request->all(), [
            'total'           => 'nullable|numeric',
            'comment'         => 'nullable|string',
            'reason'          => 'required|string',
            'moviment_id'     => 'required|exists:moviments,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
        ]);

        // Retornar errores de validación
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
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }

        $branchOffice = BranchOffice::find($request->input('branchOffice_id'));
        $moviment     = Moviment::find($request->input('moviment_id'));

        // if ($moviment->installments && $moviment->installments->first()->payInstallments->isNotEmpty()) {
        //     return response()->json(['error' => 'Se encontró Amortizaciones en esta venta'], 422);
        // }
        if ($request->input('total') < 0) {
            return response()->json(['error' => 'El Monto No debe ser Negativo'], 422);
        }
        if ($request->input('total') > $moviment->total) {
            return response()->json(['error' => 'El Monto Supera el Monto de la Venta'], 422);
        }

        // Determinar el tipo basado en el sequentialNumber del movimiento
        $tipo = '';
        if (strpos($moviment->sequentialNumber, 'F') === 0) {
            $tipo = 'FC';
        } elseif (strpos($moviment->sequentialNumber, 'B') === 0) {
            $tipo = 'BC';
        }

        // Formatear el tipo con el ID de la sucursal
        $branchOfficeIdFormatted = str_pad($box->serie, 2, '0', STR_PAD_LEFT);
        $tipo                    = $tipo . $branchOfficeIdFormatted;

        //
        if ($moviment->installments->first()) {
            $montoNota   = $request->input('total');
            $montoVenta  = $moviment->total;
            $installment = $moviment->installments->first();

            $amortizacionTotal = $installment->payInstallments()->sum('total');
            if (($montoVenta - $amortizacionTotal) < $montoNota) {
                return response()->json([
                    'message' => 'El monto de la nota no puede exceder la deuda restante.',
                ], 422);
            }

            // Generar el número de amortización
            $tipoc     = 'CC01';
            $resultado = DB::select(
                'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                 FROM pay_installments
                 WHERE SUBSTRING_INDEX(number, "-", 1) = ?',
                [$tipoc]
            );

            $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

            // Datos para la amortización
            $data = [
                'number'         => $tipoc . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentDate'    => Carbon::now()->toDateString(),
                'total'          => $request->input('total') ?? 0,
                'yape'           => 0,
                'deposit'        => 0,
                'cash'           => $request->input('total') ?? 0,
                'card'           => 0,
                'plin'           => 0,
                'nroOperacion'   => '',
                'comment'        => '',
                'type'           => 'Nota Credito',
                'installment_id' => $installment->id,
                'bank_id'        => null,
            ];

            $installmentPay = PayInstallment::create($data);

            // Actualizar el total de la deuda en el installment
            $installment->totalDebt = $installment->total - $installment->payInstallments()->sum('total');
            $installment->save();
            $today   = now()->toDateString();
            $dueDate = Carbon::parse($installment->date)->toDateString();

            if ($installment->totalDebt == 0) {
                $installment->status = 'Pagado';
            } elseif ($dueDate < $today) {
                $installment->status = 'Vencido';
            } else {
                $installment->status = 'Pendiente';
            }
            $installment->save();
        }

        // Generar el siguiente número para la nota de crédito
        $resultado2 = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(number, LOCATE("-", number) + 1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                     FROM credit_notes
                     WHERE SUBSTRING(number, 1, ?) = ?',
            [strlen($tipo), $tipo]
        );

        $siguienteNum2 = (int) $resultado2[0]->siguienteNum;

        // Preparar los datos para crear la nota de crédito
        $data = [
            'number'          => $tipo . '-' . str_pad($siguienteNum2, 8, '0', STR_PAD_LEFT),
            'totalReferido'   => $moviment->total ?? 0,
            'percentaje'      => $request->input('percentaje') ?? 0,
            'total'           => $request->input('total') ?? 0,
            'reason'          => $request->input('reason') ?? '-',
            'comment'         => $request->input('comment') ?? '-',
            'description'     => $request->input('description') ?? '-',
            'totalAjuste'     => $request->input('totalAjuste') ?? 0,
            'fechaAjuste'     => $request->input('fechaAjuste') ?? null,
            'branchOffice_id' => $request->input('branchOffice_id'),
            'moviment_id'     => $request->input('moviment_id'),
        ];

        // Crear la nota de crédito
        $object           = CreditNote::create($data);
        $moviment->status = "Anulada";
        $moviment->save();

        // Obtener la lista de productos y descripciones
        $descriptionString = '';
        $receptionDetails  = $object->moviment?->reception?->details() ?? [];
        if ($receptionDetails != []) {
            $descriptions      = $receptionDetails->pluck('description')->toArray();
            $descriptionString = implode(', ', $descriptions);
        }

        // Actualizar la nota de crédito con la lista de productos
        $object->productList = $descriptionString;
        $object->save();

        if ($moviment->typePayment == 'Contado') {
            if ($object->total == $moviment->total) {
                $movCaja = Moviment::where('mov_id', $moviment->id)->first();
                if ($movCaja) {
                    $movCaja->status = "Anulada por Nota";
                    $movCaja->save();
                    $movCaja->delete();
                }
            }
        } else {
            $installmentPay->comment = 'NC-' . $object->number;
            $installmentPay->save();
        }

        $receptions = $moviment->receptions;

        if ($object->total == $moviment->total && $request->input('reason')=="1") {
            foreach ($receptions as $reception) {
                $rececption = Reception::find($reception->id);
                $rececption->moviment_id = null;
                $rececption->save();

                $receptionBySale = ReceptionBySale::where('reception_id', $reception->id)
                    ->where('moviment_id', $moviment->id)
                    ->first();
                if ($receptionBySale) {
                    $receptionBySale->status = 'Anulada';
                    $receptionBySale->save();
                }
            }
        }

        //AQUI VALIDAR EN CASO EXISTAN COMPROMISOS

        // Recuperar la nota de crédito actualizada
        $object = CreditNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->find($object->id);

        // $this->declararNotaCredito($object->id, 1);

        Bitacora::create([
            'user_id'     => Auth::id(),     // ID del usuario que realiza la acción
            'record_id'   => $object->id,    // El ID del usuario afectado
            'action'      => 'POST',         // Acción realizada
            'table_name'  => 'credit_notes', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Guardar Nota Crédito', // Descripción de la acción
            'ip_address'  => $request->ip(),          // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),   // Información sobre el navegador/dispositivo
        ]);

        return response()->json($object, 200);
    }

    public function update(Request $request, $id)
    {
        // Validación de los datos del request
        $validator = validator()->make($request->all(), [
            'total'           => 'nullable|numeric',
            'comment'         => 'nullable|string',
            'reason'          => 'required|string',
            'moviment_id'     => 'required|exists:moviments,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
        ]);

        // Retornar errores de validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Verificar si el usuario tiene una caja asignada
        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario sin caja asignada'], 422);
        }

        // Obtener la nota de crédito a actualizar
        $object = CreditNote::find($id);
        if (! $object) {
            return response()->json(['error' => 'Credit Note Not Found'], 404);
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    "message" => "Box Not Found",
                ], 404);
            }
        }
        // else {
        //     $box_id = auth()->user()->box_id;
        //     $box = Box::find($box_id);
        // }

        // Buscar Sucursal y Movimiento
        $branchOffice = BranchOffice::find($request->input('branchOffice_id'));
        $idVenta      = $object->moviment_id;
        $moviment     = Moviment::find($request->input('moviment_id'));

        if ($moviment->installments && $moviment->installments->isNotEmpty()) {
            // Obtener la primera cuota
            $firstInstallment = $moviment->installments->first();

            // Verifica que la primera cuota exista
            if ($firstInstallment) {
                // Verifica si hay amortizaciones en la primera cuota
                if ($firstInstallment->payInstallments->isNotEmpty()) {
                    // Verifica si hay alguna amortización que no sea de tipo "Nota Credito"
                    $hasNonCreditNote = $firstInstallment->payInstallments->where('type', '!=', 'Nota Credito')->isNotEmpty();

                    if ($hasNonCreditNote) {
                        return response()->json(['error' => 'Se encontró amortizaciones que no son de tipo Nota Crédito en esta venta'], 422);
                    }
                }
            }
        }

        if ($request->input('total') < 0) {
            return response()->json(['error' => 'El Monto No debe ser Negativo'], 422);
        }
        if ($request->input('total') > $moviment->total) {
            return response()->json(['error' => 'El Monto Supera el Monto de la Venta'], 422);
        }
        if ($moviment->id != $idVenta) {
            return response()->json(['error' => 'Id Venta no puede ser diferente'], 422);
        }

        // Determinar el tipo basado en el sequentialNumber del movimiento
        $tipo = '';
        if (strpos($moviment->sequentialNumber, 'F') === 0) {
            $tipo = 'FC';
        } elseif (strpos($moviment->sequentialNumber, 'B') === 0) {
            $tipo = 'BC';
        }

        // Formatear el tipo con el ID de la sucursal
        $branchOfficeIdFormatted = str_pad($box_id, 2, '0', STR_PAD_LEFT);
        $tipo                    = $tipo . $branchOfficeIdFormatted;

        // Actualizar la nota de crédito con los nuevos datos
        // $object->number = $object->number ?? $tipo . '-' . str_pad($object->id, 8, '0', STR_PAD_LEFT);
        $object->totalReferido = $moviment->total ?? 0;
        $object->percentaje    = $request->input('percentaje') ?? 0;
        $object->total         = $request->input('total') ?? 0;
        $object->reason        = $request->input('reason') ?? '-';
        $object->comment       = $request->input('comment') ?? '-';
        $object->description   = $request->input('description') ?? '-';
        $object->totalAjuste   = $request->input('totalAjuste') ?? 0;
        $object->fechaAjuste   = $request->input('fechaAjuste') ?? null;

        $object->branchOffice_id = $request->input('branchOffice_id');
        // $object->moviment_id = $request->input('moviment_id');

        $object->save();

        // Cambiar el estado del movimiento a "Anulada"
        $moviment->status = "Anulada";
        $moviment->save();

        // Obtener la lista de productos y descripciones
        $descriptionString = '';
        $receptionDetails  = $object->moviment?->reception?->details() ?? [];
        if ($receptionDetails != []) {
            $descriptions      = $receptionDetails->pluck('description')->toArray();
            $descriptionString = implode(', ', $descriptions);
        }

        // Actualizar la nota de crédito con la lista de productos
        $object->productList = $descriptionString;
        $object->save();

        //--------------
        // Verifica si ya existe una amortización de tipo "Nota Credito"

                                         // Verifica que $installment no sea null
        if ($moviment->id == $idVenta) { // si es la misma venta
            $installment = $moviment->installments->first();

            if ($installment) { // Verificar que exista una cuota
                                    // Obtener la primera amortización de tipo "Nota Credito"
                $hasCreditNote = $installment->payInstallments->where('type', '=', 'Nota Credito')->first();

                if ($hasCreditNote) {
                    // Actualiza el registro existente en lugar de crear uno nuevo
                    $hasCreditNote->update([
                        'paymentDate' => Carbon::now()->toDateString(),
                        'total'       => $request->input('total') ?? 0,
                        'yape'        => 0,
                        'deposit'     => 0,
                        'cash'        => $request->input('total') ?? 0,
                        'card'        => 0,
                        'plin'        => 0,
                        // 'nroOperacion' => '',
                        // 'comment' => '',
                        'type'        => 'Nota Credito',
                        // 'bank_id' => null,
                    ]);
                } else {
                    // Opcional: manejar el caso en que no hay "Nota Credito"
                    // Puedes decidir qué hacer aquí, como registrar un mensaje o crear una nueva amortización
                }

                // Actualizar el total de la deuda en el installment
                $installment->totalDebt = $installment->total - $installment->payInstallments()->sum('total');
                $installment->save();
                $today   = now()->toDateString();
                $dueDate = Carbon::parse($installment->date)->toDateString();

                if ($installment->totalDebt == 0) {
                    $installment->status = 'Pagado';
                } elseif ($dueDate < $today) {
                    // Si no está pagado y la fecha de vencimiento ya pasó, marcar como "Vencido"
                    $installment->status = 'Vencido';
                } else {
                    // Si no está pagado y no ha vencido, marcar como "Pendiente"
                    $installment->status = 'Pendiente';
                }
                $installment->save();

            }
        } else {
            $movimentOld = Moviment::find($idVenta);

            // Verificar si se encontró el movimiento anterior y si tiene cuotas
            if ($movimentOld && $movimentOld->installments->isNotEmpty()) {
                $installmentOld = $movimentOld->installments->first();

                // Eliminar todas las amortizaciones de tipo "Nota Credito"
                $installmentOld->payInstallments()
                    ->where('type', '=', 'Nota Credito')
                    ->delete();
                $installmentOld->totalDebt = $installmentOld->total - $installmentOld->payInstallments()->sum('total');
                $installmentOld->save();
                // Convertir la fecha de vencimiento al formato 'YYYY-MM-DD' para comparar solo año, mes y día
                $today = now()->toDateString();

                $dueDate = Carbon::parse($installmentOld->date)->toDateString();

                // Verificar el estado de la cuota en función de la fecha y el total de la deuda
                if ($installmentOld->totalDebt == 0) {
                    $installmentOld->status = 'Pagado';
                } elseif ($dueDate < $today) {
                    // Si no está pagado y la fecha de vencimiento ya pasó, marcar como "Vencido"
                    $installmentOld->status = 'Vencido';
                } else {
                    // Si no está pagado y no ha vencido, marcar como "Pendiente"
                    $installmentOld->status = 'Pendiente';
                }

                // Guardar los cambios en la base de datos si es necesario
                $installmentOld->save();
            }

            $movimentNew    = Moviment::find($request->input('moviment_id'));
            $installmentNew = $movimentNew->installments->first();
            // Verificar si se encontró el movimiento nuevo y si tiene cuotas
            if ($installmentNew) {
                // Obtener la primera cuota

                // Generar el número de amortización
                $tipo      = 'CC01';
                $resultado = DB::select(
                    'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                     FROM pay_installments
                     WHERE SUBSTRING_INDEX(number, "-", 1) = ?',
                    [$tipo]
                );

                $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

                // Datos para la amortización
                $data = [
                    'number'         => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                    'paymentDate'    => Carbon::now()->toDateString(),
                    'total'          => $request->input('total') ?? 0,
                    'yape'           => 0,
                    'deposit'        => 0,
                    'cash'           => $request->input('total') ?? 0,
                    'card'           => 0,
                    'plin'           => 0,
                    'nroOperacion'   => '',
                    'comment'        => '',
                    'type'           => 'Nota Credito',
                    'installment_id' => $installmentNew->id,
                    'bank_id'        => null,
                ];

                // Crear la amortización para la primera cuota
                $installmentPay = PayInstallment::create($data);

                // Actualizar el total de la deuda en el installment
                $installmentNew->totalDebt = $installmentNew->total - $installmentNew->payInstallments()->sum('total');
                $installmentNew->save();

                $today   = now()->toDateString();
                $dueDate = Carbon::parse($installmentNew->date)->toDateString();

                if ($installmentNew->totalDebt == 0) {
                    $installmentNew->status = 'Pagado';
                } elseif ($dueDate < $today) {
                    // Si no está pagado y la fecha de vencimiento ya pasó, marcar como "Vencido"
                    $installmentNew->status = 'Vencido';
                } else {
                    // Si no está pagado y no ha vencido, marcar como "Pendiente"
                    $installmentNew->status = 'Pendiente';
                }
                $installmentNew->save();

            }

        }

// Actualizar el total de la deuda en el installment

        // Recuperar la nota de crédito actualizada con sus relaciones
        $object = CreditNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->find($object->id);

        Bitacora::create([
            'user_id'     => Auth::id(),     // ID del usuario que realiza la acción
            'record_id'   => $object->id,    // El ID del usuario afectado
            'action'      => 'PUT',          // Acción realizada
            'table_name'  => 'credit_notes', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Actualiza Nota Crédito', // Descripción de la acción
            'ip_address'  => $request->ip(),            // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),     // Información sobre el navegador/dispositivo
        ]);

        return response()->json($object, 200);
    }
//COMENTADO DESDE DEV
    public function declararNotaCredito(Request $request, $idventa)
    {
        $funcion    = "enviarNotaCredito";
        $empresa_id = 1;

        $notaCredito = CreditNote::find($idventa);

        if (! $notaCredito) {
            return response()->json(['message' => 'NOTA DE CREDITO NO ENCONTRADA'], 422);
        }
        if ($notaCredito->status_facturado != 'Pendiente') {
            return response()->json(['message' => 'NOTA DE CREDITO NO SE ENCUENTRA EN PENDIENTE DE ENVÍO'], 422);
        }

        if (1) {
            // Construir la URL con los parámetros
            $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
            $params = [
                'funcion'    => $funcion,
                'idventa'    => $idventa,
                'empresa_id' => $empresa_id,
            ];
            $url .= '?' . http_build_query($params);

            // Log de inicio de la solicitud
            Log::error("Iniciando solicitud para enviar Nota de Crédito. ID venta: $idventa, Empresa ID: $empresa_id, URL: $url");

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
                Log::error("Error en cURL al enviar Nota de Crédito. ID venta: $idventa, Error: $error");
                // echo 'Error en cURL: ' . $error;
            } else {
                // Registrar la respuesta en el log
                Log::error("Respuesta recibida de Nota de Crédito para ID venta: $idventa, Respuesta: $response");
                // Mostrar la respuesta
                // echo 'Respuesta: ' . $response;
            }

            // Cerrar cURL
            curl_close($ch);

            // Log del cierre de la solicitud
            Log::error("Solicitud de Nota de Crédito finalizada para ID venta: $idventa.");

            $notaCredito->status_facturado = 'Enviado';
            $notaCredito->save();
        } else {
            return response()->json([
                "message" => "NC De Ajuste, Solicitar envio Manual",
            ], 422);
        }

        $object = CreditNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->find($notaCredito->id);

        Bitacora::create([
            'user_id'     => Auth::id(),     // ID del usuario que realiza la acción
            'record_id'   => $object->id,    // El ID del usuario afectado
            'action'      => 'POST',         // Acción realizada
            'table_name'  => 'credit_notes', // Tabla afectada
            'data'        => json_encode($object),
            'description' => 'Guardar Nota Crédito', // Descripción de la acción
            'ip_address'  => $request->ip(),          // Dirección IP del usuario
            'user_agent'  => $request->userAgent(),   // Información sobre el navegador/dispositivo
        ]);
    }
//COMENTADO DESDE DEV
    public function declararNCHoy()
    {
        $empresa_id = 1;
        // $fecha = Carbon::now()->toDateString();
        $fecha = Carbon::now()->subDay()->toDateString();

        // Obtener todas las guías que cumplen con la fecha y el estado "Pendiente"
        $notascredito = CreditNote::whereDate('created_at', $fecha)
            ->where('status_facturado', 'Pendiente')
            ->get();

        Log::error("INICIO ENVIO MASIVO $fecha: DE NC DEL DÍA");
        $contador = 0;
        // Procesar cada guía encontrada
        foreach ($notascredito as $venta) {
            $numero  = $venta->sequentialNumber;
            $funcion = "enviarNotaCredito";

            $idventa = $venta->id;
            $contador++;
            // Construir la URL con los parámetros
            $url    = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
            $params = [
                'funcion'    => $funcion,
                'idventa'    => $idventa,
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
                Log::error("Error en cURL al enviar NC. ID nota: $idventa,$funcion Error: $error");
                // echo 'Error en cURL: ' . $error;
            } else {
                // Registrar la respuesta en el log
                Log::error("Respuesta recibida de NC para ID nota: $idventa,$funcion Respuesta: $response");
                // Mostrar la respuesta
                // echo 'Respuesta: ' . $response;
            }

            // Cerrar cURL
            curl_close($ch);

            // Actualizar el estado de la guía a "Enviado"
            $venta->status_facturado = 'Enviado';
            $venta->save();

            // Log del cierre de la solicitud para cada guía
            Log::info("Solicitud de NC finalizada para ID NC: $idventa. $funcion");

            Bitacora::create([
                'user_id'     => null,           // ID del usuario que realiza la acción
                'record_id'   => $venta->id,     // El ID del usuario afectado
                'action'      => 'CRON',         // Acción realizada
                'table_name'  => 'credit_notes', // Tabla afectada
                'data'        => json_encode($venta),
                'description' => 'Declaración Automatica NC 23:45 pm', // Descripción de la acción
                'ip_address'  => null,                                  // Dirección IP del usuario
                'user_agent'  => null,                                  // Información sobre el navegador/dispositivo
            ]);
        }
        Log::error("FINALIZADO ENVIO MASIVO $fecha, NC ENVIADAS: $contador");

    }

}
