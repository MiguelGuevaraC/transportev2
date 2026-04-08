<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompraPartialReceiptGroupRequest\IndexCompraPartialReceiptGroupRequest;
use App\Http\Resources\CompraPartialReceiptGroupResource;
use App\Models\CompraPartialReceiptGroup;
use App\Services\CompraPartialReceiptGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraPartialReceiptGroupController extends Controller
{
    public function __construct(protected CompraPartialReceiptGroupService $service)
    {
    }

    public function index(IndexCompraPartialReceiptGroupRequest $request)
    {
        $q = CompraPartialReceiptGroup::query()->with(['branchOffice', 'proveedor', 'invoiceCompraMoviment', 'partialMoviments']);

        return $this->getFilteredResults(
            $q,
            $request,
            CompraPartialReceiptGroup::filters,
            CompraPartialReceiptGroup::sorts,
            CompraPartialReceiptGroupResource::class
        );
    }

    public function show($id)
    {
        $m = $this->service->getById((int) $id);
        if (! $m) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        return new CompraPartialReceiptGroupResource($m);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'branch_office_id'       => 'required|integer|exists:branch_offices,id',
            'proveedor_id'           => 'required|integer|exists:people,id',
            'compra_moviment_ids'    => 'required|array|min:1',
            'compra_moviment_ids.*'  => 'integer|exists:compra_moviments,id',
            'observation'            => 'nullable|string',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $m = $this->service->createGroup(
            (int) $request->input('branch_office_id'),
            (int) $request->input('proveedor_id'),
            $request->input('compra_moviment_ids'),
            $request->input('observation')
        );

        return new CompraPartialReceiptGroupResource($m);
    }

    public function attachInvoice(Request $request, $id)
    {
        $m = CompraPartialReceiptGroup::find($id);
        if (! $m) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }
        $v = Validator::make($request->all(), [
            'invoice_compra_moviment_id' => 'required|integer|exists:compra_moviments,id',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }
        $m = $this->service->attachInvoice($m, (int) $request->input('invoice_compra_moviment_id'));

        return new CompraPartialReceiptGroupResource($m);
    }
}
