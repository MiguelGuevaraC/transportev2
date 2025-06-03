<?php
namespace App\Exports\guides;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GuidesIntegradoExport implements WithMultipleSheets
{
    protected Collection $data;

    public function __construct(array $data)
    {
        $this->data = collect($data);
    }

    public function sheets(): array
    {
        return $this->data
            ->groupBy(fn($item) => explode('-', $item->numero)[0] ?? 'SIN_SERIE')
            ->map(fn($items, $serie) => new GuideSheetExport($items->values(), $serie))
            ->values()
            ->all();
    }
}
