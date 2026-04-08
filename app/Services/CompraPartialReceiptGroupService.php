<?php

namespace App\Services;

use App\Models\CompraMoviment;
use App\Models\CompraPartialReceiptGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraPartialReceiptGroupService
{
    public function getById(int $id): ?CompraPartialReceiptGroup
    {
        return CompraPartialReceiptGroup::with(['partialMoviments', 'invoiceCompraMoviment', 'proveedor', 'branchOffice'])->find($id);
    }

    public function createGroup(int $branchOfficeId, int $proveedorId, array $compraMovimentIds, ?string $observation = null): CompraPartialReceiptGroup
    {
        return DB::transaction(function () use ($branchOfficeId, $proveedorId, $compraMovimentIds, $observation) {
            if (count($compraMovimentIds) < 1) {
                throw ValidationException::withMessages(['compra_moviment_ids' => 'Debe indicar al menos un ingreso parcial.']);
            }

            $group = CompraPartialReceiptGroup::create([
                'branch_office_id' => $branchOfficeId,
                'proveedor_id'     => $proveedorId,
                'observation'      => $observation,
            ]);

            foreach ($compraMovimentIds as $mid) {
                $m = CompraMoviment::find($mid);
                if (! $m) {
                    throw ValidationException::withMessages(['compra_moviment_ids' => "Movimiento {$mid} no encontrado."]);
                }
                if ((int) $m->branchOffice_id !== $branchOfficeId || (int) $m->proveedor_id !== $proveedorId) {
                    throw ValidationException::withMessages(['compra_moviment_ids' => 'Todos los movimientos deben ser de la misma sucursal y proveedor del grupo.']);
                }
                if (! $m->is_partial) {
                    throw ValidationException::withMessages(['compra_moviment_ids' => "El movimiento {$mid} no está marcado como ingreso parcial."]);
                }
                if ($m->partial_receipt_group_id) {
                    throw ValidationException::withMessages(['compra_moviment_ids' => "El movimiento {$mid} ya pertenece a otro grupo."]);
                }
                $m->partial_receipt_group_id = $group->id;
                $m->save();
            }

            return $group->fresh(['partialMoviments']);
        });
    }

    public function attachInvoice(CompraPartialReceiptGroup $group, int $invoiceMovimentId): CompraPartialReceiptGroup
    {
        return DB::transaction(function () use ($group, $invoiceMovimentId) {
            $inv = CompraMoviment::find($invoiceMovimentId);
            if (! $inv) {
                throw ValidationException::withMessages(['invoice_compra_moviment_id' => 'Movimiento de factura no encontrado.']);
            }
            if (strtolower((string) $inv->document_type) !== 'factura') {
                throw ValidationException::withMessages(['invoice_compra_moviment_id' => 'El movimiento vinculado debe ser tipo factura.']);
            }
            if ((int) $inv->proveedor_id !== (int) $group->proveedor_id) {
                throw ValidationException::withMessages(['invoice_compra_moviment_id' => 'El proveedor de la factura debe coincidir con el del grupo.']);
            }

            $group->invoice_compra_moviment_id = $invoiceMovimentId;
            $group->save();

            return $group->fresh(['invoiceCompraMoviment', 'partialMoviments']);
        });
    }
}
