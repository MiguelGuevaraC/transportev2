<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlmacenResource;
use App\Models\Almacen;
use App\Models\DocumentCargaDetail;
use App\Models\ProductStockByBranch;
use Illuminate\Http\Request;

class WarehouseCargaHelperController extends Controller
{
    /**
     * Sucursal del documento: siempre la del trabajador del usuario autenticado.
     */
    protected function branchOfficeIdFromAuth(): ?int
    {
        $id = auth()->user()?->worker?->branchOffice_id;

        return $id !== null ? (int) $id : null;
    }

    /**
     * Solo almacenes con secciones (sin listar sucursales) para selects de documento de carga.
     */
    public function almacenesConSecciones(Request $request)
    {
        $query = Almacen::with('seccions')->orderBy('name');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return AlmacenResource::collection($query->get());
    }

    /**
     * Lotes sugeridos según movimientos previos del producto en sucursal/almacén/sección.
     */
    public function lotesSugeridos(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|integer|exists:products,id,deleted_at,NULL',
            'almacen_id'      => 'nullable|integer|exists:almacens,id,deleted_at,NULL',
            'seccion_id'      => 'nullable|integer|exists:seccions,id,deleted_at,NULL',
            'limit'           => 'nullable|integer|min:1|max:50',
        ]);

        $branchOfficeId = $this->branchOfficeIdFromAuth();
        if ($branchOfficeId === null) {
            return response()->json(['message' => 'El usuario no tiene sucursal asignada.'], 422);
        }

        $limit = (int) $request->input('limit', 20);

        $q = DocumentCargaDetail::query()
            ->where('product_id', $request->input('product_id'))
            ->where('branchOffice_id', $branchOfficeId)
            ->whereNotNull('num_lot')
            ->where('num_lot', '!=', '');

        if ($request->filled('almacen_id')) {
            $q->where('almacen_id', $request->input('almacen_id'));
        }
        if ($request->filled('seccion_id')) {
            $q->where('seccion_id', $request->input('seccion_id'));
        }

        $lotes = $q->orderByDesc('id')
            ->limit(200)
            ->pluck('num_lot')
            ->unique()
            ->values()
            ->take($limit)
            ->all();

        return response()->json(['suggested_lots' => $lotes]);
    }

    /**
     * Cantidad total y desglose por producto en una posición (sticker A/A1).
     */
    public function cantidadPorPosicion(Request $request)
    {
        $request->validate([
            'almacen_id'      => 'required|integer|exists:almacens,id,deleted_at,NULL',
            'position_code'   => 'required|string|max:64',
            'seccion_id'      => 'nullable|integer|exists:seccions,id,deleted_at,NULL',
        ]);

        $branchOfficeId = $this->branchOfficeIdFromAuth();
        if ($branchOfficeId === null) {
            return response()->json(['message' => 'El usuario no tiene sucursal asignada.'], 422);
        }

        $q = ProductStockByBranch::query()
            ->with('product:id,description')
            ->where('branchOffice_id', $branchOfficeId)
            ->where('almacen_id', $request->input('almacen_id'))
            ->where('position_code', $request->input('position_code'));

        if ($request->filled('seccion_id')) {
            $q->where('seccion_id', $request->input('seccion_id'));
        }

        $rows = $q->get();
        $total = (float) $rows->sum('stock');
        $lines = $rows->map(function ($row) {
            return [
                'product_id'   => $row->product_id,
                'product_name' => $row->product->description ?? null,
                'stock'        => (float) $row->stock,
                'num_lot'      => $row->num_lot,
                'date_expiration' => $row->date_expiration,
            ];
        })->values()->all();

        return response()->json([
            'position_code' => $request->input('position_code'),
            'total_stock'   => $total,
            'lines'         => $lines,
        ]);
    }

    /**
     * Posiciones ocupadas (stock > 0) y huecos registrados con stock 0 (útil para reubicar).
     */
    public function estadoPosiciones(Request $request)
    {
        $request->validate([
            'almacen_id'      => 'required|integer|exists:almacens,id,deleted_at,NULL',
            'seccion_id'      => 'nullable|integer|exists:seccions,id,deleted_at,NULL',
        ]);

        $branchOfficeId = $this->branchOfficeIdFromAuth();
        if ($branchOfficeId === null) {
            return response()->json(['message' => 'El usuario no tiene sucursal asignada.'], 422);
        }

        $base = ProductStockByBranch::query()
            ->where('branchOffice_id', $branchOfficeId)
            ->where('almacen_id', $request->input('almacen_id'))
            ->whereNotNull('position_code')
            ->where('position_code', '!=', '');

        if ($request->filled('seccion_id')) {
            $base->where('seccion_id', $request->input('seccion_id'));
        }

        $occupied = (clone $base)->where('stock', '>', 0)
            ->get(['position_code', 'stock', 'product_id', 'num_lot', 'seccion_id'])
            ->map(function ($row) {
                return [
                    'position_code' => $row->position_code,
                    'stock'         => (float) $row->stock,
                    'product_id'    => $row->product_id,
                    'num_lot'       => $row->num_lot,
                    'seccion_id'    => $row->seccion_id,
                ];
            })->values()->all();

        $emptyTracked = (clone $base)->where('stock', '<=', 0)
            ->pluck('position_code')
            ->unique()
            ->values()
            ->all();

        return response()->json([
            'occupied'        => $occupied,
            'empty_tracked'   => $emptyTracked,
        ]);
    }
}
