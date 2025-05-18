<?php
namespace App\Services;

use App\Models\CompraOrder;
use Illuminate\Support\Facades\DB;

class CompraOrderService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCompraOrderById(int $id): ?CompraOrder
    {
        return CompraOrder::find($id);
    }

public function createCompraOrder(array $data): CompraOrder
{
    // Crear la orden de compra principal sin los detalles
    $ordercompra = CompraOrder::create($data);

    // Generar el prefijo con "OC" + branchOffice_id (2 dígitos)
    $tipo = 'OC' . str_pad($data['branchOffice_id'], 2, '0', STR_PAD_LEFT);

    // Obtener el siguiente número de la serie para este prefijo (los primeros 4 caracteres)
    $siguienteNum = DB::selectOne('
        SELECT COALESCE(MAX(CAST(SUBSTRING(number, LOCATE("-", number) + 1) AS SIGNED)), 0) + 1 AS siguienteNum
        FROM compra_orders
        WHERE SUBSTRING(number, 1, 4) = ?
    ', [$tipo])->siguienteNum;

    // Construir el número completo: "OCXX-00000000"
    $ordercompra->number = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
    $ordercompra->save();

    // Insertar los detalles si existen en los datos
    if (!empty($data['details']) && is_array($data['details'])) {
        foreach ($data['details'] as $detail) {
            $ordercompra->details()->create([
                'repuesto_id' => $detail['repuesto_id'],
                'quantity'    => $detail['quantity'],
                'unit_price'  => $detail['unit_price'],
                'comment'     => $detail['comment'] ?? null,
                'subtotal'    => $detail['quantity'] * $detail['unit_price'],
            ]);
        }
    }

    return $ordercompra;
}


    public function updateCompraOrder(CompraOrder $ordercompra, array $data): CompraOrder
    {
        // Actualizar datos principales de la orden (filtrando solo atributos existentes en el modelo)
        $fillableKeys = array_keys($ordercompra->getAttributes());
        $filteredData = array_intersect_key($data, array_flip($fillableKeys));
        $ordercompra->update($filteredData);

        if (! empty($data['details']) && is_array($data['details'])) {
            // Obtener detalles actuales, claveados por repuesto_id
            $existingDetails = $ordercompra->details()->get()->keyBy('repuesto_id');

            // IDs de repuestos recibidos en la actualización
            $incomingRepuestoIds = collect($data['details'])->pluck('repuesto_id')->toArray();

            // Eliminar detalles que no están en la actualización
            foreach ($existingDetails as $repuestoId => $detail) {
                if (! in_array($repuestoId, $incomingRepuestoIds)) {
                    $detail->delete();
                }
            }

            // Actualizar o crear detalles
            foreach ($data['details'] as $detail) {
                $repuestoId = $detail['repuesto_id'];

                // Datos para detalle (asegurar existencia de keys)
                $quantity  = $detail['quantity'] ?? 1;
                $unitPrice = $detail['unit_price'] ?? 0;
                $comment   = $detail['comment'] ?? null;
                $subtotal  = $quantity * $unitPrice;

                if ($existingDetails->has($repuestoId)) {
                    // Actualizar detalle existente
                    $existingDetails[$repuestoId]->update([
                        'quantity'   => $quantity,
                        'unit_price' => $unitPrice,
                        'comment'    => $comment,
                        'subtotal'   => $subtotal,
                    ]);
                } else {
                    // Crear nuevo detalle
                    $ordercompra->details()->create([
                        'repuesto_id' => $repuestoId,
                        'quantity'    => $quantity,
                        'unit_price'  => $unitPrice,
                        'comment'     => $comment,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }
        } else {
            // Si no hay detalles en el update, eliminar todos los existentes
            $ordercompra->details()->delete();
        }

        return $ordercompra;
    }

    public function destroyById($id)
    {
        return CompraOrder::find($id)?->delete() ?? false;
    }

}
