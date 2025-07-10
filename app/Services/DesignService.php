<?php

namespace App\Services;

use App\Models\Design;

class DesignService
{
    public function getDesignById(int $id): ?Design
    {
        return Design::find($id);
    }

    public function createDesign(array $data): Design
    {
        return Design::create($data);
    }

    public function updateDesign(Design $instance, array $data): Design
    {
        $instance->update($data);
        return $instance;
    }

    public function destroyById($id)
    {
        return Design::find($id)?->delete() ?? false;
    }
}