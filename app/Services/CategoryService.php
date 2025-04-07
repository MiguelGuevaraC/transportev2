<?php
namespace App\Services;

use App\Models\Category;

class CategoryService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function createCategory(array $data): Category
    {
        $taller = Category::create($data);
        return $taller;
    }

    public function updateCategory(Category $taller, array $data): Category
    {
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return Category::find($id)?->delete() ?? false;
    }

}
