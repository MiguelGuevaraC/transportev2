<?php
namespace App\Services;

use App\Models\Bank;

class BankService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getBankById(int $id): ?Bank
    {
        return Bank::find($id);
    }

    public function createBank(array $data): Bank
    {
        $proyect = Bank::create($data);
        return $proyect;
    }

    public function updateBank(Bank $proyect, array $data): Bank
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return Bank::find($id)?->delete() ?? false;
    }

}
