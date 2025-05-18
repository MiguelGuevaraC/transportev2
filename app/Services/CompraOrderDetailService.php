<?php
namespace App\Services;

use App\Models\CompraOrderDetail;

class CompraOrderDetailService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCompraOrderDetailById(int $id): ?CompraOrderDetail
    {
        return CompraOrderDetail::find($id);
    }

    public function createCompraOrderDetail(array $data): CompraOrderDetail
    {
        // Asegurarse de que existan los valores necesarios
        $unitPrice = isset($data['unit_price']) ? floatval($data['unit_price']) : 0;
        $quantity  = isset($data['quantity']) ? floatval($data['quantity']) : 0;

        // Calcular subtotal
        $data['subtotal'] = $unitPrice * $quantity;
        $ordercompra      = CompraOrderDetail::create($data);

        return $ordercompra;
    }

    public function updateCompraOrderDetail(CompraOrderDetail $ordercompra, array $data): CompraOrderDetail
    {
        $unitPrice = isset($data['unit_price']) ? floatval($data['unit_price']) : 0;
        $quantity  = isset($data['quantity']) ? floatval($data['quantity']) : 0;

        // Calcular subtotal
        $data['subtotal'] = $unitPrice * $quantity;
        $filteredData     = array_intersect_key($data, $ordercompra->getAttributes());

        $ordercompra->update($filteredData);
        return $ordercompra;
    }
    public function destroyById($id)
    {
        return CompraOrderDetail::find($id)?->delete() ?? false;
    }

}
