<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CajaYadiraExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly Collection $movements)
    {
    }

    public function collection(): Collection
    {
        return $this->movements->map(function ($movement) {
            return [
                'Fecha' => optional($movement->movement_date)->format('d/m/Y'),
                'Hora' => $movement->movement_time,
                'Tipo' => $movement->type,
                'N° operación' => $movement->operation_number,
                'Monto' => (float) $movement->amount,
                'Persona' => $this->personName($movement->person),
                'Concepto' => $movement->concept,
                'Observación' => $movement->observation,
                'Comprobante' => $movement->voucher_path,
            ];
        });
    }

    public function headings(): array
    {
        return ['Fecha', 'Hora', 'Tipo', 'N° operación', 'Monto', 'Persona', 'Concepto', 'Observación', 'Comprobante'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    private function personName($person): string
    {
        if (! $person) {
            return '';
        }

        return strtoupper((string) $person->typeofDocument) === 'RUC'
            ? (string) ($person->businessName ?? '')
            : trim(implode(' ', array_filter([
                $person->names,
                $person->fatherSurname,
                $person->motherSurname,
            ])));
    }
}
