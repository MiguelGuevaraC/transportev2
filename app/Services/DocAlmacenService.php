<?php

namespace App\Services;

use App\Models\DocAlmacen;
use App\Models\Tire;
use Illuminate\Support\Facades\Log;
use App\Services\DocAlmacenDetailService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocAlmacenService
{
    protected $detailService;

    public function __construct(DocAlmacenDetailService $detailService)
    {
        $this->detailService = $detailService;
    }

    public function getDocAlmacenById(int $id): ?DocAlmacen
    {
        try {
            return DocAlmacen::find($id);
        } catch (Exception $e) {
            Log::error("Error al obtener DocAlmacen por ID: {$id}. Mensaje: " . $e->getMessage());
            return null;
        }
    }

    public function createDocAlmacen(array $data): DocAlmacen
    {
        return DB::transaction(function () use ($data) {
            $docAlmacen = DocAlmacen::create([
                'concept_id' => $data['concept_id'],
                'type' => $data['type'], // 'INGRESO' o 'EGRESO'
                'movement_date' => $data['movement_date'],
                'note' => $data['note'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'user_id' => Auth::id(), // Asignar el usuario autenticado
            ]);

            foreach ($data['details'] as $detail) {
                $tire = Tire::findOrFail($detail['tire_id']);
                $quantity = $detail['quantity'];

                $previous_quantity = $tire->stock;
                $unit_price = $tire->precioventa;

                // Ajustar stock según tipo de movimiento
                if ($data['type'] === 'INGRESO') {
                    $new_quantity = $previous_quantity + $quantity;
                } elseif ($data['type'] === 'EGRESO') {
                    $new_quantity = $previous_quantity - $quantity;
                    if ($new_quantity < 0) {
                        throw new \Exception("Stock insuficiente para el tire '{$tire->nombre}'.");
                    }
                } else {
                    throw new \Exception("Tipo de movimiento inválido: debe ser 'INGRESO' o 'EGRESO'.");
                }

                // Crear detalle
                $docAlmacen->details()->create([
                    'tire_id' => $tire->id,
                    'quantity' => $quantity,
                    'note' => $detail['note'] ?? null,
                    'previous_quantity' => $previous_quantity,
                    'new_quantity' => $new_quantity,
                    'unit_price' => $unit_price,
                    'total_value' => $unit_price * $quantity,
                ]);

                // Actualizar stock del tire
                $tire->stock = $new_quantity;
                $tire->save();
            }

            return $docAlmacen;
        });
    }


    public function updateDocAlmacen(DocAlmacen $instance, array $data): ?DocAlmacen
    {
        return DB::transaction(function () use ($instance, $data) {
            try {
                $newDetails = $data['details'] ?? [];
                unset($data['details']);

                // Restaurar stock original antes de eliminar detalles
                foreach ($instance->details as $oldDetail) {
                    $product = Tire::findOrFail($oldDetail->tire_id);
                    if ($instance->type === 'INGRESO') {
                        $product->stock -= $oldDetail->quantity;
                    } elseif ($instance->type === 'EGRESO') {
                        $product->stock += $oldDetail->quantity;
                    }
                    $product->save();
                }

                // Eliminar detalles antiguos
                foreach ($instance->details as $oldDetail) {
                    $oldDetail->delete();
                }

                // Actualizar campos del doc almacen
                $filteredData = array_intersect_key($data, $instance->getAttributes());
                $instance->update($filteredData);

                // Crear nuevos detalles y actualizar stock
                foreach ($newDetails as $detail) {
                    $product = Tire::findOrFail($detail['tire_id']);
                    $quantity = $detail['quantity'];
                    $unit_price = $product->precioventa;
                    $previous_quantity = $product->stock;

                    if ($instance->type === 'INGRESO') {
                        $new_quantity = $previous_quantity + $quantity;
                    } elseif ($instance->type === 'EGRESO') {
                        $new_quantity = $previous_quantity - $quantity;
                        if ($new_quantity < 0) {
                            throw new \Exception("Stock insuficiente para el tire '{$product->nombre}'.");
                        }
                    } else {
                        throw new \Exception("Tipo de movimiento inválido.");
                    }

                    $instance->details()->create([
                        'tire_id' => $product->id,
                        'quantity' => $quantity,
                        'note' => $detail['note'] ?? null,
                        'previous_quantity' => $previous_quantity,
                        'new_quantity' => $new_quantity,
                        'unit_price' => $unit_price,
                        'total_value' => $unit_price * $quantity,
                    ]);

                    // Actualizar stock
                    $product->stock = $new_quantity;
                    $product->save();
                }

                return $instance;

            } catch (\Exception $e) {
                Log::error("Error al actualizar DocAlmacen ID {$instance->id}: " . $e->getMessage());
                throw $e;
            }
        });
    }


    public function destroyById($id): bool
    {
        try {
            $doc = DocAlmacen::find($id);
            if (!$doc)
                return false;

            // Borrar details si existen
            $doc->details()->delete();

            return $doc->delete();
        } catch (Exception $e) {
            Log::error("Error al eliminar DocAlmacen ID {$id}. Mensaje: " . $e->getMessage());
            return false;
        }
    }
}
