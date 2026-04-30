<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Box;
use App\Models\BranchOffice;
use App\Models\DebitNote;
use App\Models\Moviment;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DebitNoteController extends Controller
{
    public function index(Request $request)
    {
        $branch_office_id = $request->input('branch_office_id');
        $person_id = $request->input('person_id');
        $docRef = $request->input('docRef');
        $from = $request->input('from');
        $to = $request->input('to');
        $number = $request->input('number');
        $reason_code = $request->input('reason_code');

        if ($branch_office_id && is_numeric($branch_office_id)) {
            $branchOffice = BranchOffice::find($branch_office_id);
            if (! $branchOffice) {
                return response()->json([
                    'message' => 'Branch Office Not Found',
                ], 404);
            }
        }

        $box_id = $request->input('box_id');
        if ($box_id && is_numeric($box_id)) {
            $box = Box::find($box_id);
            if (! $box) {
                return response()->json([
                    'message' => 'Box Not Found',
                ], 404);
            }
        }

        $page = request()->get('page', 1);
        $perPage = request()->get('per_page', 15);

        $movCaja = DebitNote::
        when(! empty($branch_office_id) && $branch_office_id !== 'null', function ($query) use ($branch_office_id) {
            $query->where('branchOffice_id', $branch_office_id);
        })
            ->when(! empty($number) && $number !== 'null', function ($query) use ($number) {
                $query->where('number', 'like', '%'.$number.'%');
            })
            ->when(! empty($person_id) && $person_id !== 'null', function ($query) use ($person_id) {
                return $query->whereHas('moviment.person', function ($query) use ($person_id) {
                    $query->where('id', $person_id);
                });
            })
            ->when(! empty($docRef) && $docRef !== 'null', function ($query) use ($docRef) {
                return $query->whereHas('moviment', function ($query) use ($docRef) {
                    $query->where('sequentialNumber', $docRef);
                });
            })
            ->when(! empty($box_id) && $box_id !== 'null', function ($query) use ($box_id) {
                return $query->whereHas('moviment', function ($query) use ($box_id) {
                    $query->where('box_id', $box_id);
                });
            })
            ->when(! empty($from), function ($query) use ($from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when(! empty($to), function ($query) use ($to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->when(! empty($reason_code) && $reason_code !== 'null', function ($query) use ($reason_code) {
                $query->where('reason_code', $reason_code);
            })
            ->with(['branchOffice', 'moviment', 'moviment.person', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'total' => $movCaja->total(),
            'data' => $movCaja->items(),
            'current_page' => $movCaja->currentPage(),
            'last_page' => $movCaja->lastPage(),
            'per_page' => $movCaja->perPage(),
            'pagination' => $perPage,
            'first_page_url' => $movCaja->url(1),
            'from' => $movCaja->firstItem(),
            'next_page_url' => $movCaja->nextPageUrl(),
            'path' => $movCaja->path(),
            'prev_page_url' => $movCaja->previousPageUrl(),
            'to' => $movCaja->lastItem(),
        ], 200);
    }

    public function show($id)
    {
        $object = DebitNote::with(['branchOffice', 'moviment', 'moviment.person', 'moviment.reception.details'])
            ->find($id);
        if (! $object) {
            return response()->json(['error' => 'Debit Note Not Found'], 404);
        }

        return response()->json($object, 200);
    }

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'total' => 'required|numeric',
            'comment' => 'nullable|string',
            'reason' => 'nullable|string',
            'reason_code' => [
                'required',
                'string',
                'size:2',
                Rule::in(array_keys(DebitNote::REASON_CODES_SUNAT_10)),
            ],
            'moviment_id' => 'required|exists:moviments,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'newDate' => 'nullable|date',
            'newTotal' => 'nullable|numeric',
            'totalAjuste' => 'nullable|numeric',
            'fechaAjuste' => 'nullable|date',
            'percentaje' => 'nullable|numeric',
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
            if (! $box) {
                return response()->json([
                    'message' => 'Box Not Found',
                ], 404);
            }
        } else {
            $box = Box::find(Auth::user()->box_id);
        }
        if (! $box || $box->serie == null) {
            return response()->json(['error' => 'Caja No tiene Serie'], 422);
        }

        $moviment = Moviment::with(['installments'])->find($request->input('moviment_id'));
        if ($moviment->status === 'Anulada') {
            return response()->json(['error' => 'La venta está anulada; no se pueden emitir notas de débito.'], 422);
        }

        $monto = $this->resolverMontoIncrementoNotaDebito($request, $moviment);
        if ($monto < 0.01) {
            return response()->json(['error' => 'El monto de la nota de débito (incremento) debe ser al menos 0.01. Revise total, newTotal y totalAjuste.'], 422);
        }

        $tipo = $this->buildDebitNotePrefix($moviment, $box);

        $resultado2 = DB::select(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(number, LOCATE("-", number) + 1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                     FROM debit_notes
                     WHERE SUBSTRING(number, 1, ?) = ?',
            [strlen($tipo), $tipo]
        );

        $siguienteNum2 = (int) $resultado2[0]->siguienteNum;

        $this->applyDeltaToFirstInstallment($moviment, $monto);

        $data = [
            'number' => $tipo.'-'.str_pad((string) $siguienteNum2, 8, '0', STR_PAD_LEFT),
            'totalReferido' => $moviment->total ?? 0,
            'percentaje' => $request->input('percentaje') ?? 0,
            'total' => $monto,
            'reason_code' => $request->input('reason_code'),
            'reason' => $request->input('reason'),
            'comment' => $request->input('comment') ?? '-',
            'description' => $request->input('description') ?? '-',
            'totalAjuste' => $request->input('totalAjuste') ?? 0,
            'fechaAjuste' => $request->input('fechaAjuste') ?? null,
            'newDate' => $request->input('newDate') ?? null,
            'newTotal' => $request->input('newTotal') ?? null,
            'status_facturado' => 'Pendiente',
            'branchOffice_id' => $request->input('branchOffice_id'),
            'moviment_id' => $request->input('moviment_id'),
        ];

        $object = DebitNote::create($data);

        $descriptionString = '';
        $receptionDetails = $object->moviment?->reception?->details() ?? [];
        if ($receptionDetails != []) {
            $descriptions = $receptionDetails->pluck('description')->toArray();
            $descriptionString = implode(', ', $descriptions);
        }

        $object->productList = $descriptionString;
        $object->save();

        $object = DebitNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),
            'record_id' => $object->id,
            'action' => 'POST',
            'table_name' => 'debit_notes',
            'data' => json_encode($object),
            'description' => 'Guardar Nota Débito',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (config('debit_note.enviar_al_facturador')) {
            $this->invocarFacturadorNotaDebito($object->id);

            $object = DebitNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
                ->orderBy('id', 'desc')
                ->find($object->id);

            Bitacora::create([
                'user_id' => Auth::id(),
                'record_id' => $object->id,
                'action' => 'POST',
                'table_name' => 'debit_notes',
                'data' => json_encode($object),
                'description' => 'Enviar Nota Débito a facturador',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } else {
            Log::info("Nota de débito: envío a facturador desactivado (config debit_note.enviar_al_facturador o DEBIT_NOTE_ENVIAR_AL_FACTURADOR=false), id {$object->id}.");
        }

        return response()->json($object, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'total' => 'required|numeric',
            'comment' => 'nullable|string',
            'reason' => 'nullable|string',
            'reason_code' => [
                'required',
                'string',
                'size:2',
                Rule::in(array_keys(DebitNote::REASON_CODES_SUNAT_10)),
            ],
            'moviment_id' => 'required|exists:moviments,id',
            'branchOffice_id' => 'required|exists:branch_offices,id',
            'newDate' => 'nullable|date',
            'newTotal' => 'nullable|numeric',
            'totalAjuste' => 'nullable|numeric',
            'fechaAjuste' => 'nullable|date',
            'percentaje' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        if (Auth()->user()->box_id == null) {
            return response()->json(['error' => 'Usuario sin caja asignada'], 422);
        }

        $object = DebitNote::find($id);
        if (! $object) {
            return response()->json(['error' => 'Debit Note Not Found'], 404);
        }
        if ($request->input('moviment_id') != $object->moviment_id) {
            return response()->json(['error' => 'No se puede cambiar el movimiento venta de la nota de débito'], 422);
        }

        $moviment = Moviment::with(['installments'])->find($request->input('moviment_id'));
        if ($moviment->status === 'Anulada') {
            return response()->json(['error' => 'La venta está anulada.'], 422);
        }

        $montoResuelto = $this->resolverMontoIncrementoNotaDebito($request, $moviment);
        if ($montoResuelto < 0.01) {
            return response()->json(['error' => 'El monto de la nota de débito (incremento) debe ser al menos 0.01. Revise total, newTotal y totalAjuste.'], 422);
        }

        $totalAntes = (float) $object->total;
        $delta = $montoResuelto - $totalAntes;
        $this->applyDeltaToFirstInstallment($moviment, $delta);

        $object->totalReferido = $moviment->total ?? 0;
        $object->percentaje = $request->input('percentaje') ?? 0;
        $object->total = $montoResuelto;
        $object->reason_code = $request->input('reason_code');
        $object->reason = $request->input('reason');
        $object->comment = $request->input('comment') ?? '-';
        $object->description = $request->input('description') ?? '-';
        $object->totalAjuste = $request->input('totalAjuste') ?? 0;
        $object->fechaAjuste = $request->input('fechaAjuste') ?? null;
        $object->newDate = $request->input('newDate') ?? null;
        $object->newTotal = $request->input('newTotal') ?? null;
        $object->branchOffice_id = $request->input('branchOffice_id');
        $object->save();

        $descriptionString = '';
        $receptionDetails = $object->moviment?->reception?->details() ?? [];
        if ($receptionDetails != []) {
            $descriptions = $receptionDetails->pluck('description')->toArray();
            $descriptionString = implode(', ', $descriptions);
        }

        $object->productList = $descriptionString;
        $object->save();

        $object = DebitNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->find($object->id);

        Bitacora::create([
            'user_id' => Auth::id(),
            'record_id' => $object->id,
            'action' => 'PUT',
            'table_name' => 'debit_notes',
            'data' => json_encode($object),
            'description' => 'Actualiza Nota Débito',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($object, 200);
    }

    public function destroy($id)
    {
        $object = DebitNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])->find($id);
        if (! $object) {
            return response()->json(['error' => 'Debit Note Not Found'], 404);
        }

        $payload = json_encode($object);

        $moviment = Moviment::with(['installments'])->find($object->moviment_id);
        if ($moviment) {
            $monto = (float) $object->total;
            $this->applyDeltaToFirstInstallment($moviment, -$monto);
        }

        $rid = $object->id;
        $object->delete();

        Bitacora::create([
            'user_id' => Auth::id(),
            'record_id' => $rid,
            'action' => 'DELETE',
            'table_name' => 'debit_notes',
            'data' => $payload,
            'description' => 'Elimina Nota Débito',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['message' => 'Nota de débito eliminada'], 200);
    }

    public function declararNotaDebito(Request $request, $idventa)
    {
        $notaDebito = DebitNote::find($idventa);

        if (! $notaDebito) {
            return response()->json(['message' => 'NOTA DE DEBITO NO ENCONTRADA'], 422);
        }
        if ($notaDebito->status_facturado != 'Pendiente') {
            return response()->json(['message' => 'NOTA DE DEBITO NO SE ENCUENTRA EN PENDIENTE DE ENVÍO'], 422);
        }

        if (1) {
            $this->invocarFacturadorNotaDebito((int) $idventa);
        } else {
            return response()->json([
                "message" => "ND De Ajuste, Solicitar envio Manual",
            ], 422);
        }

        $object = DebitNote::with(['branchOffice', 'moviment', 'moviment.reception.details'])
            ->orderBy('id', 'desc')
            ->find($notaDebito->id);

        Bitacora::create([
            'user_id' => Auth::id(),
            'record_id' => $object->id,
            'action' => 'POST',
            'table_name' => 'debit_notes',
            'data' => json_encode($object),
            'description' => 'Enviar Nota Débito a facturador',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($object, 200);
    }

    public function declararNDHoy()
    {
        $fecha = Carbon::now()->subDay()->toDateString();

        $notasdebito = DebitNote::whereDate('created_at', $fecha)
            ->where('status_facturado', 'Pendiente')
            ->get();

        Log::error("INICIO ENVIO MASIVO $fecha: DE ND DEL DÍA");
        $contador = 0;
        foreach ($notasdebito as $venta) {
            $idventa = $venta->id;
            $contador++;
            $this->invocarFacturadorNotaDebito((int) $idventa);
            Log::info("Solicitud de ND finalizada para ID ND: $idventa. enviarNotaDebito");
            $venta->refresh();

            Bitacora::create([
                'user_id' => null,
                'record_id' => $venta->id,
                'action' => 'CRON',
                'table_name' => 'debit_notes',
                'data' => json_encode($venta),
                'description' => 'Declaración Automatica ND 23:45 pm',
                'ip_address' => null,
                'user_agent' => null,
            ]);
        }
        Log::error("FINALIZADO ENVIO MASIVO $fecha, ND ENVIADAS: $contador");
    }

    /**
     * Monto que se suma a la cuota (incremento de la ND).
     * 1) Si viene newTotal: max(0, newTotal - total de la venta).
     * 2) Si total > totalAjuste (ambos numéricos): total - totalAjuste (front: total = nuevo monto deseado, totalAjuste = base).
     * 3) Si no: total del request = incremento directo.
     */
    private function resolverMontoIncrementoNotaDebito(Request $request, Moviment $moviment): float
    {
        if ($request->filled('newTotal') && is_numeric($request->input('newTotal'))) {
            $nuevo = (float) $request->input('newTotal');
            $baseVenta = (float) $moviment->total;

            return max(0.0, $nuevo - $baseVenta);
        }

        $rawTotal = $request->input('total');
        $ajuste = $request->input('totalAjuste');
        if ($ajuste !== null && $ajuste !== '' && is_numeric($ajuste) && $rawTotal !== null && $rawTotal !== '' && is_numeric($rawTotal)) {
            $t = (float) $rawTotal;
            $a = (float) $ajuste;
            if ($t > $a) {
                return max(0.0, $t - $a);
            }
        }

        return (float) $rawTotal;
    }

    private function invocarFacturadorNotaDebito(int $idventa): void
    {
        if (! config('debit_note.enviar_al_facturador')) {
            Log::info("invocarFacturadorNotaDebito omitido: envío a facturador desactivado, id nota {$idventa}.");

            return;
        }

        $funcion = "enviarNotaDebito";
        $empresa_id = 1;
        $url = "https://develop.garzasoft.com:81/transporteFacturadorZip/controlador/contComprobante.php";
        $params = [
            'funcion' => $funcion,
            'idventa' => $idventa,
            'empresa_id' => $empresa_id,
        ];
        $url .= '?'.http_build_query($params);
        Log::error("Iniciando solicitud para enviar Nota de Débito. ID nota: $idventa, Empresa ID: $empresa_id, URL: $url");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            Log::error("Error en cURL al enviar Nota de Débito. ID nota: $idventa, Error: $error");
        } else {
            Log::error("Respuesta recibida de Nota de Débito para ID nota: $idventa, Respuesta: $response");
        }
        curl_close($ch);
        Log::error("Solicitud de Nota de Débito finalizada para ID nota: $idventa.");
        $notaDebito = DebitNote::find($idventa);
        if ($notaDebito) {
            $notaDebito->status_facturado = 'Enviado';
            $notaDebito->save();
        }
    }

    private function buildDebitNotePrefix(Moviment $moviment, Box $box): string
    {
        if (strpos($moviment->sequentialNumber, 'F') === 0) {
            $base = 'FD';
        } elseif (strpos($moviment->sequentialNumber, 'B') === 0) {
            $base = 'BD';
        } else {
            $base = 'ND';
        }
        $branchOfficeIdFormatted = str_pad((string) $box->serie, 2, '0', STR_PAD_LEFT);

        return $base.$branchOfficeIdFormatted;
    }

    private function applyDeltaToFirstInstallment(Moviment $moviment, float $deltaMonto)
    {
        if (abs($deltaMonto) < 0.0001) {
            return;
        }
        $installment = $moviment->installments->first();
        if (! $installment) {
            return;
        }
        $installment->refresh();
        $installment->total = (float) $installment->total + $deltaMonto;
        $amort = (float) $installment->payInstallments()->sum('total');
        if ($installment->total < $amort) {
            $installment->total = $amort;
        }
        if ($installment->total < 0) {
            $installment->total = 0;
        }
        $this->syncInstallmentState($installment, $moviment);
    }

    private function syncInstallmentState(Installment $installment, Moviment $moviment)
    {
        $installment->totalDebt = (float) $installment->total - (float) $installment->payInstallments()->sum('total');
        $today = now()->toDateString();
        $dueDate = Carbon::parse($installment->date)->toDateString();
        if ($installment->totalDebt == 0) {
            $installment->status = 'Pagado';
        } elseif ($dueDate < $today) {
            $installment->status = 'Vencido';
        } else {
            $installment->status = 'Pendiente';
        }
        $installment->save();
        $moviment->refresh();
        $moviment->updateSaldo();
    }
}
