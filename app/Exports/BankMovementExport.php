<?php
namespace App\Exports;

use App\Models\BankAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankMovementExport implements WithMultipleSheets
{
    protected $data;

    protected $from;
    protected $to;

    public function __construct($data = [], $from, $to)
    {
        $this->data = collect($data)->sortBy('date_moviment');
        $this->from = $from;
        $this->to   = $to;
    }

    public function sheets(): array
    {
        $sheets      = [];
        $groupedData = $this->data->groupBy(fn($item) => substr($item['bank']['name'] ?? 'Sin Banco', 0, 30));

        foreach ($groupedData as $bankName => $movements) {
            $sheets[] = new BankSheetExport($this->generateSheetTitle($bankName), $movements, $this->from, $this->to);
        }

        return $sheets;
    }
    protected function generateSheetTitle($bankName)
    {
        return substr("$bankName", 0, 31);
    }
}

class BankSheetExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected string $bankName;
    protected $from;
    protected $to;
    protected Collection $movements;

    public function __construct(string $bankName, Collection $movements, $from, $to)
    {
        $this->bankName  = $bankName;
        $this->movements = $movements->groupBy(fn($item) => $item['bank_account']['account_number'] ?? 'Desconocida');
        $this->from      = $from;
        $this->to        = $to;
    }

    public function collection(): Collection
    {
        $data = collect();

        foreach ($this->movements as $accountNumber => $transactions) {
            $data->push(["Número de Cuenta:", $accountNumber, '', '', '', '', '', '', '', '']); // Separador de cuenta
            $saldo = 0;

            $account = BankAccount::where('account_number', $accountNumber)->first();
            // Convertir la fecha de filtro a Carbon
            $fromDate = Carbon::parse($this->from);

            $saldo = DB::table('bank_movements')
                ->where('bank_account_id', $account->id)
                ->where('date_moviment', '<', $fromDate)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(CASE WHEN type_moviment = "ENTRADA" THEN total_moviment ELSE -total_moviment END) as saldo')
                ->value('saldo');
            // Insertar saldo inicial
            $fechaSaldoInicial = $transactions->first()['date_moviment'] ?? '';
            $data->push([
                Date::stringToExcel($fechaSaldoInicial),
                "'" . $accountNumber,
                'Desconocido',
                'SALDO INICIAL',
                '',
                '',
                '',
                '0.00',
                number_format($saldo, 2, '.', ''),
                '',
            ]);

            $transactions->each(function ($item) use (&$data, &$saldo) {
                $monto = $item['total_moviment'] ?? 0;
                $saldo += ($item['type_moviment'] === 'ENTRADA' ? 1 : -1) * $monto;

                $persona = trim(($item['person']['names'] ?? '') . ' ' . ($item['person']['fatherSurname'] ?? ''));
                $persona = $persona ?: ($item['person']['businessName'] ?? 'Sin nombre');

                $data->push([
                    Date::stringToExcel($item['date_moviment']),
                    "'" . ($item['bank_account']['account_number'] ?? 'Desconocida'),
                    ['bank']['name'] ?? 'Desconocido',
                    $item['type_moviment'] ?? '',
                    $persona,
                    $item['transaction_concept']['name'] ?? '',
                    $item['currency'] ?? '',
                    number_format($monto, 2, '.', ''),
                    number_format($saldo, 2, '.', ''),
                    $item['comment'] ?? '',
                ]);
            });

            $data->push(['', '', '', '', '', '', '', '', '', '']); // Línea vacía tras cada cuenta
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Fecha', 'Cuenta', 'Banco', 'Tipo', 'Persona', 'Concepto', 'Moneda', 'Total', 'Saldo Hasta Fecha', 'Comentario'];
    }

    public function map($row): array
    {
        return array_values($row);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $sheet->setTitle($this->bankName);

        // Encabezado principal
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']], // Azul oscuro
            'alignment' => ['horizontal' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Bordes en toda la tabla
        $sheet->getStyle("A1:J$highestRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Formato de moneda y fecha
        $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle('H:I')->getNumberFormat()->setFormatCode('#,##0.00');

        // Centrar número de cuenta
        $sheet->getStyle("B2:B$highestRow")->getAlignment()->setHorizontal('center');

        // Aplicar colores según tipo de movimiento
        for ($row = 2; $row <= $highestRow; $row++) {
            $tipo = $sheet->getCell("D$row")->getValue();
            if ($tipo === 'SALDO INICIAL' || $tipo === 'ENTRADA') {
                $sheet->getStyle("A$row:J$row")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']], // Verde claro
                ]);
            } elseif ($tipo === 'SALIDA') {
                $sheet->getStyle("A$row:J$row")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']], // Rojo claro
                ]);
            }
        }

        // Ajuste automático de columnas
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

}
