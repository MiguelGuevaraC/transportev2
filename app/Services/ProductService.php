<?php
namespace App\Services;

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

}
