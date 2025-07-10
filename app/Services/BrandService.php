<?php

namespace App\Services;

use App\Models\Brand;

class BrandService
{
    public function getBrandById(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function createBrand(array $data): Brand
    {
        return Brand::create($data);
    }

    public function updateBrand(Brand $instance, array $data): Brand
    {
        $instance->update($data);
        return $instance;
    }

    public function destroyById($id)
    {
        return Brand::find($id)?->delete() ?? false;
    }
}