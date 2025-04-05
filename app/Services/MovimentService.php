<?php
namespace App\Services;

use App\Models\BankAccount;
use App\Models\Bitacora;
use App\Models\Installment;
use App\Models\PayInstallment;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimentService
{
    protected $bankmovementService;

    public function __construct(BankMovementService $BankMovementService)
    {
        $this->bankmovementService = $BankMovementService;
    }
    public function getSalesPendientesByPerson($names): array
    {
        $nameFilter = $names;
        $perPage    = 50; // Límite de 50 resultados por página

        // Filtrar solo personas con movimientos de venta que tengan al menos un installment pendiente
        $query = Person::query()
            ->whereHas('movimentsVenta', function ($query) {
                $query->whereHas('installmentPendientes'); // Asegura que los movimientos tengan cuotas pendientes
            })
            ->with(['movimentsVenta.creditNote', 'movimentsVenta' => function ($query) {
                $query->whereHas('installmentPendientes') // Solo obtener movimientos con cuotas pendientes
                    ->with(['installmentPendientes']);        // Cargar las cuotas pendientes
            }]);

        if ($nameFilter) {
            $query->where(function ($subQuery) use ($nameFilter) {
                $subQuery->where('names', 'LIKE', '%' . $nameFilter . '%')
                    ->orWhere('fatherSurname', 'LIKE', '%' . $nameFilter . '%')
                    ->orWhere('motherSurname', 'LIKE', '%' . $nameFilter . '%')
                    ->orWhere('businessName', 'LIKE', '%' . $nameFilter . '%');
            });
        }

        // Aplicar paginación con límite de 50
        $persons = $query->orderByRaw("COALESCE(names, '') ASC, COALESCE(fatherSurname, '') ASC, COALESCE(motherSurname, '') ASC, COALESCE(businessName, '') ASC")
            ->paginate($perPage);

        return ['clients' => [
            'total'          => $persons->total(),
            'data'           => $persons->items(),
            'current_page'   => $persons->currentPage(),
            'last_page'      => $persons->lastPage(),
            'per_page'       => $persons->perPage(),
            'pagination'     => $perPage,
            'first_page_url' => $persons->url(1),
            'from'           => $persons->firstItem(),
            'next_page_url'  => $persons->nextPageUrl(),
            'path'           => $persons->path(),
            'prev_page_url'  => $persons->previousPageUrl(),
            'to'             => $persons->lastItem(),
        ]];
    }

    public function setPayMasiveInstallments($validatedData)
    {
        // Almacenar el total del pago masivo
        $totalPagoMasivo = 0;
        $payinstallments = [];
        foreach ($validatedData['paymasive'] as $paymentData) {
            $installment = Installment::find($paymentData['installment_id']);

            $amount = $paymentData['amount'];
            $totalPagoMasivo += $amount;
            $installment->totalDebt -= $amount;
            if ($installment->totalDebt == 0) {
                $installment->status = 'Pagado';
            }
            $installment->save();
            $tipo      = 'CC01';
            $resultado = DB::select(
                'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)), 0) + 1 AS siguienteNum
                 FROM pay_installments
                 WHERE SUBSTRING_INDEX(number, "-", 1) = ?',
                [$tipo]
            );

            $siguienteNum = isset($resultado[0]->siguienteNum) ? (int) $resultado[0]->siguienteNum : 1;

            $bank_account = isset($validatedData['bank_account_id']) ? BankAccount::find($validatedData['bank_account_id']) : null;
            $bank_id      = isset($validatedData['bank_id']) ? $validatedData['bank_id'] : null;

            $nroOperacion = $validatedData['nroOperacion'] ?? '';
            $comentario   = $validatedData['comentario'] ?? '';
            $total        = $amount;

            $payData = [
                'number'          => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentDate'     => now(), // Fecha de pago actual
                'total'           => $amount,
                'installment_id'  => $installment->id,
                'type'            => 'Pago Masivo',
                'bank_account_id' => $bank_account != null ? $bank_account->id : null,
                'bank_id'         => $bank_id ?? null,
                'nroOperacion'    => $nroOperacion,
                'comment'         => $comentario,
            ];

            // Registrar el pago en la tabla `pay_installments`
            $installmentPay = PayInstallment::create($payData);

            $payinstallments[] = $installmentPay;
            $moviment          = $installment->moviment;
            $moviment->updateSaldo();

            if ($bank_account != null) {
                $data_movement_bank = [
                    'pay_installment_id'     => isset($installmentPay->id) ? $installmentPay->id : null,
                    'bank_id'                => isset($bank_id) ? $bank_id : null,
                    'bank_account_id'        => isset($bank_account->id) ? $bank_account->id : null,
                    'currency'               => isset($bank_account->currency) ? $bank_account->currency : null,
                    'date_moviment'          => isset($installmentPay->paymentDate) ? $installmentPay->paymentDate : null,
                    'total_moviment'         => isset($total) ? $total : null,
                    'comment'                => isset($installmentPay->comment) ? $installmentPay->comment : null,
                    'user_created_id'        => isset(Auth::user()->id) ? Auth::user()->id : null,
                    'transaction_concept_id' => isset($validatedData['transaction_concept_id']) ? $validatedData['transaction_concept_id'] : 5, //deposito en cuenta
                    'person_id'              => isset($installmentPay->installment->moviment->person->id) ? $installmentPay->installment->moviment->person->id : null,
                    'type_moviment'          => 'ENTRADA',
                    'number_operation'       => $nroOperacion,
                ];
                $this->bankmovementService->createBankMovement($data_movement_bank);
            }
        }
        Bitacora::create([
            'user_id'     => Auth::id(),    // ID del usuario que realiza la acción
            'record_id'   => $moviment->id, // El ID del usuario afectado
            'action'      => 'POST',        // Acción realizada
            'table_name'  => 'paymasive',   // Tabla afectada
            'data'        => json_encode($payinstallments),
            'description' => 'Pago Masivo Realizado: ' . $totalPagoMasivo, // Descripción de la acción
            'ip_address'  => null,                                         // Dirección IP del usuario
            'user_agent'  => null,                                         // Información sobre el navegador/dispositivo
        ]);

        return response()->json(['message' => 'Pago masivo procesado correctamente.'], 200);
    }

}
