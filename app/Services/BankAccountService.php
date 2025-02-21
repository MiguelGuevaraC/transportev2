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
        $proyect = BankAccount::create($data);
        return $proyect;
    }

    public function updateBankAccount(BankAccount $proyect, array $data): BankAccount
    {
        $proyect->update($data);
        return $proyect;
    }

    public function destroyById($id)
    {
        return BankAccount::find($id)?->delete() ?? false;
    }

}
