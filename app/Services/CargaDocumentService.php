<?php
namespace App\Services;

use App\Models\CargaDocument;
use App\Models\DocumentCargaDetail;
use App\Models\Product;
use App\Models\ProductStockByBranch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CargaDocumentService
{
    protected $commonService;
    protected $productService;

    public function __construct(CommonService $commonService, ProductService $productService)
    {
        $this->commonService  = $commonService;
        $this->productService = $productService;
    }

    protected function applyStockPivotMatch($query, $detail, int $branchOfficeId, bool $fromArray = true)
    {
        $productId  = $fromArray ? ($detail['product_id'] ?? null) : $detail->product_id;
        $almacenId  = $fromArray ? ($detail['almacen_id'] ?? null) : $detail->almacen_id;
        $seccionId  = $fromArray ? ($detail['seccion_id'] ?? null) : $detail->seccion_id;
        $numLot     = $fromArray ? ($detail['num_lot'] ?? null) : $detail->num_lot;
        $posCode    = $fromArray ? ($detail['position_code'] ?? null) : $detail->position_code;
        $dateExp    = $fromArray ? ($detail['date_expiration'] ?? null) : $detail->date_expiration;

        $query->where('product_id', $productId)
            ->where('branchOffice_id', $branchOfficeId)
            ->where('almacen_id', $almacenId);

        if ($seccionId !== null && $seccionId !== '') {
            $query->where('seccion_id', $seccionId);
        } else {
            $query->whereNull('seccion_id');
        }

        if ($numLot !== null && $numLot !== '') {
            $query->where('num_lot', $numLot);
        } else {
            $query->whereNull('num_lot');
        }

        if ($posCode !== null && $posCode !== '') {
            $query->where('position_code', $posCode);
        } else {
            $query->whereNull('position_code');
        }

        if ($dateExp !== null && $dateExp !== '') {
            $query->whereDate('date_expiration', $dateExp);
        } else {
            $query->whereNull('date_expiration');
        }
    }

    protected function stockPivotAttributes(array $detail, int $branchOfficeId): array
    {
        return [
            'product_id'      => $detail['product_id'],
            'branchOffice_id' => $branchOfficeId,
            'almacen_id'      => $detail['almacen_id'],
            'seccion_id'      => $detail['seccion_id'] ?? null,
            'date_expiration' => $detail['date_expiration'] ?? null,
            'num_lot'         => $detail['num_lot'] ?? null,
            'position_code'   => $detail['position_code'] ?? null,
        ];
    }

    public function getCargaDocumentById(int $id): ?CargaDocument
    {
        return CargaDocument::find($id);
    }

    public function findStockRowForDetail(array $detail, int $branchOfficeId): ?ProductStockByBranch
    {
        $query = ProductStockByBranch::query();
        $this->applyStockPivotMatch($query, $detail, $branchOfficeId, true);

        return $query->first();
    }

    /**
     * Clave física de ubicación: sucursal + almacén + sección + código de posición.
     */
    protected function storageSlotKey(int $branchId, int $almacenId, $seccionId, string $positionCode): string
    {
        $sec = ($seccionId === null || $seccionId === '') ? 'null' : (string) (int) $seccionId;

        return $branchId . '|' . $almacenId . '|' . $sec . '|' . $positionCode;
    }

    protected function seccionIdsMatch($a, $b): bool
    {
        $emptyA = $a === null || $a === '';
        $emptyB = $b === null || $b === '';
        if ($emptyA && $emptyB) {
            return true;
        }
        if ($emptyA || $emptyB) {
            return false;
        }

        return (int) $a === (int) $b;
    }

    protected function lotsMatch($a, $b): bool
    {
        $sa = ($a === null || $a === '') ? '' : trim((string) $a);
        $sb = ($b === null || $b === '') ? '' : trim((string) $b);

        return $sa === $sb;
    }

    protected function expirationsMatch($a, $b): bool
    {
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }

        return Carbon::parse($a)->toDateString() === Carbon::parse($b)->toDateString();
    }

    /**
     * Coincidencia detalle histórico ↔ fila de stock (mismo pivote de inventario).
     */
    protected function detailMatchesStockRow(DocumentCargaDetail $det, ProductStockByBranch $row): bool
    {
        if ((int) $det->product_id !== (int) $row->product_id) {
            return false;
        }
        if ((int) $det->branchOffice_id !== (int) $row->branchOffice_id) {
            return false;
        }
        if ((int) $det->almacen_id !== (int) $row->almacen_id) {
            return false;
        }
        if (! $this->seccionIdsMatch($det->seccion_id, $row->seccion_id)) {
            return false;
        }
        if (! $this->lotsMatch($det->num_lot, $row->num_lot)) {
            return false;
        }
        if (! $this->expirationsMatch($det->date_expiration, $row->date_expiration)) {
            return false;
        }

        $dp = trim((string) ($det->position_code ?? ''));
        $rp = trim((string) ($row->position_code ?? ''));

        return $dp !== '' && $dp === $rp;
    }

    protected function sumOldDetailQtyMatchingStockRow(Collection $oldDetails, ProductStockByBranch $row): float
    {
        $sum = 0.0;
        foreach ($oldDetails as $det) {
            if ($this->detailMatchesStockRow($det, $row)) {
                $sum += (float) $det->quantity;
            }
        }

        return $sum;
    }

    /**
     * ENTRADA: no permitir dos productos distintos en la misma posición (misma sucursal, almacén y sección).
     *
     * @return array<int, string> índice del detalle => mensaje
     */
    public function entradaPositionConflictErrors(array $data, ?int $excludeCargaDocumentId = null): array
    {
        $errors = [];
        if (($data['movement_type'] ?? '') !== 'ENTRADA') {
            return $errors;
        }

        $details   = $data['details'] ?? [];
        $branchId  = (int) ($data['branchOffice_id'] ?? 0);
        $oldDetails = collect();
        if ($excludeCargaDocumentId !== null && $excludeCargaDocumentId > 0) {
            $oldDetails = DocumentCargaDetail::where('document_carga_id', $excludeCargaDocumentId)->get();
        }

        // 1) Mismo request: varios productos distintos en la misma posición física
        $keyToIndices = [];
        foreach ($details as $i => $d) {
            $pos = trim((string) ($d['position_code'] ?? ''));
            if ($pos === '') {
                continue;
            }
            $key = $this->storageSlotKey(
                $branchId,
                (int) ($d['almacen_id'] ?? 0),
                $d['seccion_id'] ?? null,
                $pos
            );
            if (! isset($keyToIndices[$key])) {
                $keyToIndices[$key] = [];
            }
            $keyToIndices[$key][] = (int) $i;
        }
        foreach ($keyToIndices as $indices) {
            $pids = [];
            foreach ($indices as $i) {
                $pids[(int) ($details[$i]['product_id'] ?? 0)] = true;
            }
            if (count($pids) > 1) {
                $msg = 'No puede ingresar dos productos distintos en la misma posición dentro de este almacén.';
                foreach ($indices as $i) {
                    $errors[$i] = $msg;
                }
            }
        }

        // 2) Stock existente: otro producto con cantidad > 0 en la misma posición
        foreach ($details as $i => $d) {
            if (isset($errors[$i])) {
                continue;
            }
            $pos = trim((string) ($d['position_code'] ?? ''));
            if ($pos === '') {
                continue;
            }
            $productId = (int) ($d['product_id'] ?? 0);
            $almacenId = (int) ($d['almacen_id'] ?? 0);
            $seccionId = $d['seccion_id'] ?? null;

            $q = ProductStockByBranch::query()
                ->where('branchOffice_id', $branchId)
                ->where('almacen_id', $almacenId)
                ->where('product_id', '!=', $productId)
                ->where('stock', '>', 0)
                ->where('position_code', $pos);

            if ($seccionId !== null && $seccionId !== '') {
                $q->where('seccion_id', (int) $seccionId);
            } else {
                $q->whereNull('seccion_id');
            }

            foreach ($q->get() as $row) {
                $remaining = (float) $row->stock;
                if ($oldDetails->isNotEmpty()) {
                    $remaining -= $this->sumOldDetailQtyMatchingStockRow($oldDetails, $row);
                }
                if ($remaining > 0.00001) {
                    $errors[$i] = 'La posición «' . $pos . '» en este almacén ya tiene otro producto con stock. Elija otra posición o vacíe la ubicación antes.';

                    break;
                }
            }
        }

        return $errors;
    }

    public function createCargaDocument(array $data): CargaDocument
    {
        return DB::transaction(function () use ($data) {
            // Crear documento principal
            $cargaDocument = CargaDocument::create(array_intersect_key(
                $data,
                array_flip((new CargaDocument())->getFillable())
            ));

            // Generar código de documento
            $tipo         = 'C' . str_pad($cargaDocument->branchOffice_id, 3, '0', STR_PAD_LEFT);
            $tipo         = str_pad($tipo, 4, '0', STR_PAD_RIGHT);
            $siguienteNum = DB::selectOne('
                SELECT COALESCE(MAX(CAST(SUBSTRING(code_doc, LOCATE("-", code_doc) + 1) AS SIGNED)), 0) + 1 AS siguienteNum
                FROM carga_documents
                WHERE SUBSTRING(code_doc, 1, 4) = ?
            ', [$tipo])->siguienteNum;

            $cargaDocument->code_doc = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
            $cargaDocument->save();
            $movementType = $data['movement_type'] ?? 'ENTRADA'; // Por defecto ENTRADA

            // Procesar cada detalle y guardarlo
            foreach ($data['details'] as $detail) {
                DocumentCargaDetail::create([
                    'quantity'          => $detail['quantity'],
                    'product_id'        => $detail['product_id'],
                    'almacen_id'        => $detail['almacen_id'],
                    'seccion_id'        => $detail['seccion_id'],
                    'document_carga_id' => $cargaDocument->id,
                    'branchOffice_id'   => $data['branchOffice_id'],
                    'comment'           => isset($detail['comment']) ? $detail['comment'] : null,                 // Comentario opcional
                    'num_anexo'         => isset($detail['num_anexo']) ? $detail['num_anexo'] : null,             // Número de anexo opcional
                    'date_expiration'   => isset($detail['date_expiration']) ? $detail['date_expiration'] : null, // Fecha de vencimiento opcional
                    'num_lot'         => isset($detail['num_lot']) ? $detail['num_lot'] : null,             // Número de lot opcional
                    'position_code'   => isset($detail['position_code']) ? $detail['position_code'] : null,
                    'damaged_photo_path' => isset($detail['damaged_photo_path']) ? $detail['damaged_photo_path'] : null,
                    'created_at'        => now(),
                ]);

                // Actualizar el stock a nivel de sucursal, almacen y seccion
                $productStock = ProductStockByBranch::firstOrCreate(
                    $this->stockPivotAttributes($detail, (int) $data['branchOffice_id']),
                    [
                        'stock' => 0,
                    ]
                );

                // Incrementar el stock
                // Incrementar o disminuir el stock según el tipo de movimiento
                if (strtoupper($movementType) === 'SALIDA') {
                    $productStock->decrement('stock', $detail['quantity']);
                } else {
                    $productStock->increment('stock', $detail['quantity']);
                }
            }

            return $cargaDocument;
        });
    }

    public function updateCargaDocument(CargaDocument $cargaDocument, array $data): CargaDocument
    {
        return DB::transaction(function () use ($cargaDocument, $data) {
            // Revertir el stock anterior
            $branch_old = $cargaDocument->branchOffice_id;
            // Actualizar los datos del documento, incluyendo el stock actual
            $cargaDocument->update(
                array_intersect_key($data, array_flip($cargaDocument->getFillable())) + [
                    'stock_balance_before' => 0, // Asegura que no sea null
                ]
            );
            $movementType = $cargaDocument->movement_type ?? 'ENTRADA'; // Por defecto ENTRADA
            // Revertir stock de los detalles
            foreach ($cargaDocument->details as $detail) {
                $productStock = ProductStockByBranch::query();
                $this->applyStockPivotMatch($productStock, $detail, (int) $detail->branchOffice_id, false);
                $productStock = $productStock->first();

                if ($productStock) {
                    if (strtoupper($movementType) === 'SALIDA') {
                        $productStock->increment('stock', $detail->quantity);
                    } else {
                        $productStock->decrement('stock', $detail->quantity);
                        
                    }
                    
                }

                // Eliminar el detalle
                $detail->delete();
            }

            // Procesar cada detalle y guardarlo
            foreach ($data['details'] as $detail) {
                DocumentCargaDetail::create([
                    'quantity'          => $detail['quantity'],
                    'product_id'        => $detail['product_id'],
                    'almacen_id'        => $detail['almacen_id'],
                    'seccion_id'        => $detail['seccion_id'],
                    'document_carga_id' => $cargaDocument->id,
                    'branchOffice_id'   => $data['branchOffice_id'],
                    'comment'           => isset($detail['comment']) ? $detail['comment'] : null,                 // Comentario opcional
                    'num_anexo'         => isset($detail['num_anexo']) ? $detail['num_anexo'] : null,             // Número de anexo opcional
                    'date_expiration'   => isset($detail['date_expiration']) ? $detail['date_expiration'] : null, // Fecha de vencimiento opcional
                    'num_lot'           => isset($detail['num_lot']) ? $detail['num_lot'] : null,             // Número de lot opcional
                    'position_code'   => isset($detail['position_code']) ? $detail['position_code'] : null,
                    'damaged_photo_path' => isset($detail['damaged_photo_path']) ? $detail['damaged_photo_path'] : null,
                    'created_at'        => now(),
                ]);

                // Actualizar el stock a nivel de sucursal, almacen y seccion
                $productStock = ProductStockByBranch::firstOrCreate(
                    $this->stockPivotAttributes($detail, (int) $data['branchOffice_id']),
                    [
                        'stock' => 0,
                    ]
                );

                // Incrementar el stock
                // Incrementar o disminuir el stock según el tipo de movimiento
                if (strtoupper($movementType) === 'SALIDA') {
                    $productStock->decrement('stock', $detail['quantity']);
                } else {
                    $productStock->increment('stock', $detail['quantity']);
                }
            }
            
            return $cargaDocument;
        });
    }

    public function destroyById($id)
    {
        return DB::transaction(function () use ($id) {
            $cargaDocument = CargaDocument::find($id);

            if (! $cargaDocument) {
                throw new ModelNotFoundException("El documento de carga no existe.");
            }
            $movementType = $cargaDocument->movement_type ?? 'ENTRADA'; // Por defecto ENTRADA
            // Revertir stock de los detalles
            foreach ($cargaDocument->details as $detail) {
                $productStock = ProductStockByBranch::query();
                $this->applyStockPivotMatch($productStock, $detail, (int) $detail->branchOffice_id, false);
                $productStock = $productStock->first();

                if ($productStock) {
                    if (strtoupper($movementType) === 'SALIDA') {
                        $productStock->increment('stock', $detail->quantity);
                    } else {
                        $productStock->decrement('stock', $detail->quantity);
                    }
                }

                // Eliminar el detalle
                $detail->delete();
            }

            // Eliminar el documento principal
            return $cargaDocument->delete();
        });
    }

    private function updateProductStock(int $productId, int $id_branch, float $quantity, string $movementType, bool $isReversal = false): Int
    {
        $product = Product::findOrFail($productId);
        $this->productService->updatestock($product, $id_branch);
        return $product->stock;
    }
}
