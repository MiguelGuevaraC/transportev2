<?php

namespace App\Services;

use App\Models\Material;

class MaterialService
{
    public function getMaterialById(int $id): ?Material
    {
        return Material::find($id);
    }

    public function createMaterial(array $data): Material
    {
        return Material::create($data);
    }

    public function updateMaterial(Material $instance, array $data): Material
    {
        $instance->update($data);
        return $instance;
    }

    public function destroyById($id)
    {
        return Material::find($id)?->delete() ?? false;
    }
}