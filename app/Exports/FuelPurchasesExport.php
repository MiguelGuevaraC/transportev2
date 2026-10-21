<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FuelPurchasesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $purchases) {}

    public function collection(): Collection
    {
        return $this->purchases->map(function ($purchase) {
            $person = $purchase->worker?->person;
            return [
                'Fecha' => optional($purchase->fuel_date)->format('d/m/Y'),
                'Grifo' => $purchase->station?->name,
                'Vehículo' => $purchase->vehicle?->currentPlate,
                'Chofer' => trim(implode(' ', array_filter([
                    $person?->names, $person?->fatherSurname, $person?->motherSurname,
                ]))),
                'Odómetro' => (float) $purchase->odometer_km,
                'Combustible' => $purchase->fuel_type,
                'Cantidad' => (float) $purchase->quantity,
                'Precio unitario' => (float) $purchase->unit_price,
                'Total' => (float) $purchase->total,
                'Condición' => $purchase->payment_condition,
                'Forma de pago' => $purchase->payment_method,
                'Factura' => $purchase->invoice_number,
                'Operación pago' => $purchase->payment_operation_number,
                'Vencimiento' => optional($purchase->due_date)->format('d/m/Y'),
                'Estado' => $purchase->status,
                'Observación' => $purchase->observation,
            ];
        });
    }

    public function headings(): array
    {
        return ['Fecha', 'Grifo', 'Vehículo', 'Chofer', 'Odómetro', 'Combustible', 'Cantidad', 'Precio unitario', 'Total', 'Condición', 'Forma de pago', 'Factura', 'Operación pago', 'Vencimiento', 'Estado', 'Observación'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
