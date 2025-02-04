<?php
namespace App\Services;

use App\Models\Unity;

class UnityService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getUnityById(int $id): ?Unity
    {
        return Unity::find($id);
    }

    public function createUnity(array $data): Unity
    {
        $proyect = Unity::create($data);
        return $proyect;
    }

    public function updateUnity(Unity $proyect, array $data): Unity
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return Unity::find($id)?->delete() ?? false;
    }

}
