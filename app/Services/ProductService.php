<?php
namespace App\Services;

use App\Models\CargaDocument;
use App\Models\DetailReception;
use App\Models\Product;

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
        $proyect = Product::create($data);
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

    public function updatestock(Product $product)
    {
        $movimientos = CargaDocument::where('product_id', $product->id)->whereNull('deleted_at')
            ->selectRaw("SUM(CASE WHEN movement_type = 'ENTRADA' THEN quantity ELSE 0 END)
        - SUM(CASE WHEN movement_type = 'SALIDA' THEN quantity ELSE 0 END) AS stock_calculado")->first();

        $detallesRecepcion = DetailReception::where('product_id', $product->id)
            ->whereHas('reception.firstCarrierGuide', function ($query) {
                $query->where('status_facturado', '!=', 'Anulada');
            })->whereNull('deleted_at')->sum('cant');

        $product->stock = max(0, ($movimientos->stock_calculado ?? 0) - $detallesRecepcion);
        $product->save();
    }

}
