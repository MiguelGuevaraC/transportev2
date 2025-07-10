<?php

namespace App\Http\Controllers\taller;

use App\Http\Controllers\Controller;
use App\Http\Requests\DesignRequest\IndexDesignRequest;
use App\Http\Requests\DesignRequest\StoreDesignRequest;
use App\Http\Requests\DesignRequest\UpdateDesignRequest;
use App\Http\Resources\DesignResource;
use App\Models\Design;
use App\Services\DesignService;

class DesignController extends Controller
{
    protected $service;

    public function __construct(DesignService $service)
    {
        $this->service = $service;
    }

    public function list(IndexDesignRequest $request)
    {
        return $this->getFilteredResults(
            Design::class,
            $request,
            Design::filters,
            Design::sorts,
            DesignResource::class
        );
    }

    public function show($id)
    {
        $item = $this->service->getDesignById($id);
        if (!$item) return response()->json(['error' => 'Design no encontrado'], 404);
        return new DesignResource($item);
    }

    public function store(StoreDesignRequest $request)
    {
        $item = $this->service->createDesign($request->validated());
        return new DesignResource($item);
    }

    public function update(UpdateDesignRequest $request, $id)
    {
        $item = $this->service->getDesignById($id);
        if (!$item) return response()->json(['error' => 'Design no encontrado'], 404);
        $item = $this->service->updateDesign($item, $request->validated());
        return new DesignResource($item);
    }

    public function destroy($id)
    {
        $item = $this->service->getDesignById($id);
        if (!$item) return response()->json(['error' => 'Design no encontrado'], 404);
        $this->service->destroyById($id);
        return response()->json(['message' => 'Design eliminado'], 200);
    }
}