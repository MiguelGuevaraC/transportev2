<?php
namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
/**
 * @OA\Schema(
 *     schema="Product",
 *     title="Product",
 *     description="Product model",
 *     required={"id", "description", "stock", "unity_id", "person_id"},
 *
 *     @OA\Property(property="id", type="integer", description="Product ID"),
 *     @OA\Property(property="description", type="string", description="Product description"),
 *     @OA\Property(property="stock", type="integer", description="Product stock"),
 *     @OA\Property(property="weight", type="number", format="float", description="Product weight"),
 *     @OA\Property(property="category", type="string", nullable=true, description="Product category"),
 *     @OA\Property(property="addressproduct", type="string", nullable=true, description="Product address"),
 *     @OA\Property(property="codeproduct", type="string", nullable=true, description="Product code"),
 *
 *     @OA\Property(property="unity_id", type="integer", description="Unit of measurement ID"),
 *     @OA\Property(property="unity", ref="#/components/schemas/Unity"),
 *     @OA\Property(property="person_id", type="integer", description="Person ID associated with the product"),

 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation date in YYYY-MM-DD HH:MM:SS format")
 * )
 */

    /**
     * Quita filas fantasma: stock 0 sin posición cuando ya hay otra fila del mismo
     * sucursal/almacén/sección/lote/fecha con stock positivo (evita duplicados 0 vs 608 en el listado).
     */
    protected function filterRedundantZeroStockPivotRows($branchOffices)
    {
        $list    = collect($branchOffices->all());
        $grouped = $list->groupBy(function ($branch) {
            $p = $branch->pivot;
            $d = '';
            if ($p->date_expiration !== null && $p->date_expiration !== '') {
                $d = Carbon::parse($p->date_expiration)->toDateString();
            }

            return $branch->id . '|' . $p->almacen_id . '|' . ($p->seccion_id ?? 'null') . '|' . trim((string) ($p->num_lot ?? '')) . '|' . $d;
        });
        $result = collect();
        foreach ($grouped as $group) {
            $hasPositive = $group->contains(function ($b) {
                return (float) ($b->pivot->stock ?? 0) > 0.00001;
            });
            foreach ($group as $branch) {
                $stock = (float) ($branch->pivot->stock ?? 0);
                $pos   = trim((string) ($branch->pivot->position_code ?? ''));
                if ($hasPositive && $stock <= 0.00001 && $pos === '') {
                    continue;
                }
                $result->push($branch);
            }
        }

        return $result;
    }

    public function toArray($request): array
    {
        $isSalida = strtoupper((string) $request->query('movement_type', '')) === 'SALIDA';
        $branchOffices = $this->branchOffices;
        if ($isSalida) {
            $branchOffices = $branchOffices->filter(function ($branch) {
                return (float) ($branch->pivot->stock ?? 0) > 0;
            });
        } else {
            $branchOffices = $this->filterRedundantZeroStockPivotRows($branchOffices);
        }

        return [
            'id'              => $this->id ?? null,
            'description'     => $this->description ?? null,
            'stock'           => $this->stock ?? null,
            'weight'          => $this->weight ?? null,
            'category'        => $this->category ?? null,
            'addressproduct'  => $this->addressproduct ?? null,
            'codeproduct'     => $this->codeproduct ?? null,

            'unity_id'        => $this->unity_id ?? null,
            'unity'           => $this->unity ? new UnityResource($this->unity) : null,
            'person_id'       => $this->person_id ?? null,
            'person'          => $this->person ?? null,
            'stock_by_branch' => $branchOffices->map(function ($branch) {
                $p = $branch->pivot;

                return [
                    'stock_row_id'     => $p->id ?? null,
                    'branch_office_id' => $branch->id,
                    'branch_name'      => $branch->name,
                    'stock'            => $p->stock,
                    'almacen_id'       => $p->almacen_id,
                    'almacen_name'     => \App\Models\Almacen::find($p->almacen_id)->name ?? null,
                    'seccion_id'       => $p->seccion_id,
                    'seccion_name'     => \App\Models\Seccion::find($p->seccion_id)->name ?? null,
                    'num_lot'          => $p->num_lot,
                    'date_expiration'  => ($p->date_expiration !== null && $p->date_expiration !== '')
                        ? Carbon::parse($p->date_expiration)->toDateString()
                        : null,
                    'position_code'    => $p->position_code,
                ];
            })->values(),

            'created_at'      => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
