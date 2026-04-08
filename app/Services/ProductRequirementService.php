<?php

namespace App\Services;

use App\Models\CheckList;
use App\Models\ProductRequirement;
use App\Models\ProductRequirementLine;
use Illuminate\Support\Facades\DB;

class ProductRequirementService
{
    public function getById(int $id): ?ProductRequirement
    {
        return ProductRequirement::with(['lines', 'checkList', 'branchOffice'])->find($id);
    }

    public function create(array $data): ProductRequirement
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);
            $req = ProductRequirement::create($data);
            foreach ($lines as $line) {
                $req->lines()->create([
                    'check_list_detail_id' => $line['check_list_detail_id'] ?? null,
                    'repuesto_id'          => $line['repuesto_id'],
                    'quantity_requested'   => $line['quantity_requested'],
                    'observation'          => $line['observation'] ?? null,
                ]);
            }

            return $req->load('lines');
        });
    }

    public function update(ProductRequirement $req, array $data): ProductRequirement
    {
        return DB::transaction(function () use ($req, $data) {
            $lines = $data['lines'] ?? null;
            unset($data['lines']);
            $req->update(array_intersect_key($data, array_flip($req->getFillable())));

            if (is_array($lines)) {
                $req->lines()->delete();
                foreach ($lines as $line) {
                    $req->lines()->create([
                        'check_list_detail_id' => $line['check_list_detail_id'] ?? null,
                        'repuesto_id'          => $line['repuesto_id'],
                        'quantity_requested'   => $line['quantity_requested'],
                        'observation'          => $line['observation'] ?? null,
                    ]);
                }
            }

            return $req->fresh(['lines']);
        });
    }

    public function createFromChecklist(CheckList $checkList, ?int $branchOfficeId, ?string $observation = null): ProductRequirement
    {
        return DB::transaction(function () use ($checkList, $branchOfficeId, $observation) {
            $req = ProductRequirement::create([
                'check_list_id'      => $checkList->id,
                'branch_office_id'   => $branchOfficeId,
                'status'             => 'BORRADOR',
                'observation'        => $observation,
            ]);

            $details = $checkList->checkListItems()
                ->wherePivot('is_selected', false)
                ->with('repuesto')
                ->get();

            foreach ($details as $item) {
                $pivotId = isset($item->pivot->id) ? (int) $item->pivot->id : null;
                if (! $item->repuesto_id) {
                    continue;
                }

                $req->lines()->create([
                    'check_list_detail_id' => $pivotId,
                    'repuesto_id'          => $item->repuesto_id,
                    'quantity_requested'   => 1,
                    'observation'          => $item->pivot->observation ?? null,
                ]);
            }

            return $req->load('lines');
        });
    }

    public function destroy(ProductRequirement $req): bool
    {
        return (bool) $req->delete();
    }
}
