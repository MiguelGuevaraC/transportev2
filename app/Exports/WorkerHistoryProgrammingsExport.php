<?php namespace App\Exports;

use App\Models\DetailWorker;
use App\Models\Worker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WorkerHistoryProgrammingsExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithMapping
{
    protected $workerId;
    protected $worker;

    public function __construct($workerId)
    {
        $this->workerId = $workerId;
        $this->worker   = Worker::find($workerId);
    }

    public function collection()
    {
        return DetailWorker::where('worker_id', $this->workerId)
            ->with([
                'programming.tract',
                'programming.platform',
                'programming.origin',
                'programming.destination',
            ])
            ->join('programmings', 'programmings.id', '=', 'detail_workers.programming_id')
            ->orderBy('programmings.id', 'desc')
            ->get();
    }

    public function map($detail): array
    {
        $programming = $detail->programming;

        return [
            $programming->departureDate ?? '',
            $programming->estimatedArrivalDate ?? '',
            $programming->numero ?? '',
            $programming->status ?? '',
            $programming->statusLiquidacion ?? '',
            $programming->totalWeight ?? '',
            $programming->origin->name ?? '',
            $programming->destination->name ?? '',
            $programming->tract->currentPlate ?? '',
            $programming->platform->currentPlate ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Fecha de Salida',
            'Fecha Estimada de Llegada',
            'Número',
            'Estado',
            'Estado de Liquidación',
            'Peso Total',
            'Origen',
            'Destino',
            'Placa del Tracto',
            'Placa de la Plataforma',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezados en negrita
        $sheet->getStyle('A2:J2')->getFont()->setBold(true);

        // Bordes para toda la tabla de datos (después del encabezado)
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A3:J{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Fila 1: Título
                $title = 'Historial de Programaciones del Conductor: ' . ($this->namePerson($this?->worker?->person  ?? null) ?? 'No encontrado');
                $sheet->setCellValue('A1', $title);
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Ajustar automáticamente las columnas A-J
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    function namePerson($person)
    {
        if ($person == null) {
            return '-'; // Si $person es nulo, retornamos un valor predeterminado
        }

        $typeD = $person->typeofDocument ?? 'dni';
        $cadena = '';

        if (strtolower($typeD) === 'ruc') {
            $cadena = $person->businessName;
        } else {
            $cadena = $person->names . ' ' . $person->fatherSurname . ' ' . $person->motherSurname;
        }

        // Corregido el operador ternario y la concatenación
        return $cadena;
    }
}
