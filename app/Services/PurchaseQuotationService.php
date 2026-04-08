<?php

namespace App\Services;

use App\Models\ProductRequirement;
use App\Models\PurchaseQuotation;
use App\Models\PurchaseQuotationLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseQuotationService
{
    public const MAX_PROVIDERS_PER_REQUIREMENT = 4;

    public function getById(int $id): ?PurchaseQuotation
    {
        return PurchaseQuotation::with(['lines', 'proveedor', 'productRequirement'])->find($id);
    }

    public function create(array $data): PurchaseQuotation
    {
        return DB::transaction(function () use ($data) {
            $requirementId = $data['product_requirement_id'];
            $proveedorId   = $data['proveedor_id'];
            $this->assertProviderSlotAvailable($requirementId, $proveedorId, null);

            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $q = PurchaseQuotation::create($data);
            $this->syncLines($q, $lines);

            return $q->fresh(['lines']);
        });
    }

    public function update(PurchaseQuotation $q, array $data): PurchaseQuotation
    {
        return DB::transaction(function () use ($q, $data) {
            $reqId = $data['product_requirement_id'] ?? $q->product_requirement_id;
            $provId = $data['proveedor_id'] ?? $q->proveedor_id;
            if (isset($data['product_requirement_id']) || isset($data['proveedor_id'])) {
                $this->assertProviderSlotAvailable((int) $reqId, (int) $provId, $q->id);
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);
            $fill = array_intersect_key($data, array_flip($q->getFillable()));
            if ($fill !== []) {
                $q->update($fill);
            }

            if (is_array($lines)) {
                $q->lines()->delete();
                $this->syncLines($q, $lines);
            }

            return $q->fresh(['lines']);
        });
    }

    public function setWinner(PurchaseQuotation $quotation): PurchaseQuotation
    {
        return DB::transaction(function () use ($quotation) {
            PurchaseQuotation::where('product_requirement_id', $quotation->product_requirement_id)
                ->update(['is_winner' => false]);
            $quotation->update(['is_winner' => true, 'status' => 'GANADORA']);

            ProductRequirement::where('id', $quotation->product_requirement_id)
                ->update(['status' => 'COTIZACION_CERRADA']);

            return $quotation->fresh(['lines']);
        });
    }

    protected function assertProviderSlotAvailable(int $requirementId, int $proveedorId, ?int $ignoreQuotationId): void
    {
        $proveedorIds = PurchaseQuotation::where('product_requirement_id', $requirementId)
            ->when($ignoreQuotationId, fn ($q) => $q->where('id', '!=', $ignoreQuotationId))
            ->pluck('proveedor_id')
            ->unique()
            ->values();

        $exists = $proveedorIds->contains($proveedorId);

        if (! $exists && $proveedorIds->count() >= self::MAX_PROVIDERS_PER_REQUIREMENT) {
            throw ValidationException::withMessages([
                'proveedor_id' => 'Solo se permiten hasta '.self::MAX_PROVIDERS_PER_REQUIREMENT.' proveedores distintos por requerimiento.',
            ]);
        }
    }

    protected function syncLines(PurchaseQuotation $q, array $lines): void
    {
        foreach ($lines as $line) {
            $qty        = $line['quantity'];
            $price      = $line['unit_price'];
            $repuestoId = $line['repuesto_id'];
            PurchaseQuotationLine::create([
                'purchase_quotation_id' => $q->id,
                'repuesto_id'           => $repuestoId,
                'quantity'              => $qty,
                'unit_price'            => $price,
                'subtotal'              => $qty * $price,
            ]);
        }
    }

    public function destroy(PurchaseQuotation $q): bool
    {
        return DB::transaction(function () use ($q) {
            $q->lines()->forceDelete();
            return (bool) $q->forceDelete();
        });
    }
}
