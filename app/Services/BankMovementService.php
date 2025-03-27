<?php
namespace App\Services;

use App\Models\BankMovement;
use Illuminate\Support\Facades\Auth;

class BankMovementService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getBankMovementById(int $id): ?BankMovement
    {
        return BankMovement::find($id);
    }

    public function createBankMovement(array $data): BankMovement
    {
        $data['user_created_id'] = Auth::user()->id;
        $proyect                 = BankMovement::create($data);
        return $proyect;
    }

    public function updateBankMovement(BankMovement $proyect, array $data): BankMovement
    {
        $proyect->update($data);
        return $proyect;
    }


    public function destroyById($id)
    {
        return BankMovement::find($id)?->delete() ?? false;
    }

    public function change_status($id)
    {
        $movement = BankMovement::find($id);

        if (! $movement) {
            return false;
        }

        $movement->status = $movement->status === 'No Confirmado' ? 'Confirmado' : 'No Confirmado';
        return $movement->save();
    }

}
