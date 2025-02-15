<?php
namespace App\Services;

use App\Models\CargaDocument;
use App\Models\DetailReception;
use App\Models\Product;
use App\Models\ProductStockByBranch;

class ProductService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getProductById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function createProduct(array $data): Product
    {
        $proyect              = Product::create($data);
        $proyect->codeproduct = strtoupper($proyect->id . '-' . substr(md5($data['description'] . uniqid()), 0, 8));
        $proyect->save();
        return $proyect;
    }
    public function updateProduct(Product $proyect, array $data): Product
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return Product::find($id)?->delete() ?? false;
    }

    public function updateStock(Product $product, int $branchOfficeId)
    {
        $this->updateBranchStock($product->id, $branchOfficeId);
        $this->updateTotalStock($product->id);
    }
    private function calculateStock(int $productId, ?int $branchOfficeId = null)
    {
        return CargaDocument::where('product_id', $productId)
        ->where('branchOffice_id', $branchOfficeId)
        ->whereNull('deleted_at')
        ->selectRaw("
            COALESCE(SUM(CASE WHEN movement_type = 'ENTRADA' THEN quantity ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN movement_type = 'SALIDA' THEN quantity ELSE 0 END), 0)
        AS stock_calculado")
        ->value('stock_calculado') ?? 0;
    }

    private function calculateReception(int $productId, ?int $branchOfficeId = null)
    {
        return DetailReception::where('product_id', $productId)
        ->whereHas('reception', function ($query) use 
        ( $branchOfficeId) {
            $query->where('branchOffice_id', $branchOfficeId);
        })
        ->whereNull('deleted_at')->sum('cant') ?? 0;
    }

    private function updateBranchStock(int $productId, int $branchOfficeId)
    {
        $stockSucursal     = $this->calculateStock($productId, $branchOfficeId);
        $detallesRecepcion = $this->calculateReception($productId, $branchOfficeId);

        ProductStockByBranch::updateOrCreate(
            ['product_id' => $productId, 'branchOffice_id' => $branchOfficeId],
            ['stock' => $stockSucursal - $detallesRecepcion]// Se RESTA recepción
        );
    }

    private function updateTotalStock(int $productId)
    {

        $stockTotal = ProductStockByBranch::where('product_id', $productId)->sum('stock');
        Product::where('id', $productId)->update(['stock' => $stockTotal]);
    }

}
