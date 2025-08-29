<?php

namespace App\Http\Controllers\Taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConceptTireOperationRequest\IndexConceptTireOperationRequest;
use App\Http\Requests\ConceptTireOperationRequest\StoreConceptTireOperationRequest;
use App\Http\Requests\ConceptTireOperationRequest\UpdateConceptTireOperationRequest;
use App\Http\Resources\ConceptTireOperationResource;
use App\Models\ConceptTireOperation;
use App\Services\ConceptTireOperationService;

class ConceptTireOperationController extends Controller
{
    protected $service;

    public function __construct(ConceptTireOperationService $service)
    {
        $this->service = $service;
    }

    public function list(IndexConceptTireOperationRequest $request)
    {
        return $this->getFilteredResults(
            ConceptTireOperation::class,
            $request,
            ConceptTireOperation::filters,
            ConceptTireOperation::sorts,
            ConceptTireOperationResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getConceptTireOperationById($id);
        if (!$item) return response()->json(['error' => 'Concepto de Operacón Neumáticos no encontrado'], 404);
        return new ConceptTireOperationResource($item);
    }

    public function store(StoreConceptTireOperationRequest $request)
    {
        $item = $this->service->createConceptTireOperation($request->validated());
        return new ConceptTireOperationResource($item);
    }

    public function update(UpdateConceptTireOperationRequest $request, $id)
    {
        $item = $this->service->getConceptTireOperationById($id);
        if (!$item) return response()->json(['error' => 'Concepto de Operacón Neumáticos no encontrado'], 404);
        $item = $this->service->updateConceptTireOperation($item, $request->validated());
        return new ConceptTireOperationResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getConceptTireOperationById($id);
        if (!$item) return response()->json(['error' => 'Concepto de Operacón Neumáticos no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'ConceptTireOperation eliminado'], 200);
    }
}