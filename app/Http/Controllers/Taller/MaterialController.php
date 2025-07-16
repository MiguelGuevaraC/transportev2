<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest\IndexMaterialRequest;
use App\Http\Requests\MaterialRequest\StoreMaterialRequest;
use App\Http\Requests\MaterialRequest\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Services\MaterialService;

class MaterialController extends Controller
{
    protected $service;

    public function __construct(MaterialService $service)
    {
        $this->service = $service;
    }

    public function list(IndexMaterialRequest $request)
    {
        return $this->getFilteredResults(
            Material::class,
            $request,
            Material::filters,
            Material::sorts,
            MaterialResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getMaterialById($id);
        if (!$item)
            return response()->json(['error' => 'Material no encontrado'], 404);
        return new MaterialResource($item);
    }

    public function store(StoreMaterialRequest $request)
    {
        $item = $this->service->createMaterial($request->validated());
        return new MaterialResource($item);
    }

    public function update(UpdateMaterialRequest $request, $id)
    {
        $item = $this->service->getMaterialById($id);
        if (!$item)
            return response()->json(['error' => 'Material no encontrado'], 404);
        $item = $this->service->updateMaterial($item, $request->validated());
        return new MaterialResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getMaterialById($id);
        if (!$item)
            return response()->json(['error' => 'Material no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'Material eliminado'], 200);
    }
}