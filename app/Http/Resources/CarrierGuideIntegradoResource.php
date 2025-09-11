<?php
namespace App\Http\Resources;

use App\Models\Moviment;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CarrierGuideIntegradoResource extends JsonResource
{
    public function toArray($request = null)
    {

        // Obtenemos el movimiento desde la relación o mediante nro_sale si no está relacionado
        $moviment = $this->reception?->moviment;

        if (!$moviment && !empty($this->reception?->nro_sale)) {
            $moviment = Moviment::where('sequentialNumber', $this->reception->nro_sale)->first();
        }

        return [

            'GUIA' => $this->numero ?? '',
            'FECHA' => isset($this->transferStartDate) ? date('d/m/Y', strtotime($this->transferStartDate)) : '',
            'CARGA' => collect($this?->reception?->details)->pluck('description')->implode(', ') ?? '',

            'RAZÓN SOCIAL REMITENTE' => $this->personNames($this->sender) ?? '',
            'RAZÓN SOCIAL DESTINATARIO' => $this->personNames($this->recipient) ?? '',
            'DOCUMENTO ANEXO' => $this->document ?? '',
            'PARTIDA' => $this?->origin?->name ?? '',
            'LLEGADA' => $this?->destination?->name ?? '',
            'CONDICIÓN DE PAGO' => $this?->reception?->conditionPay ?? '',
            'ESTADO MERCADERIA' => $this->status ?? '',
            // 'ESTADO FACTURACION' => $this->status_facturado ?? '',
            'ESTADO FACTURACION' => strtolower($this->type) === 'manual'
    ? 'Enviado'
    : ($this->status_facturado ?? ''),

    
            'DOCUMENTOS DE VENTA' => $this->reception && $this->reception->moviment
                ? $this->reception->moviment->sequentialNumber : (empty($this->reception?->nro_sale) ? 'Sin Venta' : $this->reception->nro_sale),

            'FECHA DOC' => $moviment ? date('d/m/Y', strtotime($moviment?->paymentDate)) : '',
            'MONTO TOTAL' => $moviment ? $moviment->total : '0',
            'MODO DE PAGO' => $moviment ? $this->getFormaPagoAttribute($moviment) : '',
            'NRO DE OPERACIÓN' => $moviment->operationNumber ?? ($moviment->nroTransferencia ?? ''),
            'AMORTIZACIONES_RESUMEN' => (function () use ($moviment) {
                $installments = $moviment?->installments ?? [];

                if (empty($installments)) {
                    return "No tienes deudas ni amortizaciones registradas.";
                }

                $resumen = "Resumen de tus deudas y amortizaciones:\n";

                foreach ($installments as $cuota) {
                    $fecha = date('d/m/Y', strtotime($cuota->date));
                    $estado = $cuota->status ?? 'Desconocido';
                    $montoTotal = number_format($cuota->total, 2);
                    $montoDeuda = number_format($cuota->totalDebt, 2);
                    $montoPagado = number_format($cuota->total - $cuota->totalDebt, 2);

                    $resumen .= "- Deuda con fecha $fecha, estado: $estado.\n";
                    $resumen .= "  Monto total: S/ $montoTotal, pagado: S/ $montoPagado, pendiente: S/ $montoDeuda.\n";

                    if (!empty($cuota->pay_installments)) {
                        $resumen .= "  Amortizaciones:\n";
                        foreach ($cuota->pay_installments as $pago) {
                            $fechaPago = date('d/m/Y', strtotime($pago->paymentDate));
                            $montoPago = number_format($pago->total, 2);
                            $tipoPago = $pago->type ?? 'Pago';
                            $nroOperacion = $pago->nroOperacion ?? 'N/A';

                            $resumen .= "    • Pago el $fechaPago por S/ $montoPago vía $tipoPago (Operación: $nroOperacion).\n";
                        }
                    }
                }

                return trim($resumen);
            })(),

            'FECHA DE RECEPCION DE GRT' => isset($this?->reception?->receptionDate) ? date('d/m/Y', strtotime($this?->reception?->receptionDate)) : 'Sin Fecha',
            'FECHA CARGO' => isset($this->date_cargo) ? date('d/m/Y', strtotime($this->date_cargo)) : 'Sin Fecha',
            'FECHA EST. FACTURACIÓN' => isset($this->date_est_facturacion) ? date('d/m/Y', strtotime($this->date_est_facturacion)) : 'Sin Fecha',

            'OBSERVACIONES' => $this->observation ?? '',
            'CONDUCTOR' => $this->personNames($this->driver->person) ?? '',
            'M' => $this?->user?->username ?? '',
        ];
    }

    public function getFormaPagoAttribute($moviment)
    {

        $resultado = DB::select(DB::raw('SELECT obtenerFormaPagoPorCaja(:id) AS formaPago'), [
            'id' => $moviment->id, // o el campo correcto
        ]);

        return $resultado[0]->formaPago ?? '';
    }
    public function personNames($person)
    {
        if ($person == null) {
            return '-'; // Si $person es nulo, retornamos un valor predeterminado
        }

        $typeD = strtolower($person?->typeofDocument ?? 'dni');

        if ($typeD === 'ruc') {
            return $person?->businessName ?? '-';
        } else {
            // Armar array con partes no vacías
            $parts = array_filter([
                $person?->names,
                $person?->fatherSurname,
                $person?->motherSurname,
                $person?->businessName,
            ], fn($value) => !empty($value));

            // Si no hay ningún nombre, devolvemos '-'
            if (empty($parts)) {
                return '-';
            }

            // Unir con espacios
            return implode(' ', $parts);
        }
    }

}
