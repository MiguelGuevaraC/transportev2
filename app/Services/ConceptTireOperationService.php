<?php

namespace App\Services;

use App\Models\ConceptTireOperation;

class ConceptTireOperationService
{
    public function getConceptTireOperationById(int $id): ?ConceptTireOperation
    {
        return ConceptTireOperation::find($id);
    }

    public function createConceptTireOperation(array $data): ConceptTireOperation
    {
        return ConceptTireOperation::create($data);
    }

     public function updateConceptTireOperation(ConceptTireOperation $instance, array $data): ConceptTireOperation
    {
        $filteredData = array_intersect_key($data, $instance->getAttributes());
        $instance->update($filteredData);
        return $instance;
    }

    public function destroyById($id)
    {
        return ConceptTireOperation::find($id)?->delete() ?? false;
    }
}