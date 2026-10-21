<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarrierGuide;
use App\Models\CarrierGuideRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarrierGuideRelationController extends Controller
{
    public function index($carrierGuideId)
    {
        $guide = CarrierGuide::find($carrierGuideId);
        if (! $guide) {
            return response()->json(['message' => 'Guía no encontrada'], 404);
        }

        $relations = CarrierGuideRelation::with([
            'parentGuide:id,numero,type,status,status_facturado',
            'childGuide:id,numero,type,status,status_facturado',
        ])->where(function ($query) use ($carrierGuideId) {
            $query->where('parent_carrier_guide_id', $carrierGuideId)
                ->orWhere('child_carrier_guide_id', $carrierGuideId);
        })->orderByDesc('id')->get();

        return response()->json([
            'guide' => $guide->only(['id', 'numero', 'type', 'status', 'status_facturado']),
            'relations' => $relations,
            'parents' => $relations->where('child_carrier_guide_id', (int) $carrierGuideId)->values(),
            'children' => $relations->where('parent_carrier_guide_id', (int) $carrierGuideId)->values(),
        ], 200);
    }

    public function store(Request $request, $carrierGuideId)
    {
        $source = CarrierGuide::find($carrierGuideId);
        if (! $source) {
            return response()->json(['message' => 'Guía origen no encontrada'], 404);
        }

        if (strtolower((string) $source->status_facturado) === 'anulada') {
            return response()->json(['message' => 'No se puede usar una guía anulada como matriz'], 422);
        }

        $validated = $request->validate([
            'related_guide_id' => 'nullable|integer|exists:carrier_guides,id',
            'related_guide_ids' => 'nullable|array|min:1',
            'related_guide_ids.*' => 'integer|exists:carrier_guides,id',
            'relation_type' => 'nullable|string|max:50',
        ]);

        $relatedIds = collect($validated['related_guide_ids'] ?? [])
            ->when(isset($validated['related_guide_id']), function ($ids) use ($validated) {
                return $ids->push($validated['related_guide_id']);
            })
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($relatedIds->isEmpty()) {
            return response()->json([
                'message' => 'Debe indicar related_guide_id o related_guide_ids.',
            ], 422);
        }

        if ($relatedIds->contains((int) $carrierGuideId)) {
            return response()->json([
                'message' => 'Una guía no puede vincularse consigo misma.',
            ], 422);
        }

        $relationType = $validated['relation_type'] ?? 'derivada';

        // Evita formar ciclos: si la guía origen ya depende de una guía destino,
        // no se permite volver a enlazar el destino como padre de la origen.
        foreach ($relatedIds as $relatedId) {
            $relatedGuide = CarrierGuide::find($relatedId);
            if (strtolower((string) $relatedGuide->status_facturado) === 'anulada') {
                return response()->json([
                    'message' => "La guía {$relatedId} está anulada y no puede vincularse.",
                ], 422);
            }

            if ($this->wouldCreateCycle((int) $carrierGuideId, $relatedId)) {
                return response()->json([
                    'message' => "El vínculo con la guía {$relatedId} generaría una relación circular.",
                ], 422);
            }

            $otherParentExists = CarrierGuideRelation::where('child_carrier_guide_id', $relatedId)
                ->where('parent_carrier_guide_id', '!=', $carrierGuideId)
                ->exists();

            if ($otherParentExists) {
                return response()->json([
                    'message' => "La guía {$relatedId} ya depende de otra guía matriz.",
                ], 422);
            }
        }

        DB::transaction(function () use ($carrierGuideId, $relatedIds, $relationType) {
            foreach ($relatedIds as $relatedId) {
                CarrierGuideRelation::firstOrCreate(
                    [
                        'parent_carrier_guide_id' => $carrierGuideId,
                        'child_carrier_guide_id' => $relatedId,
                    ],
                    ['relation_type' => $relationType]
                );
            }
        });

        return $this->index($carrierGuideId);
    }

    public function destroy($carrierGuideId, $relatedGuideId)
    {
        $deleted = CarrierGuideRelation::where('parent_carrier_guide_id', $carrierGuideId)
            ->where('child_carrier_guide_id', $relatedGuideId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Vínculo no encontrado'], 404);
        }

        return response()->json(['message' => 'Vínculo eliminado correctamente'], 200);
    }

    protected function wouldCreateCycle(int $parentId, int $childId): bool
    {
        $pending = [$childId];
        $visited = [];

        while ($pending) {
            $current = array_pop($pending);
            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;
            if ($current === $parentId) {
                return true;
            }

            $children = CarrierGuideRelation::where('parent_carrier_guide_id', $current)
                ->pluck('child_carrier_guide_id');

            foreach ($children as $next) {
                $pending[] = (int) $next;
            }
        }

        return false;
    }
}
