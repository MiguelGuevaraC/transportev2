<?php
namespace App\Services;

use App\Models\CheckListItem;

class CheckListItemService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getCheckListItemById(int $id): ?CheckListItem
    {
        return CheckListItem::find($id);
    }

    public function createCheckListItem(array $data): CheckListItem
    {
        $taller = CheckListItem::create($data);
        return $taller;
    }

    public function updateCheckListItem(CheckListItem $taller, array $data): CheckListItem
    {
        $filteredData = array_intersect_key($data, $taller->getAttributes());
        $taller->update($filteredData);
        return $taller;
    }
    public function destroyById($id)
    {
        return CheckListItem::find($id)?->delete() ?? false;
    }

}
