<?php
namespace App\Services;

use App\Models\BankAccount;

class BankAccountService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getBankAccountById(int $id): ?BankAccount
    {
        return BankAccount::find($id);
    }

    public function createBankAccount(array $data): BankAccount
    {
        $data['status'] = 'activa';
        $proyect        = BankAccount::create($data);
        return $proyect;
    }

    public function updateBankAccount(BankAccount $proyect, array $data): BankAccount
    {
        $allowedAttributes = array_keys($proyect->getAttributes());
        $filteredData = array_intersect_key($data, array_flip($allowedAttributes));
        if (!empty($filteredData)) {
            $proyect->update($filteredData);
        }
        return $proyect;
    }
    

    public function destroyById($id)
    {
        return BankAccount::find($id)?->delete() ?? false;
    }

}
