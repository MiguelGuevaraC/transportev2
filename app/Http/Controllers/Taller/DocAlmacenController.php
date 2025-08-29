<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocAlmacenRequest\IndexDocAlmacenRequest;
use App\Http\Requests\DocAlmacenRequest\StoreDocAlmacenRequest;
use App\Http\Requests\DocAlmacenRequest\UpdateDocAlmacenRequest;
use App\Http\Resources\DocAlmacenResource;
use App\Models\DocAlmacen;
use App\Services\DocAlmacenService;

class DocAlmacenController extends Controller
{
    protected $service;

    public function __construct(DocAlmacenService $service)
    {
        $this->service = $service;
    }

    public function list(IndexDocAlmacenRequest $request)
    {
        return $this->getFilteredResults(
            DocAlmacen::class,
            $request,
            DocAlmacen::filters,
            DocAlmacen::sorts,
            DocAlmacenResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getDocAlmacenById($id);
        if (!$item) return response()->json(['error' => 'Doc. Almacen Neumáticos no encontrado'], 404);
        return new DocAlmacenResource($item);
    }

    public function store(StoreDocAlmacenRequest $request)
    {
        $item = $this->service->createDocAlmacen($request->validated());
        return new DocAlmacenResource($item);
    }

    public function update(UpdateDocAlmacenRequest $request, $id)
    {
        $item = $this->service->getDocAlmacenById($id);
        if (!$item) return response()->json(['error' => 'Doc. Almacen Neumáticos no encontrado'], 404);
        $item = $this->service->updateDocAlmacen($item, $request->validated());
        return new DocAlmacenResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getDocAlmacenById($id);
        if (!$item) return response()->json(['error' => 'Doc. Almacen Neumáticos no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'DocAlmacen eliminado'], 200);
    }
}