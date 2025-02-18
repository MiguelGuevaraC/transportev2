<?php
namespace App\Services;

use App\Models\CargaDocument;
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
            // Obtener el stock actual del producto
            $product      = Product::findOrFail($data['product_id']);
            $productStock = ProductStockByBranch::firstOrCreate(
                [
                    'product_id'      => $data['product_id'],
                    'branchOffice_id' => $data['branchOffice_id'],
                ],
                ['stock' => 0]// Si no existe, inicia con stock 0
            );

            $cargaDocument = CargaDocument::create(array_intersect_key($data
                , array_flip((new CargaDocument())->getFillable())));

            $tipo                    = 'C' . str_pad($cargaDocument->branchOffice_id, 3, '0', STR_PAD_LEFT);
            $tipo                    = str_pad($tipo, 4, '0', STR_PAD_RIGHT);
            $siguienteNum            = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(code_doc, LOCATE("-", code_doc) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM carga_documents WHERE SUBSTRING(code_doc, 1, 4) = ?', [$tipo])[0]->siguienteNum;
            $cargaDocument->code_doc = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
            $cargaDocument->save();

            $afterStock = $this->updateProductStock($data['product_id'], $cargaDocument->branchOffice_id, $data['quantity'], $data['movement_type']);
            $cargaDocument->update([
                'stock_balance_after' => 0,
            ]);
            return $cargaDocument;
        });
    }

    public function updateCargaDocument(CargaDocument $cargaDocument, array $data): CargaDocument
    {
        return DB::transaction(function () use ($cargaDocument, $data) {
            // Revertir el stock anterior
           $branch_old = $cargaDocument->branchOffice_id;
            // Obtener el stock actual del producto
            $product      = Product::findOrFail($data['product_id']);
            $currentStock = $product->stock;

            // Actualizar los datos del documento, incluyendo el stock actual
            $cargaDocument->update(
                array_intersect_key($data, array_flip($cargaDocument->getFillable())) + [
                    'stock_balance_before' => 0, // Asegura que no sea null
                ]
            );
            

            // Aplicar el nuevo stock
            if ($branch_old != $cargaDocument->branchOffice_id) {
                $afterStock = $this->updateProductStock($data['product_id'], $branch_old, $data['quantity'], $data['movement_type']);
            }
            $afterStock = $this->updateProductStock($data['product_id'], $cargaDocument->branchOffice_id, $data['quantity'], $data['movement_type']);

            $cargaDocument->update([
                'stock_balance_after' => $afterStock,
            ]);
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

            // Regresar el stock antes de eliminar el documento
            $this->updateProductStock($cargaDocument->product_id, $cargaDocument->quantity, $cargaDocument->movement_type, true);

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
