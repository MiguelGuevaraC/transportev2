<?php
namespace App\Services;

use App\Models\CargaDocument;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CargaDocumentService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCargaDocumentById(int $id): ?CargaDocument
    {
        return CargaDocument::find($id);
    }

    public function createCargaDocument(array $data): CargaDocument
    {
        return DB::transaction(function () use ($data) {
            // Obtener el stock actual del producto
            $product = Product::findOrFail($data['product_id']);
            $currentStock = $product->stock;

            // Crear el documento de carga, incluyendo el stock actual
            $cargaDocument = CargaDocument::create([
                'movement_date'   => $data['movement_date'],
                'quantity'        => $data['quantity'],
                'unit_price'      => $data['unit_price'],
                'total_cost'      => $data['total_cost'],
                'weight'          => $data['weight'],
                'movement_type'   => $data['movement_type'],
                'comment'         => $data['comment'] ?? null,
                'product_id'      => $data['product_id'],
                'person_id'       => $data['person_id'],
                'created_at'      => now(),
                'stock_balance'   => $currentStock, // Guardar el stock actual
            ]);

            // Actualizar el stock del producto
            $this->updateProductStock($data['product_id'], $data['quantity'], $data['movement_type']);

            return $cargaDocument;
        });
    }

    public function updateCargaDocument(CargaDocument $cargaDocument, array $data): CargaDocument
    {
        return DB::transaction(function () use ($cargaDocument, $data) {
            // Revertir el stock anterior
            $this->updateProductStock($cargaDocument->product_id, $cargaDocument->quantity, $cargaDocument->movement_type, true);

            // Obtener el stock actual del producto
            $product = Product::findOrFail($data['product_id']);
            $currentStock = $product->stock;

            // Actualizar los datos del documento, incluyendo el stock actual
            $cargaDocument->update([
                'movement_date'   => $data['movement_date'],
                'quantity'        => $data['quantity'],
                'unit_price'      => $data['unit_price'],
                'total_cost'      => $data['total_cost'],
                'weight'          => $data['weight'],
                'movement_type'   => $data['movement_type'],
                'comment'         => $data['comment'] ?? null,
                'product_id'      => $data['product_id'],
                'person_id'       => $data['person_id'],
                'stock_balance'   => $currentStock, // Guardar el stock actual
            ]);

            // Aplicar el nuevo stock
            $this->updateProductStock($data['product_id'], $data['quantity'], $data['movement_type']);

            return $cargaDocument;
        });
    }

    public function destroyById($id)
    {
        return DB::transaction(function () use ($id) {
            $cargaDocument = CargaDocument::find($id);

            if (!$cargaDocument) {
                throw new ModelNotFoundException("El documento de carga no existe.");
            }

            // Regresar el stock antes de eliminar el documento
            $this->updateProductStock($cargaDocument->product_id, $cargaDocument->quantity, $cargaDocument->movement_type, true);

            return $cargaDocument->delete();
        });
    }

    private function updateProductStock(int $productId, float $quantity, string $movementType, bool $isReversal = false)
    {
        $product = Product::findOrFail($productId);

        if ($movementType === 'INGRESO') {
            // Si es reversión, restar; de lo contrario, sumar
            $product->stock += $isReversal ? -$quantity : $quantity;
        } elseif ($movementType === 'EGRESO') {
            // Si es reversión, sumar; de lo contrario, restar
            $product->stock -= $isReversal ? -$quantity : $quantity;
        }

        $product->save();
    }
}
