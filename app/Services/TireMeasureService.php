<?php

namespace App\Services;

use App\Models\TireMeasure;

class TireMeasureService
{
    public function getTireMeasureById(int $id): ?TireMeasure
    {
        return TireMeasure::find($id);
    }

    public function createTireMeasure(array $data): TireMeasure
    {
        return TireMeasure::create($data);
    }

    public function updateTireMeasure(TireMeasure $instance, array $data): TireMeasure
    {
        $filteredData = array_intersect_key($data, $instance->getAttributes());
        $instance->update($filteredData);
        return $instance;
    }

    public function destroyById($id)
    {
        return TireMeasure::find($id)?->delete() ?? false;
    }
}