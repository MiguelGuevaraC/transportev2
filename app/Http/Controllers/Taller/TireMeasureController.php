<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\TireMeasureRequest\IndexTireMeasureRequest;
use App\Http\Requests\TireMeasureRequest\StoreTireMeasureRequest;
use App\Http\Requests\TireMeasureRequest\UpdateTireMeasureRequest;
use App\Http\Resources\TireMeasureResource;
use App\Models\TireMeasure;
use App\Services\TireMeasureService;

class TireMeasureController extends Controller
{
    protected $service;

    public function __construct(TireMeasureService $service)
    {
        $this->service = $service;
    }

    public function list(IndexTireMeasureRequest $request)
    {
        return $this->getFilteredResults(
            TireMeasure::class,
            $request,
            TireMeasure::filters,
            TireMeasure::sorts,
            TireMeasureResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getTireMeasureById($id);
        if (!$item) return response()->json(['error' => 'Medida Neumático no encontrado'], 404);
        return new TireMeasureResource($item);
    }

    public function store(StoreTireMeasureRequest $request)
    {
        $item = $this->service->createTireMeasure($request->validated());
        return new TireMeasureResource($item);
    }

    public function update(UpdateTireMeasureRequest $request, $id)
    {
        $item = $this->service->getTireMeasureById($id);
        if (!$item) return response()->json(['error' => 'Medida Neumático no encontrado'], 404);
        $item = $this->service->updateTireMeasure($item, $request->validated());
        return new TireMeasureResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getTireMeasureById($id);
        if (!$item) return response()->json(['error' => 'Medida Neumático no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'Medida Neumático eliminado'], 200);
    }
}