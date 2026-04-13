<?php

namespace App\Services;

use App\Models\CargaDocument;
use App\Models\DetailReception;
use App\Models\DocumentCargaDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class KardexReportService
{
    protected mixed $normalizedProductIds;

    public function __construct(
        mixed $productIds,
        protected ?string $from,
        protected ?string $to,
        protected ?int $branchOfficeId
    ) {
        $this->normalizedProductIds = $this->normalizeProductIdsInput($productIds);
    }

    public function toFlatCollection(): Collection
    {
        if (! $this->branchOfficeId) {
            return new Collection();
        }

        $productIds = $this->resolveProductIds();
        if ($productIds === []) {
            return new Collection();
        }

        $finalCollection = new Collection();
        $toDate          = $this->to ? Carbon::parse($this->to)->endOfDay() : now();

        foreach ($productIds as $product_id) {
            $productName = Product::find($product_id)?->description ?? 'SIN NOMBRE';

            $saldoInicial = $this->getStockBefore((int) $product_id);

            $finalCollection->push(
                ['is_header' => true, 'movement_date' => $productName, 'type' => '', 'concept' => '', 'document' => '', 'num_anexo' => '', 'person' => '', 'distribuidor' => '', 'quantity' => '', 'saldo' => '', 'comment' => ''],
                ['is_header' => true, 'movement_date' => 'Fecha Movimiento', 'type' => 'Tipo Movimiento', 'concept' => 'Concepto', 'document' => 'Documento', 'num_anexo' => 'Número Anexo', 'person' => 'Persona', 'distribuidor' => 'Distribuidor', 'quantity' => 'Cantidad', 'saldo' => 'Saldo', 'comment' => 'Comentario'],
                ['movement_date' => $this->from ?? now(), 'type' => 'SALDO INICIAL', 'concept' => 'Stock acumulado hasta ' . ($this->from ?? 'Hoy'), 'document' => '', 'num_anexo' => '-', 'person' => '-', 'distribuidor' => '-', 'quantity' => 0, 'saldo' => $saldoInicial, 'comment' => '-']
            );

            $rows = $this->movementRowsForProduct((int) $product_id, $toDate);

            $saldo   = (float) $saldoInicial;
            $records = $rows->sortBy(function ($r) {
                try {
                    return Carbon::parse($r['movement_date'])->timestamp;
                } catch (\Throwable) {
                    return 0;
                }
            })->values()->map(function ($row) use (&$saldo) {
                if (($row['type'] ?? '') === 'ENTRADA') {
                    $saldo += (float) $row['quantity'];
                } else {
                    $saldo -= (float) $row['quantity'];
                }
                $row['saldo'] = $saldo;

                return $row;
            });

            $finalCollection = $finalCollection->merge($records)->push([
                'movement_date' => '', 'type' => '', 'concept' => '', 'document' => '', 'num_anexo' => '',
                'person'        => '', 'distribuidor' => '', 'quantity' => '', 'saldo' => '', 'comment' => '',
            ]);
        }

        return $finalCollection;
    }

    public function toJsonStructure(): array
    {
        if (! $this->branchOfficeId) {
            return ['branch_office_id' => null, 'from' => $this->from, 'to' => $this->to, 'products' => []];
        }

        $productIds = $this->resolveProductIds();
        $toDate     = $this->to ? Carbon::parse($this->to)->endOfDay() : now();
        $out        = [];

        foreach ($productIds as $pid) {
            $pid       = (int) $pid;
            $saldoIni  = $this->getStockBefore($pid);
            $rows      = $this->movementRowsForProduct($pid, $toDate);
            $saldo     = (float) $saldoIni;
            $movements = $rows->sortBy(function ($r) {
                try {
                    return Carbon::parse($r['movement_date'])->timestamp;
                } catch (\Throwable) {
                    return 0;
                }
            })->values()->map(function ($row) use (&$saldo) {
                if (($row['type'] ?? '') === 'ENTRADA') {
                    $saldo += (float) $row['quantity'];
                } else {
                    $saldo -= (float) $row['quantity'];
                }
                $row['saldo'] = $saldo;

                return $row;
            })->values()->all();

            $out[] = [
                'product_id'   => $pid,
                'product_name' => Product::find($pid)?->description ?? 'SIN NOMBRE',
                'saldo_inicial'=> $saldoIni,
                'movements'    => $movements,
                'saldo_final'  => $saldo,
            ];
        }

        return [
            'branch_office_id' => $this->branchOfficeId,
            'from'             => $this->from,
            'to'               => $this->to,
            'products'         => $out,
        ];
    }

    protected function normalizeProductIdsInput(mixed $productIds): mixed
    {
        if ($productIds === null || $productIds === 'null') {
            return null;
        }
        if (is_array($productIds)) {
            $ids = array_values(array_filter($productIds, fn ($v) => $v !== null && $v !== '' && $v !== 'null'));

            return $ids === [] ? null : array_map('intval', $ids);
        }

        return [(int) $productIds];
    }

    protected function resolveProductIds(): array
    {
        if (is_array($this->normalizedProductIds) && $this->normalizedProductIds !== []) {
            return $this->normalizedProductIds;
        }

        $toDate = $this->to ? Carbon::parse($this->to)->endOfDay() : now();

        $fromDetail = DocumentCargaDetail::query()
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereHas('document_carga', function ($q) use ($toDate) {
                $q->whereNull('deleted_at');
                if ($this->from) {
                    $q->whereBetween('movement_date', [Carbon::parse($this->from)->startOfDay(), $toDate]);
                }
            })
            ->distinct()
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $fromHeader = CargaDocument::query()
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereNull('deleted_at')
            ->whereNotNull('product_id')
            ->whereDoesntHave('details')
            ->when($this->from, fn ($q) => $q->whereBetween('movement_date', [Carbon::parse($this->from)->startOfDay(), $toDate]))
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $fromRecep = DetailReception::query()
            ->whereNull('deleted_at')
            ->whereNotNull('product_id')
            ->whereHas('reception', function ($q) {
                $q->where('branchOffice_id', $this->branchOfficeId);
            })
            ->when($this->from, function ($q) use ($toDate) {
                $q->whereHas('reception.firstCarrierGuide', function ($gq) use ($toDate) {
                    $gq->whereBetween('transferStartDate', [Carbon::parse($this->from)->startOfDay(), $toDate]);
                });
            })
            ->distinct()
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $merged = array_values(array_unique(array_merge($fromDetail, $fromHeader, $fromRecep)));

        return array_slice($merged, 0, 150);
    }

    protected function movementRowsForProduct(int $productId, Carbon $toDate): Collection
    {
        $rows = new Collection();

        $detailQuery = DocumentCargaDetail::query()
            ->with(['document_carga.person', 'document_carga.distribuidor'])
            ->where('product_id', $productId)
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereHas('document_carga', function ($q) use ($toDate) {
                $q->whereNull('deleted_at');
                if ($this->from) {
                    $q->whereBetween('movement_date', [Carbon::parse($this->from)->startOfDay(), $toDate]);
                }
            });

        foreach ($detailQuery->get() as $d) {
            $doc = $d->document_carga;
            if (! $doc) {
                continue;
            }
            $rows->push([
                'movement_date' => $doc->movement_date,
                'type'          => strtoupper((string) $doc->movement_type),
                'concept'       => 'DOCUMENTO DE CARGA',
                'document'      => $doc->code_doc,
                'num_anexo'     => $d->num_anexo ?? $doc->num_anexo,
                'person'        => trim(($doc->person->names ?? '') . ' ' . ($doc->person->businessName ?? '')),
                'distribuidor'  => trim(($doc->distribuidor->names ?? '') . ' ' . ($doc->distribuidor->businessName ?? '')),
                'quantity'      => (float) $d->quantity,
                'saldo'         => null,
                'comment'       => $d->comment ?? $doc->comment ?? '-',
            ]);
        }

        $headerQ = CargaDocument::query()
            ->with(['person', 'distribuidor'])
            ->where('branchOffice_id', $this->branchOfficeId)
            ->where('product_id', $productId)
            ->whereNull('deleted_at')
            ->whereDoesntHave('details')
            ->when($this->from, fn ($q) => $q->whereBetween('movement_date', [Carbon::parse($this->from)->startOfDay(), $toDate]));

        foreach ($headerQ->get() as $doc) {
            $rows->push([
                'movement_date' => $doc->movement_date,
                'type'          => strtoupper((string) $doc->movement_type),
                'concept'       => 'DOCUMENTO DE CARGA (cabecera)',
                'document'      => $doc->code_doc,
                'num_anexo'     => $doc->num_anexo,
                'person'        => trim(($doc->person->names ?? '') . ' ' . ($doc->person->businessName ?? '')),
                'distribuidor'  => trim(($doc->distribuidor->names ?? '') . ' ' . ($doc->distribuidor->businessName ?? '')),
                'quantity'      => (float) $doc->quantity,
                'saldo'         => null,
                'comment'       => $doc->comment ?? '-',
            ]);
        }

        $queryRecep = DetailReception::with(['reception.sender', 'reception.firstCarrierGuide'])
            ->whereNull('deleted_at')
            ->where('product_id', $productId)
            ->whereHas('reception', fn ($q) => $q->where('branchOffice_id', $this->branchOfficeId));

        if ($this->from) {
            $queryRecep->whereHas('reception.firstCarrierGuide', fn ($q) =>
                $q->whereBetween('transferStartDate', [Carbon::parse($this->from)->startOfDay(), $toDate]));
        }

        foreach ($queryRecep->get() as $detail) {
            $guide = $detail->reception?->firstCarrierGuide;
            if (! $guide) {
                continue;
            }
            $rows->push([
                'movement_date' => $guide->transferStartDate ?? now(),
                'type'          => 'SALIDA',
                'concept'       => 'GUIA TRANSPORTE',
                'document'      => $guide->numero ?? 'Sin Número',
                'num_anexo'     => $guide->document ?? 'Sin Número',
                'person'        => trim(($detail->reception->sender->names ?? '') . ' ' . ($detail->reception->sender->businessName ?? '')),
                'distribuidor'  => '',
                'quantity'      => (float) $detail->cant,
                'saldo'         => null,
                'comment'       => '-',
            ]);
        }

        return $rows;
    }

    protected function getStockBefore(int $productId): float
    {
        $toDate = $this->from ? Carbon::parse($this->from)->startOfDay() : now();

        $entradaDet = (float) DocumentCargaDetail::query()
            ->where('product_id', $productId)
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereHas('document_carga', fn ($q) => $q->whereNull('deleted_at')
                ->where('movement_type', 'ENTRADA')
                ->where('movement_date', '<', $toDate))
            ->sum('quantity');

        $salidaDet = (float) DocumentCargaDetail::query()
            ->where('product_id', $productId)
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereHas('document_carga', fn ($q) => $q->whereNull('deleted_at')
                ->where('movement_type', 'SALIDA')
                ->where('movement_date', '<', $toDate))
            ->sum('quantity');

        $entradaHead = (float) CargaDocument::query()
            ->where('product_id', $productId)
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereNull('deleted_at')
            ->where('movement_type', 'ENTRADA')
            ->where('movement_date', '<', $toDate)
            ->whereDoesntHave('details')
            ->sum('quantity');

        $salidaHead = (float) CargaDocument::query()
            ->where('product_id', $productId)
            ->where('branchOffice_id', $this->branchOfficeId)
            ->whereNull('deleted_at')
            ->where('movement_type', 'SALIDA')
            ->where('movement_date', '<', $toDate)
            ->whereDoesntHave('details')
            ->sum('quantity');

        $totalDetailQuantity = (float) (DetailReception::where('product_id', $productId)
            ->whereHas('reception', function ($query) use ($toDate) {
                $query->where('branchOffice_id', $this->branchOfficeId)
                    ->whereHas('firstCarrierGuide', fn ($q) =>
                        $q->where('transferStartDate', '<', $toDate));
            })
            ->whereNull('deleted_at')
            ->sum('cant') ?? 0);

        return ($entradaDet - $salidaDet) + ($entradaHead - $salidaHead) - $totalDetailQuantity;
    }
}
