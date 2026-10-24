<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ConsolidatedGuidesExport implements WithMultipleSheets
{
    private $receptions;
    private $guides;
    private $receivables;

    public function __construct(array $receptions, array $guides, array $receivables)
    {
        $this->receptions = $receptions;
        $this->guides = $guides;
        $this->receivables = $receivables;
    }

    public function sheets(): array
    {
        return [
            new ConsolidatedSheetExport(
                'Recepción de guías',
                ['Código recepción', 'Fecha recepción', 'Guía', 'Doc. venta', 'Remitente', 'Destinatario', 'Origen', 'Destino', 'Cliente paga', 'Flete', 'Deuda', 'Carga', 'Peso', 'Situación factura', 'Estado recepción', 'Pagos'],
                collect($this->receptions),
            ),
            new ConsolidatedSheetExport(
                'Guías de transporte',
                ['ID', 'N° guía', 'Tipo', 'Estado entrega', 'Estado facturación', 'Fecha emisión', 'Fecha cargo', 'Fecha facturación estimada', 'Remitente', 'Destinatario', 'Origen', 'Destino', 'Vehículo', 'Conductor', 'Documento', 'Observación'],
                collect($this->guides),
            ),
            new ConsolidatedSheetExport(
                'Cuentas por cobrar',
                ['Item', 'RUC/DNI', 'Razón social', 'Fecha emisión', 'Fecha vencimiento', 'Días retraso', 'Documento', 'Total', 'Total deuda', 'Detracción', 'Saldo neto', 'Estado', 'Pagos'],
                collect($this->receivables),
            ),
        ];
    }
}
