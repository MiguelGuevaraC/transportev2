<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest\IndexBrandRequest;
use App\Http\Requests\BrandRequest\StoreBrandRequest;
use App\Http\Requests\BrandRequest\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;

class BrandController extends Controller
{
    protected $service;

    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }

    public function list(IndexBrandRequest $request)
    {
        return $this->getFilteredResults(
            Brand::class,
            $request,
            Brand::filters,
            Brand::sorts,
            BrandResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getBrandById($id);
        if (!$item) return response()->json(['error' => 'Brand no encontrado'], 404);
        return new BrandResource($item);
    }

    public function store(StoreBrandRequest $request)
    {
        $item = $this->service->createBrand($request->validated());
        return new BrandResource($item);
    }

    public function update(UpdateBrandRequest $request, $id)
    {
        $item = $this->service->getBrandById($id);
        if (!$item) return response()->json(['error' => 'Brand no encontrado'], 404);
        $item = $this->service->updateBrand($item, $request->validated());
        return new BrandResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getBrandById($id);
        if (!$item) return response()->json(['error' => 'Brand no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'Brand eliminado'], 200);
    }
}