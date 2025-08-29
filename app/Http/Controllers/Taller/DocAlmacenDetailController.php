<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocAlmacenDetailRequest\IndexDocAlmacenDetailRequest;
use App\Http\Requests\DocAlmacenDetailRequest\StoreDocAlmacenDetailRequest;
use App\Http\Requests\DocAlmacenDetailRequest\UpdateDocAlmacenDetailRequest;
use App\Http\Resources\DocAlmacenDetailResource;
use App\Models\DocAlmacenDetail;
use App\Services\DocAlmacenDetailService;

class DocAlmacenDetailController extends Controller
{
    protected $service;

    public function __construct(DocAlmacenDetailService $service)
    {
        $this->service = $service;
    }

    public function list(IndexDocAlmacenDetailRequest $request)
    {
        return $this->getFilteredResults(
            DocAlmacenDetail::class,
            $request,
            DocAlmacenDetail::filters,
            DocAlmacenDetail::sorts,
            DocAlmacenDetailResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getDocAlmacenDetailById($id);
        if (!$item) return response()->json(['error' => 'DocAlmacenDetail no encontrado'], 404);
        return new DocAlmacenDetailResource($item);
    }

    public function store(StoreDocAlmacenDetailRequest $request)
    {
        $item = $this->service->createDocAlmacenDetail($request->validated());
        return new DocAlmacenDetailResource($item);
    }

    public function update(UpdateDocAlmacenDetailRequest $request, $id)
    {
        $item = $this->service->getDocAlmacenDetailById($id);
        if (!$item) return response()->json(['error' => 'DocAlmacenDetail no encontrado'], 404);
        $item = $this->service->updateDocAlmacenDetail($item, $request->validated());
        return new DocAlmacenDetailResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getDocAlmacenDetailById($id);
        if (!$item) return response()->json(['error' => 'DocAlmacenDetail no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'DocAlmacenDetail eliminado'], 200);
    }
}