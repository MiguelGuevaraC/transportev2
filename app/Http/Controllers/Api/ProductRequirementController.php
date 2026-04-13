<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequirementRequest\IndexProductRequirementRequest;
use App\Models\CheckList;
use App\Models\ProductRequirement;
use App\Services\ProductRequirementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductRequirementController extends Controller
{
    public function __construct(protected ProductRequirementService $service)
    {
    }

    public function index(IndexProductRequirementRequest $request)
    {
        $q = ProductRequirement::query()->with(['checkList.vehicle', 'branchOffice', 'lines.repuesto']);

        return $this->getFilteredResults(
            $q,
            $request,
            ProductRequirement::filters,
            ProductRequirement::sorts,
            \App\Http\Resources\ProductRequirementResource::class
        );
    }

    public function show($id)
    {
        $m = $this->service->getById((int) $id);
        if (! $m) {
            return response()->json(['message' => 'Requerimiento no encontrado'], 404);
        }

        return new \App\Http\Resources\ProductRequirementResource($m);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'check_list_id'      => 'required|integer|exists:check_lists,id',
            'branch_office_id'   => 'nullable|integer|exists:branch_offices,id',
            'status'             => 'nullable|string|max:50',
            'observation'        => 'nullable|string',
            'lines'              => 'required|array|min:1',
            'lines.*.repuesto_id' => 'required|integer|exists:repuestos,id',
            'lines.*.quantity_requested' => 'required|numeric|min:0.0001',
            'lines.*.check_list_detail_id' => 'nullable|integer|exists:check_list_details,id',
            'lines.*.observation' => 'nullable|string',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $data = $v->validated();
        if (empty($data['status'])) {
            $data['status'] = 'BORRADOR';
        }
        $m = $this->service->create($data);

        return new \App\Http\Resources\ProductRequirementResource($m);
    }

    public function update(Request $request, $id)
    {
        $m = ProductRequirement::find($id);
        if (! $m) {
            return response()->json(['message' => 'Requerimiento no encontrado'], 404);
        }
        $v = Validator::make($request->all(), [
            'check_list_id'      => 'sometimes|integer|exists:check_lists,id',
            'branch_office_id'   => 'nullable|integer|exists:branch_offices,id',
            'status'             => 'nullable|string|max:50',
            'observation'        => 'nullable|string',
            'lines'              => 'nullable|array|min:1',
            'lines.*.repuesto_id' => 'required_with:lines|integer|exists:repuestos,id',
            'lines.*.quantity_requested' => 'required_with:lines|numeric|min:0.0001',
            'lines.*.check_list_detail_id' => 'nullable|integer|exists:check_list_details,id',
            'lines.*.observation' => 'nullable|string',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $m = $this->service->update($m, $v->validated());

        return new \App\Http\Resources\ProductRequirementResource($m);
    }

    public function destroy($id)
    {
        $m = ProductRequirement::find($id);
        if (! $m) {
            return response()->json(['message' => 'Requerimiento no encontrado'], 404);
        }
        $this->service->destroy($m);

        return response()->json(['message' => 'Eliminado correctamente']);
    }

    public function fromChecklist(Request $request)
    {
        $v = Validator::make($request->all(), [
            'check_list_id'    => 'required|integer|exists:check_lists,id',
            'branch_office_id' => 'nullable|integer|exists:branch_offices,id',
            'observation'      => 'nullable|string',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $checkList = CheckList::find($request->input('check_list_id'));
        $m         = $this->service->createFromChecklist(
            $checkList,
            $request->input('branch_office_id'),
            $request->input('observation')
        );
        if ($m->lines->isEmpty()) {
            return (new \App\Http\Resources\ProductRequirementResource($m))
                ->additional([
                    'warning' => 'No se generaron líneas automáticas porque los ítems desmarcados no tienen repuesto asociado en el maestro. Puede completar las líneas manualmente.',
                ]);
        }

        return new \App\Http\Resources\ProductRequirementResource($m);
    }
}
