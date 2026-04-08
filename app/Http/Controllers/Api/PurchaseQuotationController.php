<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseQuotationRequest\IndexPurchaseQuotationRequest;
use App\Http\Resources\PurchaseQuotationResource;
use App\Models\PurchaseQuotation;
use App\Services\PurchaseQuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseQuotationController extends Controller
{
    public function __construct(protected PurchaseQuotationService $service)
    {
    }

    public function index(IndexPurchaseQuotationRequest $request)
    {
        $q = PurchaseQuotation::query()->with(['proveedor', 'lines.repuesto', 'productRequirement']);

        return $this->getFilteredResults(
            $q,
            $request,
            PurchaseQuotation::filters,
            PurchaseQuotation::sorts,
            PurchaseQuotationResource::class
        );
    }

    public function show($id)
    {
        $m = $this->service->getById((int) $id);
        if (! $m) {
            return response()->json(['message' => 'Cotización no encontrada'], 404);
        }

        return new PurchaseQuotationResource($m);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'product_requirement_id' => 'required|integer|exists:product_requirements,id',
            'proveedor_id'           => 'required|integer|exists:people,id',
            'is_winner'              => 'nullable|boolean',
            'status'                 => 'nullable|string|max:50',
            'comment'                => 'nullable|string',
            'lines'                  => 'required|array|min:1',
            'lines.*.repuesto_id'    => 'required|integer|exists:repuestos,id',
            'lines.*.quantity'       => 'required|numeric|min:0.0001',
            'lines.*.unit_price'     => 'required|numeric|min:0',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $data = $v->validated();
        if (! isset($data['is_winner'])) {
            $data['is_winner'] = false;
        }
        if (empty($data['status'])) {
            $data['status'] = 'REGISTRADA';
        }
        $m = $this->service->create($data);

        return new PurchaseQuotationResource($m);
    }

    public function update(Request $request, $id)
    {
        $m = PurchaseQuotation::find($id);
        if (! $m) {
            return response()->json(['message' => 'Cotización no encontrada'], 404);
        }
        $v = Validator::make($request->all(), [
            'product_requirement_id' => 'sometimes|integer|exists:product_requirements,id',
            'proveedor_id'           => 'sometimes|integer|exists:people,id',
            'is_winner'              => 'nullable|boolean',
            'status'                 => 'nullable|string|max:50',
            'comment'                => 'nullable|string',
            'lines'                  => 'nullable|array|min:1',
            'lines.*.repuesto_id'    => 'required_with:lines|integer|exists:repuestos,id',
            'lines.*.quantity'       => 'required_with:lines|numeric|min:0.0001',
            'lines.*.unit_price'     => 'required_with:lines|numeric|min:0',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $m = $this->service->update($m, $v->validated());

        return new PurchaseQuotationResource($m);
    }

    public function destroy($id)
    {
        $m = PurchaseQuotation::find($id);
        if (! $m) {
            return response()->json(['message' => 'Cotización no encontrada'], 404);
        }
        $this->service->destroy($m);

        return response()->json(['message' => 'Eliminado correctamente']);
    }

    public function setWinner($id)
    {
        $m = PurchaseQuotation::find($id);
        if (! $m) {
            return response()->json(['message' => 'Cotización no encontrada'], 404);
        }
        $m = $this->service->setWinner($m);

        return new PurchaseQuotationResource($m);
    }
}
