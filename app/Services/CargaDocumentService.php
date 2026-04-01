<?php
namespace App\Services;

use App\Models\CargaDocument;
use App\Models\DocumentCargaDetail;
use App\Models\Product;
use App\Models\ProductStockByBranch;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function getCargaDocumentById(int $id): ?CargaDocument
    {
        return CargaDocument::find($id);
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
                    'created_at'        => now(),
                ]);

                // Actualizar el stock a nivel de sucursal, almacen y seccion
                $productStock = ProductStockByBranch::firstOrCreate(
                    [
                        'product_id'      => $detail['product_id'],
                        'branchOffice_id' => $data['branchOffice_id'],
                        'almacen_id'      => $detail['almacen_id'],
                        'seccion_id'      => $detail['seccion_id'],
                        'date_expiration'   => isset($detail['date_expiration']) ? $detail['date_expiration'] : null, // Fecha de vencimiento opcional
                        'num_lot'         => isset($detail['num_lot']) ? $detail['num_lot'] : null,             // Número de anexo opcional
                    ],
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
                $productStock = ProductStockByBranch::where('product_id', $detail->product_id)
                    ->where('branchOffice_id', $detail->branchOffice_id)
                    ->where('almacen_id', $detail->almacen_id)
                    ->where('seccion_id', $detail->seccion_id)
                    ->where('num_lot','like', $detail->num_lot)
                    ->first();

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
                    'created_at'        => now(),
                ]);

                // Actualizar el stock a nivel de sucursal, almacen y seccion
                $productStock = ProductStockByBranch::firstOrCreate(
                    [
                        'product_id'      => $detail['product_id'],
                        'branchOffice_id' => $data['branchOffice_id'],
                        'almacen_id'      => $detail['almacen_id'],
                        'seccion_id'      => $detail['seccion_id'],
                        'date_expiration'   => isset($detail['date_expiration']) ? $detail['date_expiration'] : null, // Fecha de vencimiento opcional
                        'num_lot'         => isset($detail['num_lot']) ? $detail['num_lot'] : null,             // Número de lot opcional
                    ],
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
                $productStock = ProductStockByBranch::where('product_id', $detail->product_id)
                    ->where('branchOffice_id', $detail->branchOffice_id)
                    ->where('almacen_id', $detail->almacen_id)
                    ->where('seccion_id', $detail->seccion_id)
                    ->where('num_lot','like', $detail->num_lot)
                    ->first();

                if (strtoupper($movementType) === 'SALIDA') {
                    $productStock->increment('stock', $detail->quantity);
                } else {
                    $productStock->decrement('stock', $detail->quantity);
                    
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
