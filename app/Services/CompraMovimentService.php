<?php
namespace App\Services;

use App\Models\CompraMoviment;
use Illuminate\Support\Facades\DB;

class CompraMovimentService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCompraMovimentById(int $id): ?CompraMoviment
    {
        return CompraMoviment::find($id);
    }

   public function createCompraMoviment(array $data): CompraMoviment
{
    // Crear la compra inicialmente sin el número
    $compraMoviment = CompraMoviment::create($data);

    // Generar el número de movimiento basado en branchOffice_id (formato CM + ID sucursal 2 dígitos)
    $tipo = 'CM' . str_pad($data['branchOffice_id'], 2, '0', STR_PAD_LEFT);

    $siguienteNum = DB::selectOne('
        SELECT COALESCE(MAX(CAST(SUBSTRING(number, LOCATE("-", number) + 1) AS SIGNED)), 0) + 1 AS siguienteNum
        FROM compra_moviments
        WHERE SUBSTRING(number, 1, 4) = ?
    ', [$tipo])->siguienteNum;

    $compraMoviment->number = $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT);
    $compraMoviment->save();

    // Insertar detalles si existen
    if (!empty($data['details']) && is_array($data['details'])) {
        foreach ($data['details'] as $detail) {
            $compraMoviment->details()->create([
                'repuesto_id' => $detail['repuesto_id'],
                'quantity'    => $detail['quantity'],
                'unit_price'  => $detail['unit_price'],
                'comment'     => $detail['comment'] ?? null,
                'subtotal'    => $detail['quantity'] * $detail['unit_price'],
            ]);
        }
    }

    return $compraMoviment;
}


public function updateCompraMoviment(CompraMoviment $compraMoviment, array $data): CompraMoviment
{
    // Filtrar solo atributos válidos para actualizar
    $fillableKeys = array_keys($compraMoviment->getAttributes());
    $filteredData = array_intersect_key($data, array_flip($fillableKeys));
    $compraMoviment->update($filteredData);

    if (!empty($data['details']) && is_array($data['details'])) {
        // Obtener detalles actuales claveados por repuesto_id
        $existingDetails = $compraMoviment->details()->get()->keyBy('repuesto_id');

        // IDs de repuestos entrantes en la actualización
        $incomingRepuestoIds = collect($data['details'])->pluck('repuesto_id')->toArray();

        // Eliminar detalles que ya no vienen en la actualización
        foreach ($existingDetails as $repuestoId => $detail) {
            if (!in_array($repuestoId, $incomingRepuestoIds)) {
                $detail->delete();
            }
        }

        // Actualizar o crear detalles según corresponda
        foreach ($data['details'] as $detail) {
            $repuestoId = $detail['repuesto_id'];
            $quantity = $detail['quantity'] ?? 1;
            $unitPrice = $detail['unit_price'] ?? 0;
            $comment = $detail['comment'] ?? null;
            $subtotal = $quantity * $unitPrice;

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
                $compraMoviment->details()->create([
                    'repuesto_id' => $repuestoId,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'comment'     => $comment,
                    'subtotal'    => $subtotal,
                ]);
            }
        }
    } else {
        // Si no vienen detalles, eliminar todos los existentes
        $compraMoviment->details()->delete();
    }

    return $compraMoviment;
}



    public function destroyById($id)
    {
        return CompraMoviment::find($id)?->delete() ?? false;
    }

}
