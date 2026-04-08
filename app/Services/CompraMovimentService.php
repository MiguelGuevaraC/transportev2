<?php
namespace App\Services;

use App\Models\CompraMoviment;
use App\Models\CompraOrderDetail;
use App\Models\Payable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraMovimentService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCompraMovimentById(int $id): ?CompraMoviment
    {
        return CompraMoviment::find($id);
    }

    public function createCompraMoviment(array $data): CompraMoviment
    {
        return DB::transaction(function () use ($data) {
            $isPartial       = ! empty($data['is_partial']);
            $compraOrderId   = $data['compra_order_id'] ?? null;

            // Crear la compra inicialmente sin el número
            $compraMoviment = CompraMoviment::create($data);

            // Generar el número de movimiento basado en branchOffice_id (formato CM + ID sucursal 2 dígitos)
            $tipo = 'CM' . str_pad($data['branchOffice_id'], 2, '0', STR_PAD_LEFT);

            $siguienteNum = DB::selectOne('
        SELECT COALESCE(MAX(CAST(SUBSTRING(number, LOCATE("-", number) + 1) AS SIGNED)), 0) + 1 AS siguienteNum
        FROM compra_moviments
        WHERE SUBSTRING(number, 1, 4) = ?
    ', [$tipo])->siguienteNum;

            $compraMoviment->number = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
            $compraMoviment->save();

            // Insertar detalles si existen
            if (! empty($data['details']) && is_array($data['details'])) {
                $allowedAttributes = (new \App\Models\CompraMovimentDetail())->getFillable();

                foreach ($data['details'] as $detail) {
                    $detail['subtotal'] = $detail['quantity'] * $detail['unit_price'];
                    $cleanDetail        = array_intersect_key($detail, array_flip($allowedAttributes));
                    $compraMoviment->details()->create($cleanDetail);

                    if ($isPartial && $compraOrderId && ! empty($detail['compra_order_detail_id'])) {
                        $od = CompraOrderDetail::where('id', $detail['compra_order_detail_id'])
                            ->where('compra_order_id', $compraOrderId)
                            ->first();
                        if (! $od) {
                            throw ValidationException::withMessages([
                                'details' => 'El detalle de orden de compra no coincide con la orden indicada.',
                            ]);
                        }
                        $newReceived = (float) $od->quantity_received + (float) $detail['quantity'];
                        if ($newReceived > (float) $od->quantity) {
                            throw ValidationException::withMessages([
                                'details' => 'La cantidad del ingreso parcial supera lo pendiente en la línea de la orden OC.',
                            ]);
                        }
                        $od->quantity_received = $newReceived;
                        $od->save();
                    }
                }
            }
            if (! empty($data['payables']) && is_array($data['payables'])) {
                foreach ($data['payables'] as $payable) {
                    if (! isset($payable['monto']) || $payable['monto'] <= 0) {
                        continue;
                    }

                    $this->generate_credit_payment_custom($compraMoviment, $payable['monto'], $payable['days'] ?? 0);
                }
            }

            return $compraMoviment;
        });
    }
    public function updateCompraMoviment(CompraMoviment $compraMoviment, array $data): CompraMoviment
{
    // Actualiza atributos principales (igual que antes)
    $allowedCompraAttributes = $compraMoviment->getFillable();
    $filteredData            = array_intersect_key($data, array_flip($allowedCompraAttributes));
    $compraMoviment->update($filteredData);

    $allowedDetailAttributes = (new \App\Models\CompraMovimentDetail())->getFillable();

    // Manejo detalles (igual que antes)
    if (!empty($data['details']) && is_array($data['details'])) {
        $existingDetails = $compraMoviment->details()->get()->keyBy('repuesto_id');
        $incomingRepuestoIds = collect($data['details'])->pluck('repuesto_id')->toArray();

        foreach ($existingDetails as $repuestoId => $detail) {
            if (!in_array($repuestoId, $incomingRepuestoIds)) {
                $detail->delete();
            }
        }

        foreach ($data['details'] as $detail) {
            $detail['subtotal'] = $detail['quantity'] * $detail['unit_price'];
            $cleanDetail = array_intersect_key($detail, array_flip($allowedDetailAttributes));

            if ($existingDetails->has($detail['repuesto_id'])) {
                $existingDetails[$detail['repuesto_id']]->update($cleanDetail);
            } else {
                $compraMoviment->details()->create($cleanDetail);
            }
        }
    } else {
        $compraMoviment->details()->delete();
    }

    // Manejo payables con actualización y creación usando generate_credit_payment_custom
    if (!empty($data['payables']) && is_array($data['payables'])) {
        $existingPayables = $compraMoviment->payables()->get()->keyBy('id');
        $incomingIds = collect($data['payables'])->pluck('id')->filter()->map(fn($id) => (int) $id)->toArray();

        // Eliminar payables que ya no vienen
        foreach ($existingPayables as $id => $payable) {
            if (!in_array((int)$id, $incomingIds, true)) {
                $payable->delete();
            }
        }

        // Actualizar y crear
        foreach ($data['payables'] as $payableInput) {
       
           $dias=$payableInput['days'];
            if (!empty($payableInput['id']) && $existingPayables->has($payableInput['id'])) {
                // Actualizar existente
                $existingPayables[$payableInput['id']]->update([
                    'days' => $dias ?? 0,
                      'date'               => now()->addDays($dias),
                    'total' => $payableInput['monto'],
                    'totalDebt' => $payableInput['monto'],
                    'person_id' => $compraMoviment->proveedor_id,
                    // No actualizamos número ni otros campos fijos para evitar inconsistencias
                ]);
            } else {
                // Crear nuevo con tu función custom
                $this->generate_credit_payment_custom(
                    $compraMoviment,
                    $payableInput['monto'],
                    $dias ?? 0
                );
            }
        }
    } else {
        // Si no vienen payables, eliminar todos
        $compraMoviment->payables()->delete();
    }

    return $compraMoviment;
}

    public function destroyById($id)
    {
        return CompraMoviment::find($id)?->delete() ?? false;
    }

    public function generate_credit_payment_custom(CompraMoviment $moviment, float $monto, int $dias = 0)
    {
        $tipo         = 'CP01';
        $siguienteNum = DB::table('payables')
            ->whereRaw('SUBSTRING_INDEX(number, "-", 1) = ?', [$tipo])
            ->max(DB::raw('CAST(SUBSTRING_INDEX(number, "-", -1) AS UNSIGNED)')) + 1;

        $payableData = [
            'number'             => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
            'days'               => $dias,
            'date'               => now()->addDays($dias),
            'total'              => $monto,
            'totalDebt'          => $monto,
            'compra_moviment_id' => $moviment->id,
            'user_created_id'    => Auth::user()->id,
            'person_id'          => $moviment?->proveedor_id ?? null,
            'correlativo_ref'    => $moviment?->serie . '-' . $moviment?->correlative,
            'type_document_id'   => match (strtoupper($moviment?->document_type)) {
                'BOLETA'             => 1,
                'FACTURA'            => 4,
                default              => 8,
            },
            'type_payable'       => "MOVIMIENTO_COMPRA",
        ];

        Payable::create($payableData);
    }

}
